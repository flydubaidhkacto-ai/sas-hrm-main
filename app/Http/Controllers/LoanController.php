<?php

namespace App\Http\Controllers;

use App\Exports\LoansExportList;
use App\Jobs\SendLoanSMS;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessStep;
use App\Models\ApprovalRecord;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Setting;
use App\Models\User;
use App\Services\Approval\CommonService;
use App\Services\LoanService;
use App\Traits\ApprovalProcessModal;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoanController extends Controller
{

    use ApprovalProcessModal;

    function loans(Request $request)
    {
        $from = $request->start_date ? carbon($request->start_date) : carbon()->startOfMonth();
        $to = $request->end_date ? carbon($request->end_date) : carbon()->endOfMonth();
        $type = $request->type;

        $data = Loan::query();

        // filter limitations according to access
        $canApproveEmps = ApprovalProcess::canApproveEmployees('loan', user());
        if(
            role_can('loans')
        )
        {
            // if()
        }else
        if(
            user()->admin_of_super_departments
        )
        {
            $data->whereHas('employee.department', function ($query) {
                $query->whereIn('super_department_id', user()->admin_of_super_departments);
            });
        }else
        if($canApproveEmps && $canApproveEmps['employees'])
        {
            $data->whereIn('user_id', [
                ...$canApproveEmps['employees']->pluck('id'), user()->id
            ]);
        }
        else {
            $data->where('user_id', user()->id);
        }

        if ($request->employee) {
            $data->where('user_id', $request->employee);
        }
        $data->whereBetween('issue_date', [$from, $to]);

        if ($request->department) {
            $data->whereHas('employee', function ($query) use ($request) {
                $query->where('department_id', $request->department);
            });
        }

        if ($request->super_department) {
            $data->whereHas('employee.department', function ($query) use ($request) {
                $query->where('super_department_id', $request->super_department);
            });
        }

        if ($request->designation) {
            $data->whereHas('employee', function ($query) use ($request) {
                $query->where('designation_id', $request->designation);
            });
        }

        if ($type == 'Approved') {
            $data->where('status', 'approved');
        } else
        if ($type == 'Rejected') {
            $data->where('status', 'rejected');
        } else
        if($type == 'Pending') {
            $data->where('status', 'pending');
        }
        $data = $data->get();
        if($type == 'Pending') {
            $data = Loan::pending_for_approval($data);
        }
        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return LoansExportList::download_excel($data, 'Loans.xlsx');
            }
            if ($as == 'pdf') {
                return LoansExportList::download_pdf($data, 'Loans.pdf');
            }
            // if ($as == 'docx') {
            //     return EmployeeList::download_docx($data, 'Employees.docx');
            // }
            if ($as == 'print') {
                return LoansExportList::print($data);
            }
        }

        return view('loans.index', [
            'data' => $data,
            'from' => $from,
            'to' => $to,
            'type' => $type
        ]);
    }
    function create()
    {
        return view('loans.apply');
    }
    function apply(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:99999999999',
            'reason' => 'nullable|string',
            'cut_off_as' => 'in:once,monthly',
            'tenure_in_months' => 'nullable|required_if:cut_off_as,monthly|numeric|min:1|max:' . $request->amount,
            'employee' => 'required|exists:users,id',
            'date' => 'required|date',
        ]);
        $data['user_id'] = $request->employee;
        $data['user_id'] = $data['employee'];
        unset($data['employee']);
        $data['issue_date'] = $data['date'];
        unset($data['date']);

        // authorize
        // check if current user can apply
        $logged_user = user();
        $canApproveEmps = \App\Models\ApprovalProcess::canApproveEmployees('loan', $logged_user);
        if(
            (!$canApproveEmps ||
            !$canApproveEmps['employees']->first(function ($emp) use ($data) {
                return $emp->id == $data['user_id'];
            }))
            && !role_can('loans')
            && !is_admin_of_super_department(
                User::find($data['user_id'])?->department?->super_department_id
            )
            && $data['user_id'] != $logged_user->id
        ) {
            Toastr::error('You are not authorized to apply this loan type');
            return redirect()->back()->withInput();
        }

        if ($data['cut_off_as'] == 'once') {
            $data['cut_off_amount'] = null;
            $data['tenure_in_months'] = 0;
            $loan = Loan::create($data);
        } else {
            $schedule = LoanService::previewPaymentSchedule(
                $data['amount'],
                $data['tenure_in_months'],
                'fixed',
                Setting::loan_interest_rate(),
            );
            $data['cut_off_amount'] = $schedule['emi'];
            $loan = Loan::create($data);
            foreach ($schedule['emi_schedule'] as $schedule) {
                $loan->paymentSchedules()->create([
                    'month' => $schedule['month'],
                    'amount' => $schedule['emi'],
                    'principal' => $schedule['principal_paid'],
                    'interest' => $schedule['interest_paid'],
                    'remaining' => $schedule['remaining_balance'],
                ]);
            }
        }

        if (user()->is_super_admin()) {
            $loan->update(['status' => 'approved']);
            $loan->approvedAll();
        } else {
            try {
                app(CommonService::class)->onApply($loan, user(), [
                    'module' => 'loan'
                ]);
            } catch (\Throwable $e) {
                Log::error('Loan onApply failed: ' . $e->getMessage());
            }
        }

        Toastr::success('Loan Application Submitted Successfully', 'Success');
        $date = carbon($request->date)->format('d-m-Y');
        return redirect()->route('loans', ['from' => $date, 'to' => $date]);
    }

    function destroy($id)
    {
        $data = Loan::findOrFail($id);
        abort_if(!role_can('loans') && !is_admin_of_super_department(
            $data->employee?->department?->super_department_id
        ), 403);

        LoanPayment::where('loan_id', $id)->delete();
        $data->delete();
        Toastr::success('Loan Deleted Successfully', 'Success');
        return redirect()->back();
    }
    function previewPaymentSchedule(Request $request)
    {
        return LoanService::previewPaymentSchedule(
            $request->amount,
            $request->tenure_in_months,
            'fixed',
            Setting::loan_interest_rate(),
        );
    }
    function bulkLoans()
    {
        abort_if(!role_can('loans') && !user()->admin_of_super_departments, 403);

        return view('loans.bulk', [
            'employees' => User::active_employees()
                ->when(!role_can('loans') && user()->admin_of_super_departments, function ($q) {
                    $q->whereHas('department', fn($q2) => $q2->whereIn('super_department_id', user()->admin_of_super_departments));
                })
                ->get(),
        ]);
    }
    function bulkApply(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|array',
        ]);
        // dd($data);
        foreach ($data['amount'] as $employee_id => $amount) {
            // dd($data['date']);
            if ($amount == 0) {
                continue;
            }
            $loan_data = [
                'user_id' => $employee_id,
                'amount' => $amount,
                'issue_date' => $data['date'],
                'cut_off_as' => 'once',
            ];
            if(
                !role_can('loans')
                && !is_admin_of_super_department(
                    User::find($employee_id)?->department?->super_department_id
                )
            ) {
                continue;
            }
            $previous_loan = Loan::where('user_id', $employee_id)
                ->where('status', 'approved')
                ->where('cut_off_as', 'once')
                ->whereDate('issue_date', '=', $data['date'])
                ->latest()
                ->first();
            if ($previous_loan) {
                $loan_data['amount'] += $previous_loan->amount;
                $previous_loan->update($loan_data);
            } else {
                $loan = Loan::create($loan_data);

                if (user()->is_super_admin()) {
                    $loan->update(['status' => 'approved']);
                    $loan->approvedAll();
                } else {
                    try {
                        app(CommonService::class)->onApply($loan, user(), [
                            'module' => 'loan'
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Loan bulk onApply failed: ' . $e->getMessage());
                        $loan->update(['status' => 'pending']);
                    }
                }
            }
        }
        Toastr::success('Loans Applied Successfully', 'Success');
        return redirect()->back();
    }
}
