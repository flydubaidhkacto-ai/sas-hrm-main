<?php

namespace App\Traits;

use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessStep;
use App\Models\ApprovalRecord;
use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

trait ApprovalProcessModal
{
    // static function
    static function can_approve($type, User $user)
    {
        return role_can(self::get_permission_by_type($type) . '.superadmin', $user) || (
            ApprovalProcess::query()
                ->where('type', $type)
                ->whereHas('steps', function ($query) use ($user) {
                    $query->where('approver_id', $user->id);
                })
                ->exists()
            ) || role_can('superadmin', $user) || (
                (role_can('leave', $user) && $type === 'leave')
                || (role_can('station_leave', $user) && $type === 'station_leave')
                || (role_can('attendance-requests', $user) && $type === 'attendance_request')
                || (role_can('loans', $user) && $type === 'loan')
                || (role_can('fines', $user) && $type === 'fine')
                || (role_can('emp-expenses', $user) && $type === 'emp_daily_expense')
                || (role_can('inventory', $user) && $type === 'inventory_item_request')
                || (role_can('asset-request', $user) && $type === 'asset_request')
                || (role_can('payslip', $user) && $type === 'payslip')
            );
    }

    static function get_permission_by_type($type)
    {
        return ([
                'loan' => 'loans',
                'fine' => 'fines',
                'leave' => 'leave',
                'station_leave' => 'station_leave',
                'attendance_request' => 'attendance-requests',
                'emp_daily_expense' => 'emp-expenses',
                'inventory_item_request' => 'inventory',
                'asset_request' => 'asset-request',
                'payslip' => 'payslip',
            ])[$type] ?? 'admin';
    }

    function can_approve_this($step_order = null)
    {
        $steps = $this->getApprovalSteps() ?? [];
        foreach ($steps ?: [] as $order => $step) {
            if ($step_order === null && $step['can_approve']) {
                return $step;
            }
            if ($order === $step_order && $step['can_approve']) {
                return $step;
            }
        }
        return false;
    }

    function getApprovalStepsList()
    {
        $approvalProcesses = ApprovalProcess::where('type', $this->approval_process_type)
            ->get();
        // Log::info('grg2', [$approvalProcesses->toArray()]);
        $approvalProcess = null;

        foreach (
            $approvalProcesses->filter(function ($process) {
                return empty($process->for_users);
            }) as $process
        ) {
            $approvalProcess = $process;
        }
        foreach (
            $approvalProcesses->filter(function ($process) {
                // Log::info('grg3', [ is_array($process->for_users) && (
                //     in_array($this->employee_id, $process->for_users)
                //     || in_array($this->user_id, $process->for_users)
                //     || in_array(strval($this->employee_id), $process->for_users)
                //     || in_array(strval($this->user_id), $process->for_users)
                // )]);
                return is_array($process->for_users) && (
                        in_array($this->employee_id, $process->for_users)
                        || in_array($this->user_id, $process->for_users)
                        || in_array(strval($this->employee_id), $process->for_users)
                        || in_array(strval($this->user_id), $process->for_users)
                    );
            }) as $process
        ) {
            $approvalProcess = $process;
        }
        // Log::info('grg3', [$approvalProcess]);
        if (!$approvalProcess) return null;
        // Log::info('grg4', [$approvalProcess->steps]);
        $steps = $approvalProcess->steps;
        return $steps->sortBy('step_order');
    }

    function getApprovalSteps(?User $user = null)
    {
        if (!$user) $user = user();
        $stepsStatus = [];
        $steps = $this->getApprovalStepsList() ?? [];
        if (!$steps) return null;

        $prev_step_approved = true;
        foreach ($steps as $step) {
            $decided = ApprovalRecord::where('approval_step_id', $step->id)
                ->where($this->approval_process_type . '_id', $this->id)
                ->orderByDesc('id')
                ->first();

            $stepsStatus[$step->step_order] = [
                'step' => $step,
                'status' => $decided ?->status ?: 'pending',
                'approver' => $step->approver,
                'decided' => $decided,
                'can_approve' => $this->status === 'pending'
            && ($prev_step_approved || (role_can('superadmin', $user) && $step->approver ?->id === $user->id))
                    && !$decided && ($step->approver ?->id === $user->id || (
            role_can(self::get_permission_by_type($this->approval_process_type) . '.superadmin', $user)
            )),
            ];
            $prev_step_approved = $stepsStatus[$step->step_order]['status'] === 'approved';
        }
        return $stepsStatus;
    }

    function getApprovalStepsApi($user_id)
    {
        return $this->getApprovalSteps(User::findOrFail($user_id));
    }

    static function related_by($user_id, $type, $stepsStatus = null, $user_column = 'user_id')
    {
        if (User::findOrFail($user_id)->is_super_admin()) return self::query();
        // return self::query()
        //     ->where('current_approver_id', $user_id);
        // check if can approve everyone
        $canApproveEveryone = ApprovalProcess::where('type', $type)
            ->whereHas('steps', function ($query) use ($user_id) {
                $query->where('approver_id', $user_id);
            })
            ->whereNull('for_users')
            ->first();
        if ($canApproveEveryone) {
            $q = self::query();
            $steps = $canApproveEveryone->steps()
                ->orderBy('step_order', 'asc')->get();
        } else {
            $users = [];
            foreach (
                ApprovalProcess::where('type', $type)
                    ->where('for_users', '!=', null)
                    ->whereHas('steps', function ($query) use ($user_id) {
                        $query->where('approver_id', $user_id);
                    })
                    ->get() as $process
            ) {
                $users = array_merge($users, $process->for_users);
                $steps = $process->steps()->orderBy('step_order', 'asc')->get();
            }
            $q = self::query();
            if ($users) {
                if (!in_array($user_id, $users)) $users[] = $user_id;
                $q->whereIn(
                    (($type === 'leave' || $type === 'station_leave') ? 'employee_id' : $user_column),
                    $users
                );
            }
        }
        return $q->when($stepsStatus, fn($q2) => $q2->where('status', $stepsStatus));
    }


