<?php

namespace App\Services\Approval;

use App\Models\Notification;
use App\Models\User;
use App\Jobs\SendFineEmail;
use App\Models\SmsNotification;

class FineService
{


    public function handleRejection($modal, $user = null)
    {
        $user = $user ?? user();
        $rejectedBy = $user->name;
        $rejectedDate = now()->format('d F, Y h:i A');

        Notification::create([
            'title' => "Fine Rejected",
            'message' => "Your fine request of amount {$modal->amount} has been rejected.<br>
                         Reason: {$modal->reason}.<br>
                         Rejected by: {$rejectedBy}, Rejected Date: {$rejectedDate}.",
            'time_of_notify' => now(),
            'user_id' => $modal->user_id,
            // 'url' => route('fines.index'),
            // 'mobile_url' => "/fine/" . $modal->id . "/show",
        ]);
        SendFineEmail::dispatch($modal, 'rejected', $rejectedBy, $rejectedDate);
    }

    public function handleApproval($modal, $user = null)
    {
        $user = $user ?? user();
        $approvedBy = $user->name;
        $approvedDate = now()->format('d F, Y h:i A');

        Notification::create([
            'title' => "Fine Approved",
            'message' => "Your fine request of amount {$modal->amount} has been approved.<br>
                         Reason: {$modal->reason}.<br>
                            Approved by: {$approvedBy}, Approved Date:{$approvedDate}.",
            'time_of_notify' => now(),
            'user_id' => $modal->user_id,
            // 'url' => route('fines.index'),
            // 'mobile_url' => "/fine/" . $modal->id . "/show",
        ]);
        SendFineEmail::dispatch($modal, 'approved', $approvedBy, $approvedDate);
    }
}
