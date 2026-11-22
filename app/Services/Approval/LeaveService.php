<?php

namespace App\Services\Approval;

use App\Contracts\ApprovalModuleService;
use App\Services\Notification\NotificationService;
use App\Helpers\{StatusHelper, LeaveMailContentHelper, UserTitleHelper};
use Carbon\Carbon;

class LeaveService implements ApprovalModuleService
{
    public function onApply($leave, $applicant, array $context = []): void
    {
        $step = $leave->getApprovalStepsList()->sortBy('step_order')->first();

        $leave->update([
            'current_approver_id' => $step->approver_id,
            'status' => 'pending',
        ]);

        $receiverName = UserTitleHelper::fullName($step->approver->name, $step->approver->gender, $step->approver->marital_status);

        $context['status'] = $leave->status;
        $context['receiver'] = [
            'id' => $step->approver_id,
            'name' => $receiverName,
            'to' => [$step->approver->email],
            'cc' => [$leave->employee->email],
        ];

        $context['sender'] = [
            'from_mail' => config('mail.from.address')
        ];

        $context['url'] = route('leaves.show', $leave->id);

        $this->send(
            event: 'apply',
            model: $leave,
            actor: $applicant,
            context: $context
        );
    }

    public function onApprove($leave, $approver, array $context = []): void
    {
        if ($context['is_final']) {

            $receiverName = UserTitleHelper::fullName($leave->employee->name, $leave->employee->gender, $leave->employee->marital_status);

            $context['status'] = $leave->status;
            $context['receiver'] = [
                'id' => $leave->employee_id,
                'name' => $receiverName,
                'to' => [$leave->employee->email],
                'cc' => config('hr.emails'),
            ];

            $context['sender'] = [
                'from_mail' => config('mail.from.address')
            ];

            $context['url'] = route('leaves.show', $leave->id);

            $this->send(
                'approved',
                model: $leave,
                actor: $approver,
                context: $context
            );
            return;
        }

        $receiverName = UserTitleHelper::fullName($context['next_approver']->name, $context['next_approver']->gender, $context['next_approver']->marital_status);

        $context['status'] = $leave->status;
        $context['receiver'] = [
            'id' => $context['next_approver']->id,
            'name' => $receiverName,
            'to' => [$context['next_approver']->email],
            'cc' => [$leave->employee->email],
        ];

        $context['sender'] = [
            'from_mail' => config('mail.from.address')
        ];

        $context['url'] = route('leaves.show', $leave->id);

        $this->send(
            'forward',
            model: $leave,
            actor: $approver,
           context: $context
        );
    }

    public function onReject($leave, $approver, array $context = []): void
    {
        $receiverName = UserTitleHelper::fullName($leave->employee->name, $leave->employee->gender, $leave->employee->marital_status);

        $context['status'] = $leave->status;
        $context['receiver'] = [
            'id' => $leave->employee_id,
            'name' => $receiverName,
            'to' => [$leave->employee->email],
            'cc' => config('hr.emails'),
        ];

        $context['sender'] = [
            'from_mail' => config('mail.from.address')
        ];

        $context['url'] = route('leaves.show', $leave->id);

        $this->send(
            'rejected',
            model: $leave,
           actor: $approver,
          context: $context
        );
    }

    public function onForward($leave, $approver, $nextApprover, array $context = []): void
    {
    }

    public function onDelete($leave, $actor): void
    {
        $receiverName = UserTitleHelper::fullName($leave->employee->name, $leave->employee->gender, $leave->employee->marital_status);

        $context['status'] = "deleted";
        $context['receiver'] = [
            'id' => $leave->employee_id,
            'name' => $receiverName,
            'to' => [$leave->employee->email],
            'cc' => config('hr.emails'),
        ];

        $context['sender'] = [
            'from_mail' => config('mail.from.address')
        ];

        $context['url'] = route('leaves.all.approve');

        $this->send(
            'deleted',
            model: $leave,
           actor: $actor,
           context: $context
        );
    }

    public function onDateUpdated($leave, $admin, array $context = []): void
    {
        $receiverName = UserTitleHelper::fullName($leave->employee->name, $leave->employee->gender, $leave->employee->marital_status);

        $context['status'] = $leave->status;
        $context['receiver'] = [
            'id' => $leave->employee_id,
            'name' => $receiverName,
            'to' => [$leave->employee->email],
            'cc' => config('hr.emails'),
        ];

        $context['sender'] = [
            'from_mail' => config('mail.from.address')
        ];

        $context['url'] = route('leaves.show', $leave->id);

        $this->send(
            event: 'date_updated',
        model: $leave,
        actor: $admin,
        context: $context
    );
}

