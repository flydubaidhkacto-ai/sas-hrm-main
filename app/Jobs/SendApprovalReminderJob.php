<?php

namespace App\Jobs;

use App\Helpers\UserTitleHelper;
use App\Mail\SystemMail;
use App\Models\ApprovalReminder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendApprovalReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public $model,
        public User $approver,
        public string $module,
        public ?Carbon $assignedAt = null,
        public ?string $approvalUrl = null,
    ) {}

    public function handle(): void
    {
        $model = $this->model;
        $approver = $this->approver;
        $assignedAt = $this->assignedAt ?: $model->created_at;

        $employee = $model->employee ?? null;
        $applicantName = $employee
            ? UserTitleHelper::fullName($employee->name, $employee->gender, $employee->marital_status)
            : 'N/A';
        $approverName = UserTitleHelper::fullName($approver->name, $approver->gender, $approver->marital_status);

        $department = $employee?->department?->name ?? 'N/A';
        $designation = $employee?->designation?->name ?? 'N/A';
        $employeeId = $employee?->employee_id ?? $employee?->id ?? 'N/A';

        $moduleLabel = match($this->module) {
            'leave' => 'Leave Application',
            'station_leave' => 'Station Leave Application',
            'loan' => 'Loan Request',
            'fine' => 'Fine',
            'attendance_request' => 'Attendance Adjustment Request',
            'asset_request' => 'Asset Request',
            'inventory_item_request' => 'Inventory Item Request',
            'emp_daily_expense' => 'Daily Expense Claim',
            default => ucwords(str_replace('_', ' ', $this->module)),
        };

        $applicationDate = Carbon::parse($model->created_at)->format('d F, Y h:i A');
        $pendingSince = Carbon::parse($assignedAt)->format('d F, Y h:i A');
        $currentStatus = ucfirst($model->status ?? 'Pending');

        $body = "<p><strong>24-Hour Reminder:</strong> The following approval request has been pending for <strong>more than 24 hours</strong> and requires your immediate attention.</p>";
        $body .= "<table style='width:100%;border-collapse:collapse;margin-top:15px;'>";
        $body .= "<tr><td style='padding:8px;border:1px solid #ddd;background:#f9f9f9;width:35%;'><strong>Employee Name</strong></td><td style='padding:8px;border:1px solid #ddd;'>{$applicantName}</td></tr>";
        $body .= "<tr><td style='padding:8px;border:1px solid #ddd;background:#f9f9f9;'><strong>Employee ID</strong></td><td style='padding:8px;border:1px solid #ddd;'>{$employeeId}</td></tr>";
        $body .= "<tr><td style='padding:8px;border:1px solid #ddd;background:#f9f9f9;'><strong>Department</strong></td><td style='padding:8px;border:1px solid #ddd;'>{$department}</td></tr>";
        $body .= "<tr><td style='padding:8px;border:1px solid #ddd;background:#f9f9f9;'><strong>Designation</strong></td><td style='padding:8px;border:1px solid #ddd;'>{$designation}</td></tr>";
        $body .= "<tr><td style='padding:8px;border:1px solid #ddd;background:#f9f9f9;'><strong>Request Type</strong></td><td style='padding:8px;border:1px solid #ddd;'>{$moduleLabel}</td></tr>";
        $body .= "<tr><td style='padding:8px;border:1px solid #ddd;background:#f9f9f9;'><strong>Application Date</strong></td><td style='padding:8px;border:1px solid #ddd;'>{$applicationDate}</td></tr>";
        $body .= "<tr><td style='padding:8px;border:1px solid #ddd;background:#f9f9f9;'><strong>Current Approver</strong></td><td style='padding:8px;border:1px solid #ddd;'>{$approverName}</td></tr>";
        $body .= "<tr><td style='padding:8px;border:1px solid #ddd;background:#f9f9f9;'><strong>Pending Since</strong></td><td style='padding:8px;border:1px solid #ddd;'>{$pendingSince}</td></tr>";
        $body .= "<tr><td style='padding:8px;border:1px solid #ddd;background:#f9f9f9;'><strong>Current Status</strong></td><td style='padding:8px;border:1px solid #ddd;'>{$currentStatus}</td></tr>";
        $body .= "<tr><td style='padding:8px;border:1px solid #ddd;background:#f9f9f9;'><strong>Reminder Reason</strong></td><td style='padding:8px;border:1px solid #ddd;'>No action taken within 24 hours</td></tr>";
        $body .= "</table>";

        $url = $this->approvalUrl ?: '#';
        $subject = '24-Hour Reminder: Pending Approval Requires Your Action';

        $ccEmails = $this->buildCcEmails();

        app(\App\Services\Notification\NotificationService::class)->notify([
            'notification' => [
                'title' => $subject,
                'message' => $body,
                'user_id' => $approver->id,
                'url' => $url,
            ],
            'mail' => [
                'from_email' => config('mail.from.address'),
                'to_email' => [$approver->email],
                'cc_email' => $ccEmails,
                'subject' => $subject,
                'module' => $this->module,
                'body' => $body,
                'status' => [
                    'subject' => $subject,
                    'color' => '#d68910',
                    'bg' => '#fef9e7',
                    'button' => '#d68910',
                    'button_text' => 'Review Approval',
                ],
                'meta' => [
                    'template' => 'email.system',
                    'url' => $url,
                    'status' => 'Pending Reminder',
                    'color' => '#d68910',
                    'bg' => '#fef9e7',
                    'button' => '#d68910',
                    'button_text' => 'Review Approval',
                    'applicant_name' => $applicantName,
                    'receiver_name' => $approverName,
                    'module_id' => $model->id,
                ],
            ],
        ]);

        ApprovalReminder::create([
            'approvable_type' => get_class($model),
            'approvable_id' => $model->id,
            'approver_id' => $approver->id,
            'reminder_type' => '24_hour',
            'sent_at' => now(),
        ]);
    }

    protected function buildCcEmails(): array
    {
        $hrEmails = (array) config('hr.emails');

        $mdEmails = \App\Models\ApprovalProcessStep::whereHas('approvalProcess', function ($q) {
            $q->where('type', 'payslip_md_approve');
        })
            ->with('approver')
            ->get()
            ->pluck('approver.email')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $cc = collect(array_merge($hrEmails, $mdEmails))
            ->filter()
            ->unique()
            ->diff([$this->approver->email])
            ->values()
            ->toArray();

        return $cc;
    }
}
