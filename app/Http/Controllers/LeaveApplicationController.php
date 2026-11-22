<?php

namespace App\Http\Controllers;

use App\Jobs\SendLeaveApplicationDecisionSMS;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Exports\LeaveApplicationList;
use App\Exports\LeaveBalanceList;
use App\Exports\EmpLeaveReport;
use App\Jobs\GenerateAutoEarnLeave;
use App\Models\ApprovalProcess;
use App\Models\EarnLeaveHistory;
use App\Repositories\LeaveApplicationRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Services\Approval\CommonService;

class LeaveApplicationController extends Controller
{
    protected $repository;

    function __construct()
    {
        $this->repository = new LeaveApplicationRepository();
    }

    function history(Request $request)
    {
        $from = null;
        $to = null;
        $data = LeaveApplication::query()->latest();
        if ($request->month) {
            $data->where(function ($q) use ($request) {
                $q->whereMonth('start_date', carbon('01-' . $request->month))
                    ->orWhereMonth('end_date', carbon('01-' . $request->month));
            });
        }

        if (
            LeaveApplication::can_approve('leave', user())
            && !role_can('leave.show')
            && !role_can('leave')
            && !user()->admin_of_super_departments
        ) {
            $data = LeaveApplication::related_by(user()->id, 'leave');
        }

        if (
            !LeaveApplication::can_approve('leave', user())
            && !role_can('leave.show')
            && !role_can('leave')
            && !user()->admin_of_super_departments
        ) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('id', user()->id);
            });
        }
        if (
            !LeaveApplication::can_approve('leave', user())
            && !role_can('leave.show')
            && !role_can('leave')
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
                return LeaveApplicationList::download_excel($data->get(), 'Leave Application List.xlsx');
            }
            if ($as == 'pdf') {
                return LeaveApplicationList::download_pdf($data->get(), 'Leave Application List.pdf');
            }
            if ($as == 'print') {
                return LeaveApplicationList::print($data->get());
            }
        }

        return view('leaves.history', [
            'data' => $data->orderBy('updated_at', 'desc')->paginate(30),
            'from' => $from,
            'to' => $to
        ]);
    }

    function apply()
    {
        return view('leaves.apply');
    }

    function apply_save(Request $request)
    {
        try {
            //code...
            $user = User::findOrFail($request->employee);
            $leave = $this->repository->apply($request, user(), $user);

            if (!$leave) {
                throw new \Exception('Leave application failed! Please try again.');
            }
        } catch (\Throwable $th) {
            Toastr::error($th->getMessage());
            return redirect()->back()->withInput();
        }

        Toastr::success('Leave applied successfully');
        return redirect()->route('leaves.all');
    }

    function get_list(Request $request, $type, $from, $to)
    {
        $data = LeaveApplication::query()->latest();

        // filter limitations according to access
        $canApproveEmps = ApprovalProcess::canApproveEmployees('leave', user());
        if (
        role_can('leave')
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
                        $data = LeaveApplication::related_by(user()->id, 'leave', 'pending');
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
        $data = $this->get_list($request, 'pending', $from, $to);
        $data = LeaveApplication::pending_for_approval($data->orderBy('updated_at', 'desc')->get());
        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return LeaveApplicationList::download_excel($data, 'Leave Application List.xlsx');
            }
            if ($as == 'pdf') {
                return LeaveApplicationList::download_pdf($data, 'Leave Application List.pdf');
            }
            // if ($as == 'docx') {
            //     return EmployeeList::download_docx($data, 'Employees.docx');
            // }
            if ($as == 'print') {
                return LeaveApplicationList::print($data);
            }
        }
        return view('leaves.all', [
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
                return LeaveApplicationList::download_excel($data->get(), 'Leave Application List.xlsx');
            }
            if ($as == 'pdf') {
                return LeaveApplicationList::download_pdf($data->get(), 'Leave Application List.pdf');
            }
            if ($as == 'print') {
                return LeaveApplicationList::print($data->get());
            }
        }

        return view('leaves.all', [
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
                return LeaveApplicationList::download_excel($data->get(), 'Leave Application List.xlsx');
            }
            if ($as == 'pdf') {
                return LeaveApplicationList::download_pdf($data->get(), 'Leave Application List.pdf');
            }
            // if ($as == 'docx') {
            //     return EmployeeList::download_docx($data->get(), 'Employees.docx');
            // }
            if ($as == 'print') {
                return LeaveApplicationList::print($data->get());
            }
        }

        return view('leaves.all', [
            'data' => $data->paginate(25),
            'from' => $from,
            'to' => $to,
            'type' => 'rejected',
        ]);
    }
