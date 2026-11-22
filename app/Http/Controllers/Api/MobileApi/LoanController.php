<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Classes\ApiResponseClass;
use App\Http\Controllers\Controller;
use App\Models\ApprovalProcess;
use App\Models\Loan;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Services\LoanService;

class LoanController extends Controller
{

    protected $repository;


    // public function __construct(protected $user, Request $request)
    // {
    //     $this->user = $request->auth_user;
    // }

    function index(Request $request)
    {
        $from = $request->start_date ? carbon($request->start_date) : carbon()->startOfMonth();
        $to = $request->end_date ? carbon($request->end_date) : carbon()->endOfMonth();
        // $type = $request->type ?? 'pending';

        $data = Loan::query();

        $user = $request->auth_user;
        // filter limitations according to access
        $canApproveEmps = ApprovalProcess::canApproveEmployees('loan', $user);
        if(
            role_can('loans', $user)
        )
        {
            // if()
        }else
        if($canApproveEmps && $canApproveEmps['employees'])
        {
            $data->whereIn('user_id', [
                ...$canApproveEmps['employees']->pluck('id'),
                $user->id
            ]);
        }
        else {
            $data->where('user_id', $user->id);
        }

        if ($request->employee) {
            $data->where('user_id', $request->employee);
        }
        $data->whereBetween('issue_date', [$from, $to]);

        if ($request->filter !== 'all' && $request->filter !== null) {
            $data->where('status', $request->filter);
        }

        // if ($request->export) {
        //     $as = $request->export;
        //     if ($as == 'xlsx') {
        //         return LoansExportList::download_excel($data->get(), 'Loans.xlsx');
        //     }
        //     if ($as == 'pdf') {
        //         return LoansExportList::download_pdf($data->get(), 'Loans.pdf');
        //     }
        //     // if ($as == 'docx') {
        //     //     return EmployeeList::download_docx($data->get(), 'Employees.docx');
        //     // }
        //     if ($as == 'print') {
        //         return LoansExportList::print($data->get());
        //     }
        // }

        // $data = Loan::where('employee_id', $user->id);
        // if ($request->filter !== 'all' && $request->filter !== null) {
        //     $data->where('status', $request->filter);
        // }
        // var_dump($data);
        return ApiResponseClass::sendResponse(
            $data->orderBy('id', 'desc')
                ->with('employee.department.super_department', 'employee.designation')
                ->paginate(10000, ['*'], 'page', $request->page ?? 1),
            'Loan Applications Fetched Successfully'
        );
    }

    function apply(Request $request)
    {

        $user = $request->auth_user;

        abort_if(!role_can('loans.self', $user) && !role_can('loans', $user) && !Loan::can_approve('loan', $user), 403);
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

        if ($user->is_super_admin()) {
            $loan->update(['status' => 'approved']);
            $loan->approvedAll();
        }
        return ApiResponseClass::sendResponse(
            $loan,
            'Loan Applied Successfully'
        );
    }

    public function show(Request $request, $id)
    {
        $user = $request->auth_user;
        $get_loan = Loan::with('paymentSchedules', 'employee.department.super_department', 'employee.designation');
        $loan = $get_loan->find($id);
        abort_if(!role_can('loans.self', $user) && !role_can('loans', $user) && $loan->user_id != $user->id, 403);
        $total_interest = $loan->paymentSchedules()->sum('interest');
        $total_amount = $loan->paymentSchedules()->sum('amount');

        $approvalSteps = $loan->getApprovalStepsApi($user->id) ?: [];

        $interest_rate = Setting::loan_interest_rate();

        return ApiResponseClass::sendResponse(
            [
                'data' => $loan,
                'approvalSteps' => $approvalSteps,
                'interest_rate' => $interest_rate,
                'total_interest' => $total_interest,
                'total_amount' => $total_amount
            ],
            'Loan Application Fetched Successfully'
        );
    }
}
