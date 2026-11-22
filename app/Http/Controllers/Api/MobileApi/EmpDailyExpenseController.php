<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Http\Controllers\Controller;
use App\Models\EmpDailyExpense;
use App\Models\EmpDailyExpenseCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Classes\ApiResponseClass;
use App\Models\ApprovalProcess;
use Illuminate\Support\Facades\Log;
use App\Traits\ApprovalProcessModal;

class EmpDailyExpenseController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->start_date ? carbon($request->start_date) : carbon()->startOfMonth();
        $to = $request->end_date ? carbon($request->end_date) : carbon()->endOfMonth();

        $data = EmpDailyExpense::query();
        $canApproveEmps = ApprovalProcess::canApproveEmployees('emp_daily_expense', $request->auth_user);
        if(
            role_can('emp-expenses', $request->auth_user)
        )
        {
            // if()
        }else
        if($canApproveEmps && $canApproveEmps['employees'])
        {
            $data->whereIn('employee_id', [...$canApproveEmps['employees']->pluck('id'), $request->auth_user->id]);
        }
        else {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('id', $request->auth_user->id);
            });
        }

        if ($request->filter !== 'all' && $request->filter !== null) {
            $data->where('status', $request->filter);
        }

        if ($request->employee_id) {
            $data->where('employee_id', $request->employee_id);
        }

        $data->whereBetween('date', [$from, $to]);

        return ApiResponseClass::sendResponse(
            $data->orderBy('id', 'desc')
                ->with('rel_to_expensed_category')
                ->with('employee')
                ->paginate(10000, ['*'], 'page', $request->page ?? 1),
            'Employee Daily Expenses Fetched Successfully'
        );
    }

    public function create()
    {
        return ApiResponseClass::sendResponse(
            [
                'expense_categories' => EmpDailyExpenseCategory::where('status', 1)->get(),
                'employees' => User::active_employees()->get()
            ],
            'Expense Categories and Employees Fetched Successfully'
        );
    }

    public function allEmployees()
    {
        return ApiResponseClass::sendResponse(
            User::active_employees()->get(),
            'All Employees Fetched Successfully'
        );
    }

    function store(Request $request)
    {

        $formData = $request->validate([
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1|max:1000000',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2550',
            'employee_id' => 'required|exists:users,id',
            'expensed_category_id' => 'required|exists:emp_daily_expense_categories,id',
        ]);

        $user = $request->auth_user;

        $formData['employee_id'] = $request->employee_id;
        $formData['expensed_category_id'] = $request->expensed_category_id;
        $formData['date'] = Carbon::parse($request->date)->format('Y-m-d');
        $formData['amount'] = $request->amount;
        $formData['name'] = $request->name;
        $formData['description'] = $request->description;

        if ($user->is_super_admin()) {
            $formData['status'] = 'approved';
        } else {
            $formData['status'] = 'pending';
        }

        // unset($formData['employee_id']);
        $empDailyExpense = EmpDailyExpense::create($formData);

        if ($user->is_super_admin()) {
            $empDailyExpense->approvedAll();
        }
        return ApiResponseClass::sendResponse($empDailyExpense, 'Expense Created Successfully');
    }

    function show(Request $request, $id)
    {
        $user = $request->auth_user;

        $query = EmpDailyExpense::query();

        $data = $query->findOrFail($id);
        $data->load(['employee', 'rel_to_expensed_category']);
        $approvalSteps = $data->getApprovalStepsApi($user->id) ?: [];

        return ApiResponseClass::sendResponse([
            'data' => $data,
            'approvalSteps' => $approvalSteps
        ], 'Employee Daily Expense Fetched Successfully');
    }

}