public function approved_history($id)
{
    $leave = LeaveApplication::with('employee.department')->findOrFail($id);
    $user = user();

    // Match the employee-specific access restrictions in show().
    if ($user->role === 'employee' && !role_can('leave')) {
        $canApproveEmps = ApprovalProcess::canApproveEmployees('leave', $user)
            ?? ['employees' => collect()];

        $allowedEmployeeIds = collect($canApproveEmps['employees'] ?? [])
            ->pluck('id')
            ->push($user->id)
            ->unique()
            ->toArray();

        $adminSuperDepartments = $user->admin_of_super_departments ?? [];
        $employee = $leave->employee;

        abort_if(
            !in_array($employee?->department?->super_department_id, $adminSuperDepartments)
            && !in_array($employee?->id, $allowedEmployeeIds)
            && $leave->employee_id != $user->id,
            403
        );
    }

    // Only approvers or authorized leave administrators can use this feature.
    abort_unless(
        $user->is_super_admin()
        || role_can('leave', $user)
        || role_can('leave.show', $user)
        || $leave->can_approve_this()
        || in_array(
            $leave->employee?->department?->super_department_id,
            $user->admin_of_super_departments ?? []
        ),
        403
    );

    $year = now()->year;

    $approvedLeaves = LeaveApplication::query()
        ->with('leave_type')
        ->where('employee_id', $leave->employee_id)
        ->where('status', 'approved')
        ->whereDate('start_date', '<=', $year . '-12-31')
        ->whereDate('end_date', '>=', $year . '-01-01')
        ->orderBy('start_date')
        ->get();

    return view('leaves.approved-history', compact(
        'leave',
        'year',
        'approvedLeaves'
    ));
}
    public function show($id)
    {
        $leave = LeaveApplication::with('employee.department')->findOrFail($id);
        $user = user();

        if ($user->role === 'employee' && !role_can('leave')) {

            $canApproveEmps = ApprovalProcess::canApproveEmployees('leave', $user) ?? [
                    'employees' => collect(),
                ];

            $approverEmployeeIds = collect($canApproveEmps['employees'] ?? [])
                ->pluck('id')
                ->push($user->id)
                ->unique()
                ->toArray();

            $adminSuperDepartments = $user->admin_of_super_departments ?? [];

            $emp = $leave->employee;

            abort_if(
                !in_array($emp->department ?->super_department_id, $adminSuperDepartments)
            && !in_array($emp->id, $approverEmployeeIds)
            && $leave->employee_id !== $user->id,
            403
        );
    }

        if (request()->ajax()) {
    return view('leaves.show', compact('leave'));
}

return view('leaves.show-page', compact('leave'));
    }


    public function update_leave_type(Request $request, $id)
    {
        abort_if(!user()->is_super_admin(), 403);

        $validated = $request->validate([
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
        ]);

        $leave = LeaveApplication::findOrFail($id);
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        \Illuminate\Support\Facades\DB::transaction(function () use ($leave, $leaveType) {
            $leave->leave_type_id = $leaveType->id;
            $leave->type = $leaveType->name;
            $leave->save();
        });

        return redirect()->back()->with('success', 'Leave type updated successfully.');
    }
  
    /**
     * TYPES methods
     */
    function types()
    {

        return view('leaves.types', [
            'types' => LeaveType::latest()
                ->get(),
        ]);
    }

    function types_save(Request $request)
    {

        $request->validate([
            'name' => 'required|max:150',
            'bg_color' => 'required',
        ]);
        $type = new LeaveType();
        $type->name = $request->name;
        $type->is_paid = $request->paid == 1;
        $type->bg_color = $request->bg_color;
        $type->is_for_all_user = $request->is_for_all_user == 1;
        $type->affects_leave_balance = $request->affects_leave_balance == 1;
        $type->save();
        return redirect()->route('leaves.types')->with('success', 'Leave type created successfully');
    }

    function types_update(Request $request)
    {
        $request->validate([
            'name' => 'required|max:150',
            'bg_color' => 'required'
        ]);
        // dd($request->all());
        $type = LeaveType::findOrFail($request->id);
        if ($type->is_editable == false) {
            return redirect()->route('leaves.types')
                ->with('error', 'Leave type is not editable');
        }
        $type->name = $request->name;
        $type->is_paid = $request->paid == 1;
        $type->bg_color = $request->bg_color;

        $type->is_for_all_user = $request->is_for_all_user == 1;
        $type->affects_leave_balance = $request->affects_leave_balance == 1;
        $type->save();
        return redirect()->route('leaves.types')->with('success', 'Leave type updated successfully');
    }

    function types_delete(Request $request)
    {
        if (
        LeaveApplication::where('leave_type_id', $request->id)->exists()
        ) {
            return redirect()->route('leaves.types')->with('error', 'Leave type is used in some applications');
        }
        $type = LeaveType::findOrFail($request->id);
        $type->delete();
        return redirect()->route('leaves.types')->with('success', 'Leave type deleted successfully');
    }

    function balance(Request $request)
    {
        abort_if(!(
            role_can('leave')
            || role_can('leave.balance-assign')
            || role_can('leave.balance-assign.self.super_department')
        ), 403);

        $employees = User::active_employees()->orderBy('id');

        if ($request->employee) {
            $employees->where('id', $request->employee);
        }

        if ($request->designation) {
            $employees->where('designation_id', $request->designation);
        }

        if ($request->department) {
            $employees->where('department_id', $request->department);
        }

        if ($request->super_department) {
            $employees->whereHas('department', function ($query) use ($request) {
                $query->where('super_department_id', $request->super_department);
            });
        }

        $employees = in_array($request->export, ['pdf', 'print'], true)
    ? $employees->get()
    : $employees->paginate(25)->withQueryString();

        $types = LeaveType::orderBy('id')
            // ->where('is_editable', true)
            ->get();
        $year = $request->year ?? date('Y');

        if ($request->export) {
            $as = $request->export;
            // if ($as == 'xlsx') {
            //     return EmployeeList::download_excel($employees->get(), 'Employees.xlsx');
            // }
            if ($as == 'pdf') {
                return LeaveBalanceList::download_pdf($employees, $types, $year, 'Leave Balance.pdf');
            }
            // if ($as == 'docx') {
            //     return LeaveBalanceList::download_docx($employees->get(), 'Employees.docx');
            // }
            if ($as == 'print') {
                return LeaveBalanceList::print($employees, $types, $year);
            }
        }
        return view('leaves.balance', [
            'employees' => $employees,
            'types' => $types,
            'year' => $year,
        ]);
    }

    function balance_employee(Request $request)
    {
        return LeaveBalance::employee_balance($request->employee, $request->type, $request->year ?? date('Y'));
    }

    function balance_update_default(Request $request)
    {
        abort_if(!(
        role_can('leave')
        ), 403);
        // dd($request->all());
        foreach ($request->types ?? [] as $id => $val) {
            Setting::emp_leave_balance(0, $id, $request->year, $val);
        }
        Toastr::success('Leave balance updated successfully');
        return redirect()->back();
    }

    function balance_update($user_id, Request $request)
    {
        $emp = User::findOrFail($user_id);
        abort_if(!(
            role_can('leave')
            || role_can('leave.balance-assign')
            || role_can('leave.balance-assign.self.super_department')
        ), 403);
        foreach ($request->types ?? [] as $id => $val) {
            Setting::emp_leave_balance($user_id, $id, $request->year, $val);
        }
        Toastr::success('Leave balance updated successfully');
        return redirect()->back();
    }

    function destroy(LeaveApplication $leave)
    {
        abort_if(!role_can('leave'), 403);
        $leave->delete();
        app(CommonService::class)->onDelete($leave, user(), [
            'module' => 'leave'
        ]);
        Toastr::success('Leave deleted successfully :)', 'Success');
        return redirect()->back();
    }

    public function print($id)
    {
        // $this->can();
        $leave = LeaveApplication::findOrFail($id);

        if (config('app.customer') == 'sas') {
            return view('leaves.print-sas', [
                'leave' => $leave,
            ]);
        }
        // $formatted = $leave->formatted();
        return view('leaves.print', [
            'leave' => $leave,
        ]);
    }

    public function download($id)
    {
        // $this->can();
        $leave = LeaveApplication::findOrFail($id);

        if (config('app.customer') == 'sas') {
            $html = view('leaves.print-sas', [
                'leave' => $leave,
            ])->render();
        } else {
            $html = view('leaves.print', [
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
        return $pdf->stream($leave->employee->name . ' - ' . $leave->leave_type->name . ' - ' . $leave->start_date->format('Y-m-d') . ' - ' . $leave->end_date->format('Y-m-d') . '.pdf');
    }

    public function emp_leave_report(Request $request)
    {
        $start_date = carbon($request->start_date ?? now()->startOfMonth());
        $end_date = carbon($request->end_date ?? now()->endOfMonth());
        $data = LeaveApplication::latest();
        $employee = User::active_employees()->find($request->employee);
        if ($request->employee) {
            $data->where('employee_id', $request->employee);
        }
        if ($request->start_date) {
            $data->where('start_date', '>=', $start_date);
        }
        if ($request->end_date) {
            $data->where('end_date', '<=', $end_date);
        }

        if ($request->export) {
            $as = $request->export;
            // if ($as == 'xlsx') {
            //     return LeaveApplicationList::download_excel($data->get(), 'Leave Application List.xlsx');
            // }
            if ($as == 'pdf') {
                return EmpLeaveReport::download_pdf($data->get(), $employee, $start_date, $end_date, 'Employee Leave Report.pdf');
            }
            // if ($as == 'docx') {
            //     return EmployeeList::download_docx($data->get(), 'Employees.docx');
            // }
            if ($as == 'print') {
                return EmpLeaveReport::print($data->get(), $employee, $start_date, $end_date);
            }
        }


        return view('leaves.emp_leave_report', [
            'data' => $data->paginate(100),
            'employee' => $employee,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'employee' => $employee,
        ]);
    }

    function get_duration(Request $request)
    {
        $start_date = carbon($request->start);
        $end_date = carbon($request->end);
        return response()->json(LeaveApplication::getDurationDays(
            $start_date, $end_date, User::findOrFail($request->user_id)
        ));
    }

    function delete($id)
    {
        LeaveApplication::find($id)->delete();
        return null;
    }

    function earn_leave_history()
    {
        abort_if(!role_can('leave'), 403);
        return view('leaves.earn_leave_history', [
            'data' => EarnLeaveHistory::latest()
                ->with(['user.department', 'user.designation'])
                ->paginate(100),
        ]);
    }

    function earn_leave_policy(Request $request)
    {
        abort_if(!role_can('leave'), 403);

        if ($request->key === 'enabled_one_earn_leave_after_each_weekend') {
            Setting::enabled_one_earn_leave_after_each_weekend($request->enabled_one_earn_leave_after_each_weekend == 'on');
            return redirect()->route('leaves.earn_leave.policy')
                ->with('success', 'One earn leave after each weekend setting updated successfully');
        }
        if ($request->key === 'one_earn_leave_after_each_weekend') {
            Setting::one_earn_leave_after_each_weekend($request->one_earn_leave_after_each_weekend);
            return redirect()->route('leaves.earn_leave.policy')
                ->with('success', 'One earn leave after each weekend setting updated successfully');
        }
        return view('leaves.earn_leave_policy', [
            'enabled_one_earn_leave_after_each_weekend' => Setting::enabled_one_earn_leave_after_each_weekend(),
            'one_earn_leave_after_each_weekend' => Setting::one_earn_leave_after_each_weekend(),
        ]);
    }
}