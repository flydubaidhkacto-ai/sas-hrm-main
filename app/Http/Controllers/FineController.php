<?php

namespace App\Http\Controllers;

use App\Exports\FineReport;
use App\Jobs\InitAutoLateFineEmployees;
use App\Jobs\SendFineSMS;
use App\Models\AutoLateFine;
use App\Models\Fine;
use App\Models\FinePayment;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class FineController extends Controller
{
    function fines(Request $request)
    {
        abort_if(
            !role_can('fines')
            && !\App\Models\ApprovalProcess::canApprove('fine', user())
            && !role_can('fines.self')
            && !user()->admin_of_super_departments
        , 403);

        $from = $request->from ? carbon($request->from) : carbon()->startOfMonth();
        $to = $request->to ? carbon($request->to) : carbon()->endOfMonth();
        $type = $request->type;

        $data = Fine::whereBetween('created_at', [$from, $to]);

        // filter limitations according to access
        $canApproveEmps = \App\Models\ApprovalProcess::canApproveEmployees('loan', user());
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
            $data->where(function($data) {
                $data->whereHas('employee.department', function($query){
                    return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                })
                ->orWhere('user_id', user()->id);
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


        if ($request->export) {
            $as = $request->export;
            if ($as == 'xlsx') {
                return FineReport::download_excel($data->get(), 'Fine Report.xlsx');
            }
            if ($as == 'pdf') {
                return FineReport::download_pdf($data->get(), 'Fine Report.pdf');
            }
            if ($as == 'print') {
                return FineReport::download_print($data->get());
            }
        }

        return view('fines.index', [
            'data' => $data->get(),
            'from' => $from,
            'to' => $to,
            'type' => $type
        ]);
    }
    function create()
    {
        abort_if(!role_can('fines') && !user()->admin_of_super_departments, 403);
        return view('fines.apply');
    }
    function apply(Request $request)
    {
        abort_if(!role_can('fines') && !is_admin_of_super_department(
            User::find($request->employee)->department->super_department_id
        ), 403);
        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:99999999999',
            'reason' => 'nullable|string',
            'cut_off_as' => 'in:once,monthly',
            'cut_off_amount' => 'nullable|required_if:cut_off_as,monthly|numeric|min:1|max:' . $request->amount,
            'employee' => 'required|exists:users,id',
            'date' => 'required|date',
        ]);
        $data['user_id'] = $request->employee;
        $data['added_by_user_id'] = user()->id;

        if ($data['cut_off_as'] == 'once') {
            $data['cut_off_amount'] = null;
        }

        if (user()->is_super_admin()) {
            $data['status'] = 'approved';
        }

        $data['user_id'] = $data['employee'];
        unset($data['employee']);
        $data['created_at'] = $data['date'];
        unset($data['date']);
        $fine = Fine::create($data);

        if (user()->is_super_admin() && role_can('fines')) {
            $fine->approvedAll();
        }

        Toastr::success('Loan Application Submitted Successfully', 'Success');
        $date = carbon($request->date)->format('d-m-Y');
        return redirect()->route('fines.index', ['from' => $date, 'to' => $date]);
    }
    // function decide(Request $request, $id)
    // {
    //     $data = Fine::findOrFail($id);
    //     abort_if(!role_can('fines'), 403);
    //     if ($request->status == 1) {
    //         $data->update([
    //             'approved_at' => now(),
    //         ]);
    //         SendFineSMS::dispatch($data);
    //         Toastr::success('Loan Approved Successfully', 'Success');
    //     } else {
    //         $data->update([
    //             'rejected_at' => now(),
    //         ]);
    //         Toastr::success('Loan Rejected Successfully', 'Success');
    //     }
    //     return redirect()->route('fines.index');
    // }
    function auto_late_fine()
    {
        return view('fines.auto_late_fine', [
            'data' => AutoLateFine::all(),
        ]);
    }
    function auto_late_fine_store(Request $request)
    {
        abort_if(!role_can('fines'), 403);

        $data = $request->validate([
            'fine_status' => 'required|in:approved,pending',
            'late_days_amount' => 'required|numeric|min:1|max:365',
            'late_days' => 'required|in:consecutive_days,any_days_on_month',
            'type' => 'required|in:late,early-out',
            'fine_amount' => 'nullable|numeric|min:0|max:9999999',
            'fine_amount_days' => 'nullable|numeric|min:0|max:9999999',
        ]);
        if (!$data['fine_amount'] && !$data['fine_amount_days']) {
            Toastr::error('Fine amount or fine amount days is required', 'Error');
            return redirect()->back()->withInput();
        }
        if ($data['fine_amount'] && $data['fine_amount_days']) {
            Toastr::error('Fine amount or fine amount days is required, Not Both', 'Error');
            return redirect()->back()->withInput();
        }
        if ($data['fine_amount_days']) {
            $data['fine_amount'] = $data['fine_amount_days'];
            $data['fine_amount_type'] = 'days';
        }
        try {
            unset($data['fine_amount_days']);
        } catch (\Exception $e) {
        }
        AutoLateFine::create($data);
        Toastr::success('Auto Late Fine Added Successfully', 'Success');
        return redirect()->back();
    }
    function auto_late_fine_delete($id)
    {
        abort_if(!role_can('fines'), 403);
        $fine = AutoLateFine::findOrFail($id);
        $fine->delete();
        Toastr::success('Auto Late Fine Deleted Successfully', 'Success');
        return redirect()->back();
    }
    function destroy($id)
    {
        $fine = Fine::findOrFail($id);
        abort_if(!role_can('fines') && !is_admin_of_super_department(
            $fine->employee?->department?->super_department_id
        ), 403);

        FinePayment::where('fine_id', $id)->delete();
        $fine->delete();
        Toastr::success('Fine deleted successfully', 'Success');
        return redirect()->back();
    }
}