    static function pending_for_approval(Collection $collection, $approver = null, $user_column = 'user_id', bool $return_own = true): Collection
    {
        if (!$approver) $approver = user();

        $type = (new self)->approval_process_type;

        $user_column = (($type === 'leave' || $type === 'station_leave') ? 'employee_id' : $user_column);

        $approver_id = $approver->id;

        // ✅ Step 1: Only pending
        $collection = $collection->filter(function ($item) {
            return $item->status === 'pending';
        });

        // ✅ Step 2: Super admin or module admin (see ALL pending items)
        if (
            $approver->is_super_admin()
            || (role_can('leave', $approver) && $type === 'leave')
            || (role_can('station_leave', $approver) && $type === 'station_leave')
            || (role_can('attendance-requests', $approver) && $type === 'attendance_request')
            || (role_can('loans', $approver) && $type === 'loan')
            || (role_can('fines', $approver) && $type === 'fine')
            || (role_can('emp-expenses', $approver) && $type === 'emp_daily_expense')
            || (role_can('inventory', $approver) && $type === 'inventory_item_request')
            || (role_can('asset-request', $approver) && $type === 'asset_request')
            || (role_can('payslip', $approver) && $type === 'payslip')
        ) {
            return $collection;
        }

        // ✅ OLD LOGIC (unchanged)
        $allProcesses = ApprovalProcess::where('type', $type)
            ->whereHas('steps', function ($query) use ($approver_id) {
                $query->where('approver_id', $approver_id);
            })
            ->get();

        $filtered = $collection->filter(function ($item) use ($allProcesses, $type, $user_column, $approver_id, $return_own) {

            if ($return_own && $approver_id == $item->{$user_column}) return true;

            foreach ($allProcesses as $process) {

                $applier_id = $item->{(($type === 'leave' || $type === 'station_leave') ? 'employee_id' : $user_column)};

                if (
                    in_array($applier_id, $process->for_users ?: [])
                    || empty($process->for_users)
                ) {

                    $steps = $process->steps()->orderBy('step_order', 'asc')->get();

                    $prevSteps = [];
                    $currentStep = null;
                    $nextSteps = [];

                    foreach ($steps as $step) {
                        if ($step->approver_id == $approver_id) {
                            $currentStep = $step;
                        } elseif ($currentStep && $currentStep->step_order < $step->step_order) {
                            $nextSteps[] = $step;
                        } else {
                            $prevSteps[] = $step;
                        }
                    }

                    if (!$currentStep) return false;

                    $approvedRecords = $item->approvalRecords;

                    $prevApproved = false;
                    $nexPending = false;

                    if ($prevSteps) {
                        if ($approvedRecords->count() === 0) {
                            $prevApproved = false;
                        } else {
                            $prevApproved = $approvedRecords
                                    ->whereIn('approval_step_id', collect($prevSteps)->pluck('id'))
                                    ->filter(fn($q) => $q->status !== 'approved')
                                    ->count() === 0;
                        }
                    } else {
                        $prevApproved = true;
                    }

                    if ($nextSteps && !$approvedRecords->whereIn('approval_step_id', collect($nextSteps)->pluck('id'))->first()) {
                        $nexPending = true;
                    } else if (!$nextSteps) {
                        $nexPending = true;
                    }

                    $shouldShow = $prevApproved && $nexPending && (
                            $approvedRecords->where('approval_step_id', $currentStep->id)->count() === 0
                        );

                    return $shouldShow;
                }
            }

            return false;
        });

        // 🔥🔥🔥 FINAL FIX
        return $filtered->filter(function ($item) use ($approver_id, $approver, $user_column) {

            // ✅ Safety: skip any loan that is no longer pending
            if ($item->status !== 'pending') {
                return false;
            }

            // ✅ Applicant: always see own pending
            if ($approver_id == $item->{$user_column}) {
                return $item->status === 'pending';
            }

            // ✅ Normal approver: must be current approver AND not already approved this step
            if ((int)$item->current_approver_id === (int)$approver_id) {
                $currentStep = $item->getApprovalStepsList()
                    ->firstWhere('approver_id', $approver_id);

                if ($currentStep) {
                    $alreadyApproved = $item->approvalRecords
                        ->where('approval_step_id', $currentStep->id)
                        ->where('status', 'approved')
                        ->isNotEmpty();

                    if ($alreadyApproved) {
                        return false;
                    }
                }
            }

            return (int)$item->current_approver_id === (int)$approver_id;
        });
    }


    /**
     * Relationship: Get approval requests related to this model.
     */
    public function approvalRecords()
    {
        return $this->hasMany(ApprovalRecord::class, $this->approval_process_type . '_id');
    }

    static function final_approved()
    {
        return self::query()->where('status', 'approved');
    }

    public function approvedAll()
    {
        foreach ($this->getApprovalSteps() ?? [] as $item) {
            $step = ApprovalProcessStep::findOrFail($item['step']->id);
            $approval = ApprovalRecord::create([
                $this->approval_process_type . '_id' => $this->id,
                'approval_step_id' => $step['id'],
                'status' => 'approved',
            ]);
        }
        if (($this->approval_process_type == 'leave') || ($this->approval_process_type == 'station_leave')) {
            $this->update([
                'published_for_view' => true
            ]);
        }
    }
}
