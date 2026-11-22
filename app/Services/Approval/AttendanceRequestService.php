<?php

namespace App\Services\Approval;

use App\Models\Notification;
use App\Models\SmsNotification;
use App\Models\User;
use Carbon\Carbon;
use App\Jobs\SendAttendanceEmail;
use App\Jobs\SendAttendanceRelatedSMS;

class AttendanceRequestService
{

    protected bool $checkMailPermission;

    public function __construct($modal)
    {
        $this->checkMailPermission = SmsNotification::where('user_id', $modal->user_id)
            ->where('types', 'attendance_request')
            ->exists();
    }


    public function handleRejection($modal, $user = null)
    {
        $user = $user ?? user();
        $rejectedBy = $user->name;

        $punchIn = Carbon::parse($modal->punch_in)->format('d F, Y h:i A');
        $punchOut = Carbon::parse($modal->punch_out)->format('d F, Y h:i A');
        $rejectedDate = now()->format('d F, Y h:i A');

        Notification::create([
            'title' => "Attendance Rejected",
            'message' => "Your attendance request for {$modal->date} has been rejected.<br>
                        Punch In: {$punchIn}, Punch Out: {$punchOut}.<br>
                        Rejected by: {$rejectedBy}, Rejected Date: {$rejectedDate}.",
            'time_of_notify' => now(),
            'user_id' => $modal->user_id,
            'url' => route('attendance-requests'),
            'mobile_url' => "/attendance/requests/" . $modal->id . "/show",
        ]);

        if ($this->checkMailPermission) {
            SendAttendanceEmail::dispatch($modal, 'rejected', $rejectedBy, $rejectedDate);
        }
    }

    public function handleApproval($modal, $user = null)
    {
        $user = $user ?? user();
        $approvedBy = $user->name;

        $punchIn = Carbon::parse($modal->punch_in)->format('d F, Y h:i A');
        $punchOut = Carbon::parse($modal->punch_out)->format('d F, Y h:i A');
        $approvedDate = now()->format('d F, Y h:i A');

        Notification::create([
            'title' => "Attendance Approved",
            'message' => "Your attendance request for {$modal->date} has been approved.<br>
                        Punch In: {$punchIn}, Punch Out: {$punchOut}.<br>
                        Approved by: {$approvedBy}, Approved Date: {$approvedDate}.",
            'time_of_notify' => now(),
            'user_id' => $modal->user_id,
            'url' => route('attendance-requests'),
            'mobile_url' => "/attendance/requests/" . $modal->id . "/show",
        ]);
        if ($this->checkMailPermission) {
            SendAttendanceEmail::dispatch($modal, 'approved', $approvedBy, $approvedDate);
        }
    }
}
