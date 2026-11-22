<?php

namespace App\Services\Approval;

use App\Models\Notification;
use App\Models\SmsNotification;
use App\Models\User;
use Carbon\Carbon;
use App\Jobs\SendLeaveEmail;
use App\Models\Fine;
use Illuminate\Support\Facades\Log;

class StationLeaveService
{

    public function handleRejection($modal, $user = null)
    {
        $user = $user ?? user();
        $rejectedBy = $user->name;
        $rejectedDate = now()->format('d F, Y h:i A');

        Notification::create([
            'title' => "Station Leave Request Rejected",
            'message' => "Your leave request ({$modal->type}) from " .
                Carbon::parse($modal->start_date)->format('d F, Y') .
                " to " . Carbon::parse($modal->end_date)->format('d F, Y') .
                " has been rejected.\n Rejected by: {$rejectedBy}, Rejected Date: {$rejectedDate}.",
            'time_of_notify' => now(),
            'user_id' => $modal->employee_id,
            'url' => route('station.leave.show', $modal->id),
            'mobile_url' => '/station/leave/' . $modal->id . '/show'
        ]);

        SendLeaveEmail::dispatch($modal, 'rejected', $rejectedBy, $rejectedDate);
    }

    public function handleApproval($modal, $user = null)
    {
        $user = $user ?? user();
        $approvedBy = $user->name;
        $approvedDate = now()->format('d F, Y h:i A');

        Notification::create([
            'title' => "Station Leave Request Approved",
            'message' => "Your leave request ({$modal->type}) from " .
                Carbon::parse($modal->start_date)->format('d F, Y') .
                " to " . Carbon::parse($modal->end_date)->format('d F, Y') .
                " has been approved.\n Approved by: {$approvedBy}, Approved Date: {$approvedDate}.",
            'time_of_notify' => now(),
            'user_id' => $modal->employee_id,
            'url' => route('station.leave.show', $modal->id),
            'mobile_url' => '/station/leave/' . $modal->id . '/show'
        ]);

        SendLeaveEmail::dispatch($modal, 'approved', $approvedBy, $approvedDate);
    }
}
