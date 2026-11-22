<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeBonusReport;
use App\Models\Bonus;
use App\Models\BonusCategory;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    public function categoryIndex()
    {
        abort_if(!role_can('bonuses') && !role_can('payslip'), 403);
        return view('bonuses.categories', [
            'categories' => BonusCategory::latest()->paginate(1000)
        ]);
    }
    function saveRecordcategory(Request $request)
    {
        abort_if(!role_can('bonuses') && !role_can('payslip'), 403);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        BonusCategory::create([
            'name' => $request->name,
        ]);
        Toastr::success('Added new category successfully :)', 'Success');
        return redirect()->back();
    }
    function updateRecordcategory(Request $request)
    {
        abort_if(!role_can('bonuses') && !role_can('payslip'), 403);
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        // dd($request->d_id);
        (BonusCategory::findOrFail($request->d_id))->update([
            'name' => $request->name,
        ]);
        Toastr::success('updated record successfully :)', 'Success');
        return redirect()->back();
    }
    function deleteRecordcategory(Request $request)
    {
        abort_if(!role_can('bonuses') && !role_can('payslip'), 403);
        BonusCategory::destroy($request->id);
        Toastr::success('Delete record successfully :)', 'Success');
        return redirect()->back();
    }
    function bonuses(Request $request)
    {
        abort_if(!role_can('bonuses') && !role_can('payslip') && !user()->admin_of_super_departments, 403);
        $data = Bonus::query()
            ->with([
                'category',
                'employee.department.super_department',
                'employee.designation'
            ])
            ->when(!role_can('bonuses') && !role_can('payslip') && user()->admin_of_super_departments, function ($q) {
                $q->whereHas('employee.department', function($q2) {
                    $q2->whereIn('super_department_id', user()->admin_of_super_departments);
                });
            });
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
        if ($request->month) {
            $data->whereDate('month', carbon('01-' . $request->month)->format('Y-m-d'));
        } else {
            $data->whereDate('month', now()->startOfMonth()->format('Y-m-d'));
        }
        if ($request->export) {
            $ex = $request->export;
            return EmployeeBonusReport::{'download_' . $ex}($data->get());
        }
        $data = $data->latest()->get();
        return view('bonuses.index', [
            'bonuses' => $data
        ]);
    }
    function create()
    {
        abort_if(!role_can('bonuses') && !role_can('payslip') && !user()->admin_of_super_departments, 403);
        return view('bonuses.create', [
            'categories' => BonusCategory::all()
        ]);
    }
    function generate(Request $request)
    {
        abort_if(!role_can('bonuses') && !role_can('payslip') && !user()->admin_of_super_departments, 403);
        $request->validate([
            'month' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|exists:bonus_categories,id',
            'amount_type' => 'required|in:fixed,percentage,multiply',
        ]);
        $users = User::active_employees()
            ->whereHas('payroll_card')
            ->with('payroll_card')
            ->when(!role_can('bonuses') && !role_can('payslip') && user()->admin_of_super_departments, function ($q) {
                $q->whereHas('department', function($q2) {
                    $q2->whereIn('super_department_id', user()->admin_of_super_departments);
                });
            });
        if ($request->super_department) {
            $users->whereHas('department', function ($query) use ($request) {
                return $query->where('super_department_id', $request->super_department);
            })
                ->orWhereIn('id', user()->managed_employees->pluck('id'));
        }
        if ($request->department) {
            $users->where('department_id', $request->department);
        }
        if ($request->designation) {
            $users->where('designation_id', $request->designation);
        }
        if ($request->employee) {
            $users->where('id', $request->employee);
        }
        if (is_array($request->condition)) {
            $now = now();
            if ($request->condition['months']) {
                $now->subMonths($request->condition['months']);
            }
            if ($request->condition['years']) {
                $now->subYears($request->condition['years']);
            }
            $users->whereDate('join_date', '<', $now->format('Y-m-d'));
        }

        $limit = 20;
        $date = carbon('01-' . $request->month);
        if ($request->step_2) {
            $users = $users->paginate($limit);
            $done = [];
            foreach ($users as $user) {
                Bonus::where('user_id', $user->id)
                    ->where('month', $date->format('Y-m-d'))
                    ->where('bonus_category_id', $request->category)
                    ->delete();
                $amount_to_use = $request->amount;
                $total_amount = 0;
                if ($request->amount_type == 'fixed') {
                    $total_amount = $amount_to_use;
                } else {
                    $base = $user->payroll_card->basic_salary;
                    if ($request->amount_type == 'percentage') {
                        $total_amount = round($base * $amount_to_use / 100, 2);
                    } else {
                        $total_amount = round($base * $amount_to_use, 2);
                    }
                }
                Bonus::create([
                    'user_id' => $user->id,
                    'month' => $date->format('Y-m-d'),
                    'amount' => $total_amount,
                    'bonus_category_id' => $request->category,
                    'amount_type' => $request->amount_type,
                ]);
                $done[] = $user->id;
            }
            return $done;
        }

        return [
            'total_user_pages' => ceil($users->count() / $limit),
        ];
    }
}
