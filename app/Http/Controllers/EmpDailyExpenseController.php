<?php

namespace App\Http\Controllers;

use App\Models\EmpDailyExpense;
use App\Models\EmpDailyExpenseCategory;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Exports\EmpDailyExpenseList;
use App\Models\ApprovalProcess;
use App\Models\User;

class EmpDailyExpenseController extends Controller
{
    function index(Request $request)
    {
        $from = $request->from_date ? carbon($request->from_date) : now()->subDays(31);
        $to = $request->to_date ? carbon($request->to_date)->endOfDay() : now();
        $type = $request->type;
        if (!$type) {
            $type = 'Pending';
        }
        $data = EmpDailyExpense::query()
            ->whereBetween('date', [$from->format('Y-m-d') . ' 00:00:00', $to->format('Y-m-d') . ' 23:59:59']);
        $canApproveEmps = ApprovalProcess::canApproveEmployees('emp_daily_expense', user());
        if(
            role_can('emp-expenses')
        )
        {
            // if()
        }else
        if(
            user()->admin_of_super_departments
        )
        {
            $data->where(function($data) {
                $data->whereHas('employee.department', function($query){
                    return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                })
                ->orWhere('employee_id', user()->id);
            });
        }else
        if($canApproveEmps && $canApproveEmps['employees'])
        {
            $data->whereIn('employee_id', [...$canApproveEmps['employees']->pluck('id'), user()->id]);
        }
        else {
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

        if($type !== 'all')
        {
            $data->where('status', strtolower($type));
        }

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return EmpDailyExpenseList::download_excel($data->get(), 'Employee Daily Expense.xlsx');
            }
            if ($as == 'pdf') {
                return EmpDailyExpenseList::download_pdf($data->get(), 'Employee Daily Expense.pdf');
            }
            // if ($as == 'docx') {
            //     return EmployeeList::download_docx($data->get(), 'Employees.docx');
            // }
            if ($as == 'print') {
                return EmpDailyExpenseList::print($data->get());
            }
        }

        $data = $data->get();
        if(strtolower($type) == 'pending')
        {
            $data = EmpDailyExpense::pending_for_approval($data);
        }
        return view('emp-expenses.index', [
            'expenses' => $data,
            'from' => $from,
            'to' => $to,
            'type' => $type,
        ]);
    }
    function create()
    {
        abort_if(!role_can('emp-expenses') && !role_can('emp-expenses.self') && !user()->admin_of_super_departments, 403);
        $expenseCategories = EmpDailyExpenseCategory::where('status', 1)->get();
        return view('emp-expenses.create', compact('expenseCategories'));
    }
    function store(Request $request)
    {
        abort_if(!role_can('emp-expenses') && !role_can('emp-expenses.self') && !is_admin_of_super_department(
            User::find($request->employee)->department?->super_department_id
        ), 403);

        $formData = $request->validate([
            'date' => 'required|date|after:' . now()->subMonth()->format('Y-m-d'),
            'amount' => 'required|numeric|min:1|max:100000',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2550',
            'employee' => 'required|exists:users,id',
            'expensed_category_id' => 'required|exists:emp_daily_expense_categories,id',
        ]);
        $formData['employee_id'] = $formData['employee'];

        if (user()->is_super_admin()) {
            $formData['status'] = 'approved';
        } else {
            $formData['status'] = 'pending';
        }

        unset($formData['employee']);
        $empDailyExpense = EmpDailyExpense::create($formData);

        if (user()->is_super_admin()) {
            $empDailyExpense->approvedAll();
        }

        Toastr::success('Expense added successfully :)', 'Success');
        return redirect()->route('emp-expenses.index', [
            'type' => user()->is_super_admin() ? 'Approved' : 'Pending',
        ]);
    }
    function destroy($id)
    {
        $expense = EmpDailyExpense::findOrFail($id);
        abort_if(!role_can('emp-expenses') && !is_admin_of_super_department(
            $expense->employee?->department?->super_department_id
        ), 403);

        $expense->delete();
        Toastr::success('Expense deleted successfully :)', 'Success');
        return redirect()->route('emp-expenses.index', [
            'type' => user()->is_super_admin() ? 'Approved' : 'Pending',
        ]);
    }
}