    protected function send(
        string $event,
        $model,
        $actor,
        array $context = []
    ): void
    {

        $status = $context['status'];
        $receiverId = $context['receiver']['id'];
        $receiverName = $context['receiver']['name'];
        $from = $context['sender']['from_mail'];
        $to = $context['receiver']['to'] ?? [];
        $cc = $context['receiver']['cc'] ?? [];
        $url = $context['url'] ?? '#';
        $module = $context['module'] ?? 'leave';

        $mailStatus = StatusHelper::mail($status);

        // Format dates
        $appliedAt = Carbon::parse($model->created_at)->format('d F, Y h:i A');
        $actionTime = now()->format('d F, Y h:i A');

        // Format employee and approver names with titles
        $applicantName = UserTitleHelper::fullName(
            $model->employee->name,
            $model->employee->gender,
            $model->employee->marital_status
        );
        $actorName = UserTitleHelper::fullName(
            $actor->name,
            $actor->gender,
            $actor->marital_status
        );

        $template = 'email.system';


        if ($module === 'leave') {

            $totalLeave = $model->duration_count;
            $totalLeaveText = $totalLeave . ' Hour' . ($totalLeave > 1 ? 's' : '');

            if ($model->is_hourly) {

                $fromDate = Carbon::parse($model->start_date)->format('d F, Y h:i A');
                $toDate = Carbon::parse($model->end_date)->format('d F, Y h:i A');
                $oldStartDate = isset($context['old_dates']['start_date'])
                    ? Carbon::parse($context['old_dates']['start_date'])->format('d F, Y h:i A')
                    : null;

                $oldEndDate = isset($context['old_dates']['end_date'])
                    ? Carbon::parse($context['old_dates']['end_date'])->format('d F, Y h:i A')
                    : null;


            } else {

                $fromDate = Carbon::parse($model->start_date)->format('d F, Y');
                $toDate = Carbon::parse($model->end_date)->format('d F, Y');
                $oldStartDate = isset($context['old_dates']['start_date'])
                    ? Carbon::parse($context['old_dates']['start_date'])->format('d F, Y')
                    : null;

                $oldEndDate = isset($context['old_dates']['end_date'])
                    ? Carbon::parse($context['old_dates']['end_date'])->format('d F, Y')
                    : null;

            }

            // Build full meta array
            $metaData = array_merge($mailStatus, [
                'applicant_name' => $applicantName,
                'receiver_name' => $receiverName,
                'leave_type' => $model->type,
                'leave_reason' => $model->title,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'applied_at' => $appliedAt,
                'actor_name' => $actorName,
                'action_time' => $actionTime,
                'is_hourly' => $model->is_hourly,
                'total_leave' => $totalLeaveText,
                'status' => $status,
                'old_start_date' => $oldStartDate,
                'old_end_date' => $oldEndDate,
            ]);

            $subjectContent = LeaveMailContentHelper::subject($event, $metaData);
            $bodyContent = LeaveMailContentHelper::body($event, $metaData);

        } else if ($module === 'station_leave') {

            $fromDate = Carbon::parse($model->start_date)->format('d F, Y');
            $toDate = Carbon::parse($model->end_date)->format('d F, Y');
            $oldStartDate = isset($context['old_dates']['start_date'])
                ? Carbon::parse($context['old_dates']['start_date'])->format('d F, Y')
                : null;

            $oldEndDate = isset($context['old_dates']['end_date'])
                ? Carbon::parse($context['old_dates']['end_date'])->format('d F, Y')
                : null;

            $totalLeave = $model->duration_count;
            $totalLeaveText = $totalLeave . ' Day' . ($totalLeave > 1 ? 's' : '');

            // Build full meta array
            $metaData = array_merge($mailStatus, [
                'applicant_name' => $applicantName,
                'receiver_name' => $receiverName,
                'leave_type' => 'Station Leave',
                'leave_reason' => $model->reason,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'applied_at' => $appliedAt,
                'actor_name' => $actorName,
                'action_time' => $actionTime,
                'is_hourly' => false,
                'total_leave' => $totalLeaveText,
                'status' => $status,
                'old_start_date' => $oldStartDate,
                'old_end_date' => $oldEndDate,
            ]);

            $subjectContent = LeaveMailContentHelper::subject($event, $metaData);
            $bodyContent = LeaveMailContentHelper::body($event, $metaData);

        } else if ($module === 'attendance_request') {

            dd('attendance_request for test');

        } else {

            dd('others module for test');
        }

        // Send notification & email
        app(NotificationService::class)->notify([
            'notification' => [
                'title' => $subjectContent,
                'message' => $bodyContent,
                'user_id' => $receiverId,
                'url' => $url,
            ],
            'mail' => [
                'from_email' => $from,
                'to_email' => $to,
                'cc_email' => $cc,
                'subject' => $subjectContent,
                'body' => $bodyContent,
                'template' => $template,
                'url' => $url,
                'sender_id' => $actor->id,
                'receiver_id' => $receiverId,
                'module' => $module,
                'module_id' => $model->id,
                'status' => $mailStatus,
                'meta' => $metaData,
            ]
        ]);
    }

}
