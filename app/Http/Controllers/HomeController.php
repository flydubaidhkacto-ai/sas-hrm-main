<?php

namespace App\Http\Controllers;

use App\Actions\EmployeePunched;
use App\Actions\GeneratePayslip\Generate;
use App\Jobs\CheckEmployeeReward;
use App\Jobs\InitAutoAbsentFineEmployees;
use App\Jobs\SendLateWarningSMS;
use App\Models\Attendance;
use App\Models\AutoReward;
use App\Models\EmployeeReward;
use App\Models\InOutRecord;
use App\Models\Notice;
use App\Models\NoticeView;
use App\Models\Setting;
use App\Models\SuperDepartment;
use App\Traits\GenerateTotalDutyOfEmployee;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserZkDeviceFingerprint;
use App\Models\ZktecoCloud;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use CheckAndVerifyLicense;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{

    use GenerateTotalDutyOfEmployee;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        SendLateWarningSMS::dispatch();
    }

    public function index(Request $request)
    {
        if ($request->arif_regenrate) {
            $records = InOutRecord::query()
                ->whereDate('date_time', '>', '2025-07-31')
                ->get();
            foreach ($records as $record) {
                dump(
                    \App\Actions\EmployeePunched\Execute::run($record)
                );
            }
            dd('done!');
        }
        // dd(
        //     Generate::execute(User::find(1260), Carbon::parse('2025-04-01'))
        // );
        if ($request->no_need_to_show_sms_error_warning) {
            cache()->set('no_need_to_show_sms_error_warning', 1, now()->addMonth()); // month
            return redirect()->back();
        }

        if (request()->home_page_refresh !== null) {
            if (request()->home_page_refresh != 1) {
                (Setting::where('key', 'home_page_refresh')->delete());
            } else {
                (Setting::create([
                    'key' => 'home_page_refresh',
                    'value' => 1
                ]));
            }
            Toastr::success('Auto Refresh set :)', 'Success');
            return redirect()->route('home');
        }

        if ($request->notice) {
            if ($request->id) {
                NoticeView::create([
                    'employee_id' => user()->id,
                    'notice_id' => $request->id
                ]);
            }
            $totalUsers = User::active_employees()->count();
            return Notice::query()
                ->when(!$request->all || $request->all == 0, function ($q) {
                    $q->whereDoesntHave('userView', function ($query) {
                        $query->where('employee_id', user()->id);
                    });
                })
                ->limit($request->limit ?? 3)
                ->latest()
                ->with('attachments')
                ->withCount('userView')
                ->get()
                ->map(function ($notice) use ($request, $totalUsers) {
                    $notice->link = route('notice.view', ['id' => $notice->id]);
                    $notice->notViewed = (!$request->all || $request->all == 0) ? false : !(
                        $notice->userView()
                        ->where('employee_id', user()->id)
                        ->exists()
                    );
                    $notice->total_users = $totalUsers;
                    return $notice;
                });
        }

        return view('dashboard.dashboard-optimized', [
            'home_page_refresh' => Setting::where('key', 'home_page_refresh')->first(),
        ]);
    }

    /**
     * Get employee statistics for dashboard
     */
    public function getEmployeeStats(Request $request)
    {
        if (!function_exists('filter_role__tmp')) {
            function filter_role__tmp($users)
            {
                if (role_can('dashboard')) {
                    return $users;
                }
                if (role_can('dashboard.self.super_department')) {
                    return $users->whereHas('department', function ($q) {
                        $q->where('super_department_id', user()->department?->super_department_id);
                    });
                }
                return $users->where('id', user()->id);
            }
        }

        $today = Carbon::today();

        $stats = [
            'total_employees' => filter_role__tmp(\App\Models\User::employees())->count(),
            'total_active_employees' => filter_role__tmp(\App\Models\User::active_employees())->count(),
            'total_absent_employees' => filter_role__tmp(\App\Models\User::active_employees())
                ->whereHas('attendance', function ($query) {
                    $query->whereDate('date', today());
                    $query->where('got_late_check_in', true);
                })
                ->count(),
            'today_present' => \App\Models\User::active_employees()
                ->whereHas('attendance', function ($query) {
                    $query->where('date', now()->format('Y-m-d'));
                })->count(),
            'today_absent' => \App\Models\User::absent($today)->count(),
            'today_leave' => \App\Models\LeaveApplication::final_approved()
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get machine punches for dashboard
     */
    public function getMachinePunches(Request $request)
    {
        if (!(role_can('dashboard') || role_can('dashboard.self') || role_can('dashboard.self.super_department'))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $punches = \App\Models\InOutRecord::with([
            'employee.designation',
            'employee.department.super_department',
        ]);

        if (!role_can('dashboard')) {
            if (role_can('dashboard.self.super_department')) {
                $punches->whereHas('employee', function ($q1) {
                    $q1->whereHas('department', function ($q) {
                        $q->where(
                            'super_department_id',
                            user()->department?->super_department_id,
                        );
                    });
                });
            } else {
                $punches->where('user_id', user()->id);
            }
        }

        $punches = $punches->orderBy('id', 'desc')
            ->limit(12)
            ->get();

        return response()->json([
            'html' => view('reports.machine-log-table', ['data' => $punches])->render()
        ]);
    }

    /**
     * Get branch statistics for dashboard
     */
    public function getBranchStats(Request $request)
    {
        if (!(role_can('dashboard') || role_can('dashboard.self.super_department'))) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $departments = role_can('dashboard')
            ? \App\Models\SuperDepartment::ordered()->get()
            : \App\Models\SuperDepartment::where('id', user()->department?->super_department_id)->ordered()->get();

        $branchData = [];

        foreach ($departments as $item) {
            $in = \App\Models\User::currently_inside()
                ->whereHas('department', function ($query) use ($item) {
                    $query->where('super_department_id', $item->id);
                })
                ->count();

            $out = \App\Models\User::currently_outside()
                ->whereHas('department', function ($query) use ($item) {
                    $query->where('super_department_id', $item->id);
                })
                ->count();

            $total = \App\Models\User::active_employees()
                ->whereHas('department', function ($query) use ($item) {
                    $query->where('super_department_id', $item->id);
                })
                ->count();

            $present = \App\Models\User::active_employees()
                ->whereHas('attendance', function ($query) {
                    $query->where('date', now()->format('Y-m-d'));
                })
                ->whereHas('department', function ($query) use ($item) {
                    $query->where('super_department_id', $item->id);
                })
                ->count();

            $lastData = \App\Models\InOutRecord::whereHas(
                'employee.department.super_department',
                function ($query) use ($item) {
                    $query->where('id', $item->id);
                },
            )
                ->orderBy('date_time', 'desc')
                ->first();

            $branchData[] = [
                'id' => $item->id,
                'name' => $item->name,
                'in' => $in,
                'out' => $out,
                'total' => $total,
                'present' => $present,
                'last_sync' => $lastData ? ($lastData->created_at ?? $lastData->date_time)->format('d M, Y h:i A') : null
            ];
        }

        return response()->json(['branches' => $branchData]);
    }

    /**
     * Get pending tasks for dashboard
     */
    public function getPendingTasks(Request $request)
    {
        $data = [
            'pending_leaves' => view('dashboard.parts.pending-leaves')->render(),
            'pending_fines' => view('dashboard.parts.pending-fines')->render(),
            'pending_loans' => view('dashboard.parts.pending-loans')->render(),
            'pending_att_requests' => view('dashboard.parts.pending-att-requests')->render(),
        ];

        return response()->json($data);
    }

    /**
     * Get birthday and admin cards for dashboard
     */
    public function getAdminCards(Request $request)
    {
        if (!function_exists('filter_role__tmp')) {
            function filter_role__tmp($users)
            {
                if (role_can('dashboard')) {
                    return $users;
                }
                if (role_can('dashboard.self.super_department')) {
                    return $users->whereHas('department', function ($q) {
                        $q->where('super_department_id', user()->department?->super_department_id);
                    });
                }
                return $users->where('id', user()->id);
            }
        }

        $total_absent_employees = filter_role__tmp(\App\Models\User::active_employees())
            ->whereHas('attendance', function ($query) {
                $query->whereDate('date', today());
                $query->where('got_late_check_in', true);
            })
            ->latest()
            ->limit(20)
            ->get();

        $today = Carbon::today();
        $user = user();

        $data = [
            'birthday_card' => view('dashboard.parts.birthday-card', compact('user'))->render(),
            'admin_cards' => view('dashboard.parts.admin-cards', compact('total_absent_employees', 'today'))->render(),
        ];

        return response()->json($data);
    }


    // public function markAsViewed(Request $request)
    // {
    //     $request->validate([
    //         'notice_id' => 'required|exists:notices,id',
    //     ]);

    //     NoticeView::firstOrCreate([
    //         'user_id' => user()->id,
    //         'notice_id' => $request->notice_id,
    //     ]);

    //     // return response()->json(['success' => true]);
    //     Toastr::success('success');
    // }

    function department_current_status($department)
    {
        abort_if(!(
            role_can('dashboard')
            || role_can('dashboard.self.super_department')
        ), 403, "Please get permission from Administrator");
        $department = SuperDepartment::findOrFail($department);
        $in = \App\Models\User::currently_inside()
            ->with(['designation', 'department.super_department'])
            ->whereHas('department', function ($query) use ($department) {
                $query->where('super_department_id', $department->id);
            })
            ->get();
        $out = \App\Models\User::currently_outside()
            ->with(['designation', 'department.super_department'])
            ->whereHas('department', function ($query) use ($department) {
                $query->where('super_department_id', $department->id);
            })
            ->get();
        $absent = User::absent()
            ->with(['designation', 'department.super_department'])
            ->whereHas('department', function ($query) use ($department) {
                $query->where('super_department_id', $department->id);
            })
            ->get();
        return view('dashboard.department_current_status', [
            'department' => $department,
            'in' => $in,
            'out' => $out,
            'absent' => $absent
        ]);
    }
    function password_change(Request $request)
    {
        return view('users.password_change');
    }
    function password_change_save(Request $request)
    {
        abort_if(config('app.is_demo'), 403);

        $data = $request->validate([
            'current_password' => 'required|max:255',
            'new_password' => 'required|min:6|max:255',
            'confirm_new_password' => 'required|max:255|same:new_password',
        ]);
        if (
            Hash::check($data['current_password'], user()->password)
        ) {
            User::where('id', user()->id)->update([
                'password' => Hash::make($data['new_password'])
            ]);
            Toastr::success('Password changed successfully', 'Success');
            return redirect()->route('home');
        }
        Toastr::error('Wrong current password', 'Error');
        return redirect()->back();
    }

    function head_of_employees_store(User $user, Request $request)
    {
        abort_if(!role_can('role'), 403);
        $request->validate([
            'employee_ids' => 'nullable|array',
        ]);
        $user->managed_employees()->sync($request->employee_ids ?? []);
        Toastr::success('Head of employees updated successfully', 'Success');
        return back();
    }
    function head_of_employees(User $user)
    {
        abort_if(!role_can('role'), 403);
        return view('roles.head_of_employees', [
            'emps' => User::active_employees()
                ->whereNot('id', $user->id)
                ->get(),
            'user' => $user->load('managed_employees'),
        ]);
    }
    function update(Request $request)
    {
        if ($request->update) {
            Toastr::success('Update request has been sent. Please wait a moment for our system to update!', 'Success');
            return redirect()->back();
        }
        $new = Http::retry(3)
            ->timeout(3)
            ->get(
                config('app.env') == 'local' ? "http://localhost:8001/latest-software-version" : "https://app.prohrbd.com/latest-software-version"
            )->body();
        return view('settings.update', [
            'update' => $new
        ]);
    }

    public function getWeather(Request $request)
    {
        // Fetch lat and lon from the query parameters
        $lat = $request->query('lat', '23.685000');  // Default to Bangladesh if no lat is passed
        $lon = $request->query('lon', '90.356300');  // Default to Bangladesh if no lon is passed

        $apiKey = config('services.openweathermap.key');

        // Call the OpenWeatherMap API
        $response = Cache::remember('weather_data' . $lat . $lon, 60 * 60, function () use ($lat, $lon, $apiKey) {
            return Http::get("https://api.openweathermap.org/data/2.5/weather", [
                'lat' => $lat,
                'lon' => $lon,
                'units' => 'metric',
                'appid' => $apiKey
            ])->json();
        });

        // If the response is successful, return the data, otherwise, return an error
        if ($response) {
            return response()->json($response);
        } else {
            return response()->json([
                'error' => 'Failed to fetch weather data',
                'status' => 422
            ], 422);
        }
    }

    /**
     * Clear application cache (route: cache.clean)
     */
    public function cacheClean()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            Toastr::success('Cache cleaned successfully!');
        } catch (\Exception $e) {
            Toastr::error('Failed to clean cache: ' . $e->getMessage());
        }
        return redirect()->back();
    }
}
