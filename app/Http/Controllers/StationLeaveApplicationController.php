<?php

namespace App\Http\Controllers;

use App\Jobs\SendLeaveApplicationDecisionSMS;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Role;
use App\Models\Setting;
use App\Models\StationLeaveApplication;
use App\Models\User;
use App\Repositories\LeaveApplicationRepository;
use App\Services\Approval\CommonService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Exports\LeaveApplicationList;
use App\Exports\StationLeaveApplicationList;
use App\Exports\LeaveBalanceList;
use App\Exports\EmpLeaveReport;
use App\Jobs\GenerateAutoEarnLeave;
use App\Models\ApprovalProcess;
use App\Models\EarnLeaveHistory;
use App\Repositories\StationLeaveApplicationRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Http\Requests\StationLeave\StoreStationLeaveRequest;

class StationLeaveApplicationController extends Controller
{
    protected $repository;

    function __construct()
    {
        $this->repository = new StationLeaveApplicationRepository();
    }

    function history(Request $request)
    {
        $from = null;
        $to = null;
        $data = StationLeaveApplication::query()->latest()->with(['employee.department.super_department', 'employee.designation', 'approvalRecords']);
        if ($request->month) {
            $data->where(function ($q) use ($request) {
                $q->whereMonth('start_date', carbon('01-' . $request->month))
                    ->orWhereMonth('end_date', carbon('01-' . $request->month));
            });
        }

        if (
            StationLeaveApplication::can_approve('station_leave', user())
            && !role_can('station_leave')
            && !user()->admin_of_super_departments
        ) {
            $data = StationLeaveApplication::related_by(user()->id, 'station_leave');
        }

        if (
            !StationLeaveApplication::can_approve('station_leave', user())
            && !role_can('station_leave')
            && !user()->admin_of_super_departments
        ) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('id', user()->id);
            });
        }
        if (
            !StationLeaveApplication::can_approve('station_leave', user())
            && !role_can('station_leave')
            && user()->admin_of_super_departments
        ) {
            $data->whereHas('employee', function ($query) use ($request) {
                $query->whereHas('department', function ($query) use ($request) {
                    return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                });
            });
        }

        if ($request->employee) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('id', $request->employee);
            });
        }
        if ($request->department) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('department_id', $request->department);
            });
        } elseif ($request->super_department) {
            $data->whereHas('employee', function ($query) use ($request) {
                $q = $query->whereHas('department', function ($query) use ($request) {
                    return $query->where('super_department_id', $request->super_department);
                });
                if (user()->managed_employees->pluck('id')) {
                    $q->orWhereIn('id', user()->managed_employees->pluck('id'));
                }
                return $q;
            });
        }

        if ($request->designation) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('designation_id', $request->designation);
            });
        }

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return StationLeaveApplicationList::download_excel($data->get(), 'Station Leave Application List.xlsx');
            }
            if ($as == 'pdf') {
                return StationLeaveApplicationList::download_pdf($data->get(), 'Station Leave Application List.pdf');
            }
            if ($as == 'print') {
                return StationLeaveApplicationList::print($data->get());
            }
        }

        return view('stationLeaves.history', [
            'data' => $data->orderBy('updated_at', 'desc')->paginate(30),
            'from' => $from,
            'to' => $to
        ]);
    }

    function apply()
    {
        return view('stationLeaves.apply');
    }

    public function apply_save(StoreStationLeaveRequest $request)
    {
        $data = $request->validated();

        // handle file here (BEST PRACTICE)
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store('station-leaves');
            $data['attachment_name'] = $file->getClientOriginalName();
        }

        try {
            $employee = User::findOrFail($data['employee_id']);
            $this->repository->apply($data, user(), $employee);
        } catch (\Throwable $e) {


            Toastr::error($e->getMessage());
            return back()->withInput();
        }

        Toastr::success('Station Leave applied successfully');
        return redirect()->route('station.leave.pending');
    }

    function get_list(Request $request, $type, $from, $to)
    {
        $data = StationLeaveApplication::query()->latest()->with(['employee.department.super_department', 'employee.designation', 'approvalRecords']);

        // filter limitations according to access
        $canApproveEmps = ApprovalProcess::canApproveEmployees('station_leave', user());
        if (
        role_can('station_leave')
        ) {
            // if()
        } else
            if (
            user()->admin_of_super_departments
            ) {
                $data->whereHas('employee', function ($query) {
                    return $query->whereHas('department', function ($query) {
                        return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                    });
                });
            } else
                if ($canApproveEmps && $canApproveEmps['employees']) {
                    if (strtolower($type) === 'pending') {
                        $data = StationLeaveApplication::related_by(user()->id, 'station_leave', 'pending');
                    } else {
                        $data->whereIn('employee_id', [...$canApproveEmps['employees']->pluck('id'), user()->id]);
                    }
                } else {
                    $data->whereHas('employee', function ($query) use ($request) {
                        return $query->where('id', user()->id);
                    });
                }

        if ($request->employee) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('id', $request->employee);
            });
        }

        if ($request->department) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('department_id', $request->department);
            });
        } elseif ($request->super_department) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->whereHas('department', function ($query) use ($request) {
                    return $query->where('super_department_id', $request->super_department);
                })
                    ->orWhereIn('id', user()->managed_employees->pluck('id'));
            });
        }
        if ($request->designation) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('designation_id', $request->designation);
            });
        }
        if ($from && $to) {
            $data->where(function ($q) use ($from, $to) {
                $q->where('start_date', '<=', $to)
                    ->where('end_date', '>=', $from);
            });
        }
        $data->where('status', strtolower($type));
        return $data;
    }

    function all(Request $request)
    {
        $from = null;
        $to = null;
        if ($request->start_date && $request->end_date) {
            $from = carbon($request->start_date)->startOfDay();
            $to = carbon($request->end_date)->endOfDay();
        }
        // dd($request->all());
        $data = $this->get_list($request, 'pending', $from, $to);

        $data = StationLeaveApplication::pending_for_approval($data->orderBy('updated_at', 'desc')->get());

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return StationLeaveApplicationList::download_excel($data, 'Station Leave Application List.xlsx');
            }
            if ($as == 'pdf') {
                return StationLeaveApplicationList::download_pdf($data, 'Station Leave Application List.pdf');
            }
            // if ($as == 'docx') {
            //     return EmployeeList::download_docx($data, 'Employees.docx');
            // }
            if ($as == 'print') {
                return StationLeaveApplicationList::print($data);
            }
        }


        return view('stationLeaves.all', [
            'data' => $data,
            'from' => $from,
            'to' => $to,
            'type' => 'pending',
        ]);
    }

    function all_approve(Request $request)
    {
        $from = now()->startOfMonth();
        $to = now()->endOfMonth();
        if ($request->start_date && $request->end_date) {
            $from = carbon($request->start_date)->startOfDay();
            $to = carbon($request->end_date)->endOfDay();
        }
        $data = $this->get_list($request, 'approved', $from, $to);

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return StationLeaveApplicationList::download_excel($data->get(), 'Station Leave Application List.xlsx');
            }
            if ($as == 'pdf') {
                return StationLeaveApplicationList::download_pdf($data->get(), 'Station Leave Application List.pdf');
            }
            if ($as == 'print') {

                return StationLeaveApplicationList::print($data->get());
            }
        }

        return view('stationLeaves.all', [
            'data' => $data->orderBy('updated_at', 'desc')->paginate(25),
            'from' => $from,
            'to' => $to,
            'type' => 'approved',
        ]);
    }

    function all_rejected(Request $request)
    {
        $from = now()->startOfMonth();
        $to = now()->endOfMonth();
        if ($request->start_date && $request->end_date) {
            $from = carbon($request->start_date)->startOfDay();
            $to = carbon($request->end_date)->endOfDay();
        }
        $data = $this->get_list($request, 'rejected', $from, $to);

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return StationLeaveApplicationList::download_excel($data->get(), 'Station Leave Application List.xlsx');
            }
            if ($as == 'pdf') {
                return StationLeaveApplicationList::download_pdf($data->get(), 'Station Leave Application List.pdf');
            }
            // if ($as == 'docx') {
            //     return EmployeeList::download_docx($data->get(), 'Employees.docx');
            // }
            if ($as == 'print') {
                return StationLeaveApplicationList::print($data->get());
            }
        }

        return view('stationLeaves.all', [
            'data' => $data->paginate(25),
            'from' => $from,
            'to' => $to,
            'type' => 'rejected',
        ]);
    }

    function show($id)
    {
        $leave = StationLeaveApplication::findOrFail($id);

        if (user()->role === 'employee' && !role_can('station_leave')) {
            $canApproveEmps = ApprovalProcess::canApproveEmployees('station_leave', user());
            $emp = $leave->employee;

            $approvableIds = [];

            if ($canApproveEmps && isset($canApproveEmps['employees'])) {
                $approvableIds = $canApproveEmps['employees']->pluck('id')->toArray();
            }

            $approvableIds[] = user()->id; // Add self

            abort_if(
                !in_array($emp ?->id, $approvableIds)
            && !in_array($emp ?->department ?->super_department_id, user()->admin_of_super_departments ?: [])
            && $leave->employee_id != user()->id,
            403
        );
    }


        if (request()->ajax()) {
    return view('stationLeaves.show', [
        'leave' => $leave,
    ]);
}

return view('stationLeaves.show-page', [
    'leave' => $leave,
]);
    }


    function destroy(StationLeaveApplication $leave)
    {
        abort_if(!role_can('station_leave'), 403);
        $leave->delete();

        app(CommonService::class)->onDelete($leave, user(), [
            'module' => 'station_leave'
        ]);

        Toastr::success('Station Leave deleted successfully :)', 'Success');
        return redirect()->back();
    }

    public function print($id)
    {
        // $this->can();
        $leave = StationLeaveApplication::findOrFail($id);

        if (config('app.customer') == 'sas') {
            return view('stationLeaves.print-sas', [
                'leave' => $leave,
            ]);
        }
        // $formatted = $leave->formatted();
        return view('stationLeaves.print', [
            'leave' => $leave,
        ]);
    }

    public function download($id)
    {
        // $this->can();
        $leave = StationLeaveApplication::findOrFail($id);


        if (config('app.customer') == 'sas') {
            $html = view('stationLeaves.print-sas', [
                'leave' => $leave,
            ])->render();
        } else {
            $html = view('stationLeaves.print', [
                'leave' => $leave,
            ])->render();
        }

        $pdf = new Dompdf(([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'dpi' => 110,
        ]));

        $pdf->loadHtml($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();
        return $pdf->stream($leave->employee->name . ' - ' . "Station Leave" . ' - ' . $leave->start_date->format('Y-m-d') . ' - ' . $leave->end_date->format('Y-m-d') . '.pdf');
    }

    function get_duration(Request $request)
    {
        $start_date = carbon($request->start);
        $end_date = carbon($request->end);
        return response()->json(StationLeaveApplication::getDurationDays(
            $start_date, $end_date, User::findOrFail($request->user_id)
        ));
    }

    function delete($id)
    {
        StationLeaveApplication::find($id)->delete();
        return null;
    }

}