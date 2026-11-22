<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalProcess extends Model
{
    protected $guarded = [];
    protected $casts = [
        'for_users' => 'array',
    ];
    static $types = [
        'leave',
        'loan',
        'fine',
        'attendance_request',
        'asset_request',
        'inventory_item_request',
        'emp_daily_expense',
        'station_leave',
        'payslip',
        'payslip_prepare',
        'payslip_accounts_check',
        'payslip_finance_verify',
        'payslip_md_approve',
    ];


    public function steps()
    {
        return $this->hasMany(ApprovalProcessStep::class, 'approval_process_id');
    }
    function records()
    {
        return $this->hasMany(ApprovalRecord::class, 'approval_process_id');
    }

    /**
     * Check if a user can approve a model of a specific type
     * @param $type string loan|fine|leave|attendance_request|emp_daily_expense|inventory_item_request|asset_request
     * @param $approver User
     * @param $user User
     */
    static function canApprove($type, User $approver, ?User $user = null): bool
    {
        if($user) {
            return ApprovalProcessStep::query()
                ->whereHas('approvalProcess', function($q) use ($type, $user) {
                    $q->where('type', $type)
                    ->where(function ($q) use ($user) {
                        $q->where('for_users', 'like', '%"'.$user->id.'"%')
                        ->orWhereNull('for_users');
                    });
                })
                ->where('approver_id', $approver->id)
                ->exists();
        }
        return ApprovalProcessStep::query()
            ->whereHas('approvalProcess', function($q) use ($type) {
                $q->where('type', $type);
            })
            ->where('approver_id', $approver->id)
            ->exists();
    }
    /**
     * Check if a user can approve a model of a specific type
     * @param $type string loan|fine|leave|attendance_request|emp_daily_expense|inventory_item_request|asset_request
     * @param $approver User
     * @return array[]|null
     */
    static function canApproveEmployees($type, User $approver): ?array
    {
        $approverStep = ApprovalProcessStep::query()
            ->whereHas('approvalProcess', function($q) use ($type) {
                $q->where('type', $type);
            })
            ->where('approver_id', $approver->id)
            ->latest()
            ->get();
        if(!$approverStep->count()) {
            return null;
        }
        $users = [];
        foreach($approverStep as $step) {
            $users = array_merge($users, $step->approvalProcess->for_users ?: []);
            if(!$step->approvalProcess->for_users) {
                $users = User::active_employees()->pluck('id')->toArray();
            }
        }
        $users = array_unique($users);

        return [
            'step' => $approverStep->first()->step_order,
            'employees' => $users ?
                User::whereIn('id', $users)->get()
                : User::active_employees()->get(),
        ];
    }
}
