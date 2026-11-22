<?php

namespace App\Services\Approval;

use App\Contracts\ApprovalModuleService;
use App\Services\Notification\NotificationService;
use App\Helpers\{
    StatusHelper,
    LeaveMailContentHelper,
    AttendanceMailContentHelper,
    UserTitleHelper,
    DurationHelper
};
use Carbon\Carbon;

class CommonService implements ApprovalModuleService
{
    /* ============================================================
     | APPLY
     ============================================================ */
    public function onApply($model, $applicant, array $context = []): void
    {
        $step = $model->getApprovalStepsList()->sortBy('step_order')->first();

        $model->update([
            'current_approver_id' => $step->approver_id,
            'status' => 'pending',
        ]);

        $context = $this->baseContext(
            model: $model,
            receiver: $step->approver,
            cc: [$model->employee->email],
            status: $model->status,
            context: $context
        );

        $this->send('apply', $model, $applicant, $context);
    }

    /* ============================================================
     | APPROVE
     ============================================================ */
    public function onApprove($model, $approver, array $context = []): void
    {
        if ($context['is_final']) {
            $context = $this->finalEmployeeContext($model, $approver, $context);
            $this->send('approved', $model, $approver, $context);
            return;
        }

        $context = $this->baseContext(
            model: $model,
            receiver: $context['next_approver'],
            cc: [$model->employee->email],
            status: $model->status,
            context: $context
        );

        $this->send('forward', $model, $approver, $context);
    }

    /* ============================================================
     | REJECT
     ============================================================ */
    public function onReject($model, $approver, array $context = []): void
    {
        $context = $this->finalEmployeeContext($model, $approver, $context);
        $this->send('rejected', $model, $approver, $context);
    }

    /* ============================================================
     | DELETE
     ============================================================ */
    public function onDelete($model, $approver, array $context = []): void
    {
        $context['status'] = 'deleted';
        $context = $this->finalEmployeeContext($model, $approver, $context);
        $this->send('deleted', $model, $approver, $context);
    }

    /* ============================================================
     | DATE UPDATED
     ============================================================ */
    public function onDateUpdated($model, $admin, array $context = []): void
    {
        $context = $this->finalEmployeeContext($model, $admin, $context);
        $this->send('date_updated', $model, $admin, $context);
    }

    /* ============================================================
     | SEND (CORE)
     ============================================================ */
    protected function send(string $event, $model, $actor, array $context): void
    {
        $mailStatus = StatusHelper::mail($context['status']);

        $appliedAt = $this->formatDate($model->created_at);
        $actionTime = now()->format('d F, Y h:i A');

        $applicantName = $this->fullName($model->employee);
        $actorName = $this->fullName($actor);

        [$subject, $body, $meta] = match ($context['module']) {
        'leave', 'station_leave' =>
                $this->buildLeaveMail($event, $model, $context, $mailStatus, $applicantName, $actorName, $appliedAt, $actionTime),

            'attendance_request' =>
                $this->buildAttendanceMail($event, $model, $context, $mailStatus, $applicantName, $actorName, $appliedAt, $actionTime),

            default => [null, null, null]
        };

        app(NotificationService::class)->notify([
            'notification' => [
                'title' => $subject,
                'message' => $body,
                'user_id' => $context['receiver']['id'],
                'url' => $context['url'],
            ],
            'mail' => [
                'from_email' => $context['sender']['from_mail'],
                'to_email' => $context['receiver']['to'],
                'cc_email' => $context['receiver']['cc'],
                'subject' => $subject,
                'body' => $body,
                'template' => 'email.system',
                'url' => $context['url'],
                'sender_id' => $actor->id,
                'receiver_id' => $context['receiver']['id'],
                'module' => $context['module'],
                'module_id' => $model->id,
                'status' => $mailStatus,
                'meta' => $meta,
            ],
        ]);
    }

