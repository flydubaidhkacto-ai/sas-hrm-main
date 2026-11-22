<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Actions\ApproveModal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
use App\Services\Approval\InventoryItemRequestService;
use App\Services\Approval\LoanService;
use App\Services\Approval\LeaveService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Classes\ApiResponseClass;
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
        ];

        return isset($models[$module]) ? $models[$module]::findOrFail($module_id) : null;
    }

    public function approved(Request $request)
    {
        $user = $request->auth_user;
        $module = $request->module;
        $module_id = $request->module_id;

        $modal = $this->getModuleModel($module, $module_id);
        // return ($modal);
        $step = ApprovalProcessStep::findOrFail($request->approval_step_id);
        $approve = ApproveModal::execute($modal, $step, $request->auth_user, $request, $module);
        $moduleText = $approve['moduleText'];
        $status = $approve['status'];

        return ApiResponseClass::sendResponse(
            'success',  array (
                'message' => $moduleText . ' has been ' . ($status) . ' '
                . 'and moved to the next step.',
                'from' => carbon($request->start_date ?: $modal->start_date)->startOfDay()->format('Y-m-d'),
                'to' => carbon($request->end_date ?: $modal->end_date)->endOfDay()->format('Y-m-d'),
            )
        );
    }
    public function updateRemarks(Request $request)
    {
        $record = ApprovalRecord::findOrFail($request->record_id);
        $step = ApprovalProcessStep::findOrFail($record->approval_step_id);
        $user = $request->auth_user;

        abort_if(!(role_can('edit-remarks', $user) && (
            ($step->approver_id == $user->id)
            || $user->is_super_admin()
        )), 403, "You are not authorized to update remarks.");
        
        $record->note = $request->note;
        $record->save();
        return ['success' => true];
    }
}
