<?php

namespace App\Services\Approval;

use App\Contracts\ApprovalModuleService;
use App\Jobs\SendMailJob;
use App\Models\MailLog;
use Illuminate\Support\Facades\Log;

class CommonService implements ApprovalModuleService
{
    /* =====================================================
     * 🎯 PUBLIC EVENTS
     * ===================================================== */

    public function onApply($model, $applicant, array $context = []): void
    {
        $step = $model->getApprovalStepsList()->sortBy('step_order')->first();

        $model->update([
            'current_approver_id' => $step->approver_id,
            'status' => 'pending',
        ]);

        $context['receiver'] = $this->buildReceiver(
            id: $step->approver_id,
            name: $this->fullName($step->approver),
            to: [$step->approver->email],
            cc: [$this->safeEmail($model->employee->email)]
        );

        $context['sender'] = $this->defaultSender();
        $context['status'] = $model->status;
        $context['url'] = $this->resolveUrl($context['module'], $model->id);

        $this->send('apply', $model, $applicant, $context);
    }

    public function onApprove($model, $approver, array $context = []): void
    {
        $context['url'] = $this->resolveUrl($context['module'], $model->id);

        if ($context['is_final'] ?? false) {

            $toEmails = [$this->safeEmail($model->employee->email)];

            $ccEmails = $this->mergeCcEmails(
                $toEmails,
                config('hr.emails'),
                $this->getDownlineApproverEmails($model, $approver)
            );

            $context['receiver'] = $this->buildReceiver(
                id: $model->employee->id,
                name: $this->fullName($model->employee),
                to: $toEmails,
                cc: $ccEmails
            );

            $context['sender'] = $this->defaultSender();
            $context['status'] = $model->status;

            $this->send('approved', $model, $approver, $context);
            return;
        }

        // forward
        $next = $context['next_approver'];

        $context['receiver'] = $this->buildReceiver(
            id: $next->id,
            name: $this->fullName($next),
            to: [$next->email],
            cc: [$this->safeEmail($model->employee->email)]
        );

        $context['sender'] = $this->defaultSender();
        $context['status'] = $model->status;

        $this->send('forward', $model, $approver, $context);
    }

    public function onReject($model, $approver, array $context = []): void
    {
        $this->notifyApplicant('rejected', $model, $approver, $context);
    }

    public function onDelete($model, $approver, array $context = []): void
    {
        $context['status'] = 'deleted';
        $this->notifyApplicant('deleted', $model, $approver, $context);
    }

    public function onDateUpdated($model, $admin, array $context = []): void
    {
        $this->notifyApplicant('date_updated', $model, $admin, $context);
    }

    /* =====================================================
     * 🎯 🔥 SEND METHOD (FULL WORKING)
     * ===================================================== */

    protected function send(string $event, $model, $actor, array $context): void
    {
        try {

            $receiver = $context['receiver'];
            $sender   = $context['sender'];

            $subject = $this->buildSubject($event, $context);
            $body    = $this->buildBody($event, $context);

            $mailLog = MailLog::create([
                'module'        => $context['module'] ?? null,
                'model_id'      => $model->id ?? null,
                'to_email'      => $receiver['to'],
                'cc_email'      => $receiver['cc'] ?? [],
                'bcc_email'     => [],
                'from_email'    => $sender['from_mail'],
                'subject'       => $subject,
                'body'          => $body,
                'meta'          => [
                    'template'   => 'email.system',
                    'url'        => $context['url'] ?? null,
                    'status'     => $context['status'] ?? null,
                    'from_name'  => $sender['from_name'],
                ],
                'attachments'   => [],
                'status'        => 'pending',
                'attempt'       => 0,
            ]);

            // 🚀 dispatch queue
            SendMailJob::dispatch($mailLog);

        } catch (\Throwable $e) {

            Log::error('Mail send failed', [
                'error' => $e->getMessage(),
                'module' => $context['module'] ?? null,
            ]);
        }
    }

    /* =====================================================
     * 🎯 SUBJECT & BODY BUILDERS
     * ===================================================== */

    protected function buildSubject(string $event, array $context): string
    {
        return match ($event) {
        'apply' => 'New request submitted',
            'approved' => 'Request approved',
            'forward' => 'Request requires approval',
            'rejected' => 'Request rejected',
            'deleted' => 'Request deleted',
            'date_updated' => 'Request date updated',
            default => 'HRM Notification',
        };
    }

    protected function buildBody(string $event, array $context): string
    {
        return match ($event) {
        'apply' => 'A new request has been submitted.',
            'approved' => 'Your request has been approved.',
            'forward' => 'A request is waiting for your approval.',
            'rejected' => 'Your request has been rejected.',
            'deleted' => 'Your request has been deleted.',
            'date_updated' => 'Your request date has been updated.',
            default => 'Please check the system for details.',
        };
    }

    /* =====================================================
     * 🎯 HELPERS
     * ===================================================== */

    protected function notifyApplicant(string $event, $model, $actor, array $context): void
    {
        $toEmails = [$this->safeEmail($model->employee->email)];

        $ccEmails = $this->mergeCcEmails(
            $toEmails,
            config('hr.emails'),
            $this->getDownlineApproverEmails($model, $actor)
        );

        $context['receiver'] = $this->buildReceiver(
            id: $model->employee->id,
            name: $this->fullName($model->employee),
            to: $toEmails,
            cc: $ccEmails
        );

        $context['sender'] = $this->defaultSender();
        $context['status'] = $context['status'] ?? $model->status;
        $context['url'] = $this->resolveUrl($context['module'], $model->id);

        $this->send($event, $model, $actor, $context);
    }

    protected function resolveUrl(string $module, $id): string
    {
        return match ($module) {
        'leave' => route('leaves.show', $id),
            'station_leave' => route('station.leave.show', $id),
            'attendance_request' => route('attendance-requests'),
            default => '#',
        };
    }

    protected function safeEmail(?string $email): string
    {
        if (
            empty($email) ||
            !str_ends_with(strtolower($email), '@sasflydubai.com')
        ) {
            return 'test.mail1@sasflydubai.com';
        }

        return $email;
    }

    protected function mergeCcEmails(array $to, ...$groups): array
    {
        return collect($groups)
            ->flatten()
            ->filter()
            ->unique()
            ->diff($to)
            ->values()
            ->toArray();
    }

    protected function buildReceiver(int $id, string $name, array $to, array $cc): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'to' => $to,
            'cc' => $cc,
        ];
    }

    protected function defaultSender(): array
    {
        return [
            'from_mail' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];
    }

    protected function fullName($user): string
    {
        return $user->name ?? 'User';
    }

    protected function getDownlineApproverEmails($model, $actor): array
    {
        return []; // 👉 keep your existing logic if needed
    }
}