    /* ============================================================
     | MAIL BUILDERS
     ============================================================ */
    protected function buildAttendanceMail($event, $model, $context, $mailStatus, $applicant, $actor, $appliedAt, $actionTime)
    {
        $meta = array_merge($mailStatus, [
            'applicant_name' => $applicant,
            'receiver_name' => $context['receiver']['name'],
            'date' => $model->date,
            'request_type' => $this->attendanceType($model),
            'punch_in' => $this->formatDate($model->punch_in),
            'punch_out' => $this->formatDate($model->punch_out),
            'notes' => $model->notes,
            'applied_at' => $appliedAt,
            'actor_name' => $actor,
            'action_time' => $actionTime,
            'status' => $context['status'],
        ]);

        return [
            AttendanceMailContentHelper::subject($event, $meta),
            AttendanceMailContentHelper::body($event, $meta),
            $meta
        ];
    }

    protected function buildLeaveMail($event, $model, $context, $mailStatus, $applicant, $actor, $appliedAt, $actionTime)
    {
        $duration = $model->is_hourly
            ? DurationHelper::calculate(true, $model->start_date, $model->end_date, $model->start_time, $model->end_time)['label']
            : $model->duration_count . ' Day' . ($model->duration_count > 1 ? 's' : '');

        $meta = array_merge($mailStatus, [
            'applicant_name' => $applicant,
            'receiver_name' => $context['receiver']['name'],
            'leave_type' => $model->type ?? 'Station Leave',
            'leave_reason' => $model->title ?? $model->reason,
            'from_date' => $this->formatDate($model->start_date),
            'to_date' => $this->formatDate($model->end_date),
            'total_leave' => $duration,
            'applied_at' => $appliedAt,
            'actor_name' => $actor,
            'action_time' => $actionTime,
            'status' => $context['status'],
        ]);

        return [
            LeaveMailContentHelper::subject($event, $meta),
            LeaveMailContentHelper::body($event, $meta),
            $meta
        ];
    }

    /* ============================================================
     | HELPERS
     ============================================================ */
    protected function attendanceType($model): string
    {
        return match (true) {
        $model->punch_in && $model->punch_out => 'IN-OUT',
            $model->punch_in => 'IN',
            $model->punch_out => 'OUT',
            default => '-',
        };
    }

    protected function formatDate($date): ?string
    {
        return $date ? Carbon::parse($date)->format('d F, Y h:i A') : null;
    }

    protected function fullName($user): string
    {
        return UserTitleHelper::fullName($user->name, $user->gender, $user->marital_status);
    }

    protected function baseContext($model, $receiver, array $cc, string $status, array $context): array
    {
        return array_merge($context, [
            'status' => $status,
            'receiver' => [
                'id' => $receiver->id,
                'name' => $this->fullName($receiver),
                'to' => [$receiver->email],
                'cc' => $cc,
            ],
            'sender' => [
                'from_mail' => config('mail.from.address')
            ],
            'url' => $this->resolveUrl($context['module'], $model),
        ]);
    }

    protected function finalEmployeeContext($model, $actor, array $context): array
    {
        $to = [$model->employee->email];
        $cc = collect([
            config('hr.emails'),
            $this->getDownlineApproverEmails($model, $actor),
        ])->flatten()->filter()->unique()->diff($to)->values()->toArray();

        return $this->baseContext($model, $model->employee, $cc, $model->status, $context);
    }

    protected function resolveUrl(string $module, $model): string
    {
        return match ($module) {
        'leave' => route('leaves.show', $model->id),
            'station_leave' => route('station.leave.show', $model->id),
            'attendance_request' => route('attendance-requests'),
            'loan' => route('loans.show', $model->id),
            'fine' => route('fine.show', $model->id),
            'asset_request' => route('asset_request.show', $model->id),
            'inventory_item_request' => route('inventory_item_request.show', $model->id),
            'emp_daily_expense' => route('emp-expenses.show', $model->id),
            default => '#',
        };
    }

    protected function getDownlineApproverEmails($model, $currentApprover): array
    {
        $steps = $model->getApprovalStepsList();
        $current = $steps->firstWhere('approver_id', $currentApprover->id);

        return $current
            ? $steps->where('step_order', '<', $current->step_order)
                ->pluck('approver.email')->filter()->unique()->values()->toArray()
            : [];
    }
}
