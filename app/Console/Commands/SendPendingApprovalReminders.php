<?php

namespace App\Console\Commands;

use App\Jobs\SendApprovalReminderJob;
use App\Models\ApprovalProcessStep;
use App\Models\ApprovalRecord;
use App\Models\ApprovalReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendPendingApprovalReminders extends Command
{
    protected $signature = 'approvals:send-reminders';
    protected $description = 'Send 24-hour reminders for pending approval requests';

    protected array $modules = [
        'leave' => \App\Models\LeaveApplication::class,
        'station_leave' => \App\Models\StationLeaveApplication::class,
        'loan' => \App\Models\Loan::class,
        'fine' => \App\Models\Fine::class,
        'attendance_request' => \App\Models\AttendanceRequest::class,
        'asset_request' => \App\Models\AssetRequest::class,
        'inventory_item_request' => \App\Models\InventoryItemRequest::class,
        'emp_daily_expense' => \App\Models\EmpDailyExpense::class,
    ];

    protected array $foreignKeys = [
        'leave' => 'leave_id',
        'station_leave' => 'station_leave_id',
        'loan' => 'loan_id',
        'fine' => 'fine_id',
        'attendance_request' => 'attendance_request_id',
        'asset_request' => 'asset_request_id',
        'inventory_item_request' => 'inventory_item_id',
        'emp_daily_expense' => 'emp_daily_expense_id',
    ];

    public function handle(): int
    {
        $processed = 0;
        $skipped = 0;

        foreach ($this->modules as $moduleType => $modelClass) {
            try {
                $pendingItems = $modelClass::where('status', 'pending')
                    ->whereNotNull('current_approver_id')
                    ->where('current_approver_id', '>', 0)
                    ->get();
            } catch (\Throwable $e) {
                $this->warn("Skipping {$moduleType}: {$e->getMessage()}");
                continue;
            }

            foreach ($pendingItems as $item) {
                try {
                    $result = $this->processItem($item, $moduleType);
                    if ($result === true) {
                        $processed++;
                    } else {
                        $skipped++;
                    }
                } catch (\Throwable $e) {
                    $this->warn("Error processing {$moduleType} #{$item->id}: {$e->getMessage()}");
                    $skipped++;
                }
            }
        }

        $this->info("Approval reminders processed: {$processed}, skipped: {$skipped}");

        return 0;
    }

    protected function processItem($item, string $moduleType): ?bool
    {
        $assignedAt = $this->resolveAssignedAt($item, $moduleType);

        if (!$assignedAt) {
            return false;
        }

        $hoursPending = abs(Carbon::now()->diffInHours($assignedAt));

        if ($hoursPending < 24) {
            return false;
        }

        $alreadySent = ApprovalReminder::where('approvable_type', $modelClass = $item::class)
            ->where('approvable_id', $item->id)
            ->where('approver_id', $item->current_approver_id)
            ->where('reminder_type', '24_hour')
            ->exists();

        if ($alreadySent) {
            return false;
        }

        $approver = \App\Models\User::find($item->current_approver_id);
        if (!$approver || !$approver->email) {
            return false;
        }

        // Skip if current approver has already approved this step
        $currentStep = $item->getApprovalStepsList()
            ->firstWhere('approver_id', $item->current_approver_id);

        if ($currentStep) {
            $alreadyApproved = $item->approvalRecords
                ->where('approval_step_id', $currentStep->id)
                ->where('status', 'approved')
                ->isNotEmpty();

            if ($alreadyApproved) {
                return false;
            }
        }

        $url = $this->resolveApprovalUrl($item, $moduleType);

        SendApprovalReminderJob::dispatch($item, $approver, $moduleType, $assignedAt, $url);

        return true;
    }

    protected function resolveAssignedAt($item, string $moduleType): ?Carbon
    {
        $steps = $item->getApprovalStepsList();
        if (!$steps || $steps->isEmpty()) {
            return null;
        }

        $currentStep = $steps->firstWhere('approver_id', $item->current_approver_id);
        if (!$currentStep) {
            return Carbon::parse($item->created_at);
        }

        if ((int) $currentStep->step_order === 1) {
            return Carbon::parse($item->created_at);
        }

        $previousStepIds = $steps
            ->where('step_order', '<', $currentStep->step_order)
            ->pluck('id');

        if ($previousStepIds->isEmpty()) {
            return Carbon::parse($item->created_at);
        }

        $fk = $this->foreignKeys[$moduleType] ?? null;
        if (!$fk) {
            return Carbon::parse($item->created_at);
        }

        $record = ApprovalRecord::where($fk, $item->id)
            ->whereIn('approval_step_id', $previousStepIds)
            ->where('status', 'approved')
            ->orderByDesc('created_at')
            ->first();

        return $record ? Carbon::parse($record->created_at) : Carbon::parse($item->created_at);
    }

    protected function resolveApprovalUrl($item, string $moduleType): ?string
    {
        try {
            return match($moduleType) {
                'leave' => route('leaves.show', $item->id),
                'station_leave' => route('station.leave.show', $item->id),
                'attendance_request' => route('attendance-requests'),
                'loan' => route('loans.show', $item->id),
                'fine' => route('fine.show', $item->id),
                'asset_request' => route('asset_request.show', $item->id),
                'inventory_item_request' => route('inventory_item_request.show', $item->id),
                'emp_daily_expense' => route('emp-expenses.show', $item->id),
                default => '#',
            };
        } catch (\Throwable $e) {
            return '#';
        }
    }
}

