<?php

namespace App\Http\Controllers;

use App\Actions\ApproveModal;
use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessStep;
use App\Models\ApprovalRecord;
use App\Models\AssetRequest;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\EmpDailyExpense;
use App\Models\InventoryItemRequest;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Notification;
use App\Services\Approval\AssetRequestService;
use App\Services\Approval\FineService;
use App\Services\Approval\AttendanceRequestService;
use App\Services\Approval\EmpDailyExpenseService;
use App\Services\Approval\InventoryItemRequestService;
use App\Services\Approval\LoanService;
use App\Services\Approval\LeaveService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalController extends Controller
{
    static public function getModuleModel($module, $module_id)
    {
        $models = [
            'loan' => \App\Models\Loan::class,
            'leave' => \App\Models\LeaveApplication::class,
            'fine' => \App\Models\Fine::class,
            'attendance_request' => AttendanceRequest::class,
            'asset_request' => AssetRequest::class,
            'inventory_item_request' => InventoryItemRequest::class,
            'emp_daily_expense' => EmpDailyExpense::class,
            'station_leave' => \App\Models\StationLeaveApplication::class,
            'payslip' => \App\Models\Payslip::class,
        ];

        return isset($models[$module]) ? $models[$module]::findOrFail($module_id) : null;
    }

    public function approve(Request $request, $module, $module_id)
    {
        //dd($request->all());

        $modal = $this->getModuleModel($module, $module_id);
        $step = ApprovalProcessStep::find($request->approval_step_id);
        try {

            $approve = ApproveModal::execute($modal, $step, user(), $request, $module);
            $moduleText = $approve['moduleText'];
            $status = $approve['status'];

            Toastr::success(
                $moduleText . ' has been ' . $status . '.'
            );
        } catch (\Exception $th) {

            Toastr::error($th->getMessage());
        }

        if ($request->approval_process_type == 'leave') {

            return redirect()->route('leaves.all');

        } elseif ($request->approval_process_type == 'station_leave') {

            return redirect()->route('station.leave.pending');

        } else {
            return back();
        }


    }

    public function updateRemarks(Request $request)
    {
        $record = ApprovalRecord::findOrFail($request->record_id);
        $step = ApprovalProcessStep::findOrFail($record->approval_step_id);
        abort_if(!(role_can('edit-remarks') && (
                ($step->approver_id == user()->id)
                || user()->is_super_admin()
            )), 403, "You are not authorized to update remarks.");
        $record->note = $request->note;
        $record->save();
        return ['success' => true];
    }
}
