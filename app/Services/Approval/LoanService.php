<?php

namespace App\Services\Approval;

use App\Helpers\UserTitleHelper;
use App\Services\Notification\NotificationService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LoanService
{
    public function handleRejection($modal, $user = null)
    {
        $user = $user ?? user();
        $rejectedBy = $user->name;
        $rejectedDate = now()->format('d F, Y h:i A');

        $receiverName = UserTitleHelper::fullName($modal->employee->name, $modal->employee->gender, $modal->employee->marital_status);

        $body = "<p>Your loan request of amount <strong>{$modal->amount}</strong> has been <strong>rejected</strong>.</p>";
        $body .= "<p>Reason: {$modal->reason}</p>";
        $body .= "<p>Rejected by: <strong>{$rejectedBy}</strong>, Rejected Date: {$rejectedDate}</p>";

        try {
            app(NotificationService::class)->notify([
                'notification' => [
                    'title' => 'Loan Rejected',
                    'message' => $body,
                    'user_id' => $modal->user_id,
                    'url' => route('loans'),
                ],
                'mail' => [
                    'from_email' => config('mail.from.address'),
                    'to_email' => [$modal->employee->email],
                    'subject' => 'Loan Request Rejected',
                    'module' => 'loan',
                    'body' => $body,
                    'status' => [
                        'subject' => 'Loan Request Rejected',
                        'color' => '#c0392b',
                        'bg' => '#fdf2f2',
                        'button' => '#c0392b',
                        'button_text' => 'View Loan',
                    ],
                    'meta' => [
                        'template' => 'email.system',
                        'url' => route('loans'),
                        'status' => 'Rejected',
                        'color' => '#c0392b',
                        'bg' => '#fdf2f2',
                        'button' => '#c0392b',
                        'button_text' => 'View Loan',
                        'applicant_name' => $receiverName,
                        'receiver_name' => $receiverName,
                        'module_id' => $modal->id,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Loan rejection notification failed: ' . $e->getMessage());
        }
    }

    public function handleApproval($modal, $user = null)
    {
        $user = $user ?? user();
        $approvedBy = $user->name;
        $approvedDate = now()->format('d F, Y h:i A');

        $receiverName = UserTitleHelper::fullName($modal->employee->name, $modal->employee->gender, $modal->employee->marital_status);

        $body = "<p>Your loan request of amount <strong>{$modal->amount}</strong> has been <strong>approved</strong>.</p>";
        $body .= "<p>Reason: {$modal->reason}</p>";
        $body .= "<p>Approved by: <strong>{$approvedBy}</strong>, Approved Date: {$approvedDate}</p>";

        try {
            app(NotificationService::class)->notify([
                'notification' => [
                    'title' => 'Loan Approved',
                    'message' => $body,
                    'user_id' => $modal->user_id,
                    'url' => route('loans'),
                ],
                'mail' => [
                    'from_email' => config('mail.from.address'),
                    'to_email' => [$modal->employee->email],
                    'subject' => 'Loan Request Approved',
                    'module' => 'loan',
                    'body' => $body,
                    'status' => [
                        'subject' => 'Loan Request Approved',
                        'color' => '#27ae60',
                        'bg' => '#e8f8f5',
                        'button' => '#27ae60',
                        'button_text' => 'View Loan',
                    ],
                    'meta' => [
                        'template' => 'email.system',
                        'url' => route('loans'),
                        'status' => 'Approved',
                        'color' => '#27ae60',
                        'bg' => '#e8f8f5',
                        'button' => '#27ae60',
                        'button_text' => 'View Loan',
                        'applicant_name' => $receiverName,
                        'receiver_name' => $receiverName,
                        'module_id' => $modal->id,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Loan approval notification failed: ' . $e->getMessage());
        }
    }
}
