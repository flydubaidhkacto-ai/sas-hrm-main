<?php

namespace App\Http\Controllers;

use App\Jobs\SendRewardSMS;
use App\Models\EmployeeReward;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Exports\EmployeeRewardList;
use App\Models\User;

class RewardController extends Controller
{
    function rewards(Request $request)
    {
        abort_if(!(
            role_can('rewards')
            || role_can('rewards.self.super_department')
            || user()->admin_of_super_departments
        ), 403);
        $data = EmployeeReward::query()
            ->when(!role_can('rewards'), function ($q) {
                if (user()->admin_of_super_departments) {
                    $q->whereHas('employee.department', function ($query) {
                        return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                    })
                    ->orWhere('user_id', user()->id);
                } else
                if (role_can('rewards.self.super_department')) {
                    $q->whereHas('employee.department', function ($query) {
                        return $query->where('super_department_id', user()->department?->super_department_id);
                    });
                } else {
                    $q->where('user_id', user()->id);
                }
            });
        // apply date filter
        $from = null;
        $to = null;
        if ($request->start_date && $request->end_date) {
            $from = carbon($request->start_date);
            $to = carbon($request->end_date);
            $data = $data->whereBetween('date', [
                $from->format('Y-m-d') . ' 00:00',
                $to->format('Y-m-d') . ' 23:59'
            ]);
        }
        // apply search
        // if ($request->department)

        if ($request->employee) {
            $data->where('user_id', $request->employee);
        }

        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return EmployeeRewardList::download_excel($data->get(), 'Employee Reward.xlsx');
            }
            if ($as == 'pdf') {
                return EmployeeRewardList::download_pdf($data->get(), 'Employee Reward.pdf');
            }
            // if ($as == 'docx') {
            //     return EmployeeList::download_docx($data->get(), 'Employees.docx');
            // }
            if ($as == 'print') {
                return EmployeeRewardList::print($data->get());
            }
        }

        $data = $data->paginate(100);
        return view('rewards.index', [
            'data' => $data,
            'from' => $from,
            'to' => $to
        ]);
    }
    function create()
    {
        return view('rewards.create');
    }
    function store(Request $request)
    {
        $emp = User::find($request->employee);
        abort_if(!(
            role_can('rewards')
            || (role_can('rewards.self.super_department') && $emp?->department?->super_department_id == user()->department?->super_department_id)
            || is_admin_of_super_department($emp->department?->super_department_id)
        ), 403);

        $request->validate([
            'employee' => 'required|exists:users,id',
            'name' => 'required|max:255',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:1',
        ]);
        $employee = EmployeeReward::create([
            'user_id' => $request->employee,
            'name' => $request->name,
            'date' => carbon($request->date)->format('Y-m-d') . ' 00:00',
            'amount' => $request->amount,
            'added_by' => user()->id
        ]);
        SendRewardSMS::dispatch($employee);
        Toastr::success('Reward created successfully');
        return redirect()->route('rewards');
    }
    function delete($id)
    {
        $reward = EmployeeReward::findOrFail($id);
        $emp = $reward->employee;
        abort_if(!(
            role_can('rewards')
            || (role_can('rewards.self.super_department') && $emp?->department?->super_department_id == user()->department?->super_department_id)
            || is_admin_of_super_department($emp->department?->super_department_id)
        ), 403);
        
        $reward->delete();
        Toastr::success('Reward deleted successfully');
        return redirect()->back();
    }
}
