<?php

namespace App\Helpers;

class LeaveMailContentHelper
{
    /**
     * Return the email subject based on event type
     */
    public static function subject(string $event, array $meta = []): string
    {
        $applicant = $meta['applicant_name'] ?? 'Employee';
        $leaveType = $meta['leave_type'] ?? 'Leave';
        $totalLeave = $meta['total_leave'] ?? null;

        // Prefix like: "1 Day Annual Leave"
        $leavePrefix = $totalLeave
            ? "{$totalLeave} {$leaveType}"
            : $leaveType;

        return match($event){

        'apply' =>
            "New {$leaveType} ({$totalLeave}) Application from {$applicant} – your Approval Required",

        'forward' =>
            "{$leaveType} ({$totalLeave}) Application Forwarded for your Approval – of {$applicant}",

        'final_pending' =>
            "{$leaveType} ({$totalLeave}) Application Pending Final Approval – of {$applicant}",

        'approved' =>
            "{$leaveType} ({$totalLeave}) Application Approved – of {$applicant}",

        'rejected' =>
            "{$leaveType} ({$totalLeave}) Application Rejected – of {$applicant}",

        'deleted' =>
            "{$leaveType} ({$totalLeave}) Application Cancelled – of {$applicant}",

        'date_updated' =>
            "{$leaveType} ({$totalLeave}) Dates Updated – of ({$applicant})",

         'onQuery' => "Query Raised on {$leaveType} ({$totalLeave}) – of ({$applicant})",

         'onQueryAnswer' => "Query Answer Submitted for {$leaveType} ({$totalLeave}) – of ({$applicant})",

        default =>
            "{$leaveType} ({$totalLeave}) Application Update – of {$applicant}",
    };
}


    /**
     * Return the email body HTML based on event type and meta information
     */
    public static function body(string $event, array $meta): string
    {
        // Recipient name (who the email is sent to)
        $receiver = $meta['receiver_name'] ?? 'User';

        // Applicant / employee info
        $applicant = $meta['applicant_name'] ?? 'N/A';
        $appliedAt = $meta['applied_at'] ?? '-';
        $leaveType = $meta['leave_type'] ?? '-';
        $fromDate = $meta['from_date'] ?? '-';
        $toDate = $meta['to_date'] ?? '-';
        $totalLeave = $meta['total_leave'] ?? '-';
        $leaveReason = $meta['leave_reason'] ?? '-';

        // Last action info (approve/reject/forward)
        $actor = $meta['actor_name'] ?? '-';
        $actorTime = $meta['action_time'] ?? '-';

        // Current status
        $status = $meta['status'] ?? '-';
        $query = $meta['query'] ?? '-';
        $queryAnswer = $meta['queryAnswer'] ?? '-';


        // Base leave details (common to all events)
        $leaveDetails = "
            <strong>Applicant:</strong> {$applicant}<br>
            <strong>Applied At:</strong> {$appliedAt}<br>
            <strong>Leave Type:</strong> {$leaveType}<br>
            <strong>Leave Period:</strong> {$fromDate} to {$toDate}<br>
            <strong>Total Leave:</strong> {$totalLeave}<br>
            <strong>Reason:</strong> {$leaveReason}<br>
        ";

        // Event-specific messages
        return match($event){
        'apply' => "
                Dear <strong>{$receiver}</strong>,<br><br>
                A new leave application has been submitted by <strong>{$applicant}</strong>.<br><br>
                {$leaveDetails}
            ",

            'forward' => "
                Dear <strong>{$receiver}</strong>,<br><br>
                This leave application has been approved by <strong>{$actor}</strong> and forwarded for your further approval.<br>
                <strong>Forwarded At:</strong> {$actorTime}.<br><br>
                {$leaveDetails}
            ",

            'final_pending' => "
                Dear <strong>{$receiver}</strong>,<br><br>
                The leave application has been approved by previous approvers and requires your <strong>final approval</strong>.<br>
                <strong>Last Approved By:</strong> {$actor}<br>
                <strong>Approved At:</strong> {$actorTime}<br><br>
                {$leaveDetails}
                <strong>Status:</strong> {$status}
            ",

            'approved' => "
                Dear <strong>{$receiver}</strong>,<br><br>
                Your leave application has been fully approved by <strong>{$actor}</strong>.<br>
                <strong>Approved At:</strong> {$actorTime}<br><br>
                {$leaveDetails}
            ",

            'rejected' => "
                Dear <strong>{$receiver}</strong>,<br><br>
                Your leave application has been rejected by <strong>{$actor}</strong>.<br>
                <strong>Rejected At:</strong> {$actorTime}<br><br>
                {$leaveDetails}
            ",

            'deleted' => "
                Dear <strong>{$receiver}</strong>,<br><br>
                Your leave application has been deleted by <strong>{$actor}</strong>.<br>
                <strong>Deleted At:</strong> {$actorTime}<br><br>
                {$leaveDetails}
            ",

            'date_updated' => "
                Dear <strong>{$receiver}</strong>,<br><br>
                Your approved leave dates have been updated by <strong>{$actor}</strong>.<br><br>
                
                <strong>Previous Leave Period:</strong><br>
                {$meta['old_start_date']} to {$meta['old_end_date']}<br><br>

                <strong>Updated Leave Period:</strong><br>
                {$fromDate} to {$toDate}<br><br>
                
              <strong>Applicant:</strong> {$applicant}<br>
              <strong>Applied At:</strong> {$appliedAt}<br>
              <strong>Leave Type:</strong> {$leaveType}<br>
              <strong>Total Leave:</strong> {$totalLeave}<br>
              <strong>Reason:</strong> {$leaveReason}<br>",

          'onQuery' => "
                Dear <strong>{$receiver}</strong>,<br><br>
                A clarification has been requested regarding the following leave application.<br><br>
                <strong>Query Details:</strong><br>
                <em>{$query}</em><br><br>
              <strong>Requested By:</strong> {$actor}<br>
              <strong>Query Raised At:</strong> {$actorTime}<br><br>
                {$leaveDetails}
            ",

            'onQueryAnswer' => "
            Dear <strong>{$receiver}</strong>,<br><br>
    
            A response has been submitted regarding the previously raised query on the following leave application.<br><br>
    
            <strong>Query:</strong><br>
            <em>{$query}</em><br><br>
    
            <strong>Query Answer:</strong><br>
            <em>{$queryAnswer}</em><br><br>
    
            <strong>Responded By:</strong> {$actor}<br>
            <strong>Responded At:</strong> {$actorTime}<br><br>
    
            {$leaveDetails}
            ",

            default => "
                Dear <strong>{$receiver}</strong>,<br><br>
                {$leaveDetails}
            ",
        };
    }
}
