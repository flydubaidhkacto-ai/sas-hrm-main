<?php

namespace App\Services\Approval;

use App\Models\Notification;
use App\Models\SmsNotification;
use App\Models\User;
use App\Jobs\SendLoanEmail;
use Illuminate\Support\Facades\Log;

class EmpDailyExpenseService
{
    public function handleRejection($modal, $user = null)
    {
        $user = $user ?? user();
        $rejectedBy = $user->name;
        $rejectedDate = now()->format('d F, Y h:i A');

        Notification::create([
            'title' => "Expense Entry Rejected",
            'message' => "Your expense entry of amount {$modal->amount} has been rejected <br>
Expense Name: {$modal->name}.<br>",
            'time_of_notify' => now(),
            'user_id' => $modal->employee_id,
            'url' => route('emp-expenses.index', [
                'start_date' => $modal->date,
                'end_date' => $modal->date,
                'employee_id' => $modal->employee_id,
                'type' => 'rejected',
                'id' => $modal->id,
            ]),
            'mobile_url' => "/emp-expense/" . $modal->id . "/show",
        ]);
    }

    public function handleApproval($modal, $user = null)
    {
        $user = $user ?? user();
        $approvedBy = $user->name;
        $approvedDate = now()->format('d F, Y h:i A');

        Notification::create([
            'title' => "Expense Entry Approved",
            'message' => "Your expense entry of amount {$modal->amount} has been approved.<br>
Expense Name: {$modal->name}.<br>",
            'time_of_notify' => now(),
            'user_id' => $modal->employee_id,
            'url' => route('emp-expenses.index', [
                'start_date' => $modal->date,
                'end_date' => $modal->date,
                'employee_id' => $modal->employee_id,
                'type' => 'all',
                'id' => $modal->id,
            ]),
            'mobile_url' => "/emp-expense/" . $modal->id . "/show",
        ]);
    }
}
