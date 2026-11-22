<?php

namespace App\Helpers;

class AttendanceMailContentHelper
{
    /**
     * Email Subject Generator
     */
    public static function subject(string $event, array $meta = []): string
    {
        $applicant = $meta['applicant_name'] ?? 'N/A';
        $date = $meta['date'] ?? '';
        $type = strtoupper($meta['request_type'] ?? 'ATTENDANCE'); // IN / OUT

        return match($event){

        'apply' =>
                "Attendance {$type} Request ({$date}) from {$applicant} – your Approval Required",

            'forward' =>
                "Attendance {$type} Request ({$date}) Forwarded for your Approval – of {$applicant}",

            'final_pending' =>
                "Attendance {$type} Request ({$date}) Pending Final Approval – of {$applicant}",

            'approved' =>
                "Attendance {$type} Request Approved ({$date}) – of {$applicant}",

            'rejected' =>
                "Attendance {$type} Request Rejected ({$date}) – of {$applicant}",

            'deleted' =>
                "Attendance {$type} Request Cancelled ({$date}) – of {$applicant}",

            'onQuery' =>
            "Query Raised on Attendance {$type} Request ({$date}) – of {$applicant}",

            'onQueryAnswer' =>
            "Query Answer Submitted for Attendance {$type} Request ({$date}) – of {$applicant}",

            default =>
                "Attendance {$type} Request Update ({$date}) – of {$applicant}",
        };
    }

    /**
     * Email Body Generator (HTML)
     */
    public static function body(string $event, array $meta): string
    {
        // Receiver
        $receiver = $meta['receiver_name'] ?? 'User';

        // Employee Info
        $applicant = $meta['applicant_name'] ?? 'N/A';
        $employeeId = $meta['employee_id'] ?? '-';

        // Attendance Info
        $date = $meta['date'] ?? '-';
        $requestType = strtoupper($meta['request_type'] ?? '-'); // IN / OUT
        $punchIn = $meta['punch_in'] ?? '-';
        $punchOut = $meta['punch_out'] ?? '-';
        $notes = $meta['notes'] ?? '-';
        $query = $meta['query'] ?? '-';
        $queryAnswer = $meta['queryAnswer'] ?? '-';

        // Action Info
        $actor = $meta['actor_name'] ?? '-';
        $actionTime = $meta['action_time'] ?? '-';

        // Status
        $status = ucfirst($meta['status'] ?? '-');

        // Common Attendance Details
        $attendanceDetails = "
            <strong>Applicant:</strong> {$applicant} (ID: {$employeeId})<br>
            <strong>Date:</strong> {$date}<br>
            <strong>Request Type:</strong> {$requestType}<br>
            <strong>Punch In:</strong> {$punchIn}<br>
            <strong>Punch Out:</strong> {$punchOut}<br>
            <strong>Reason:</strong> {$notes}<br>
        ";

        return match($event){

        'apply' => "
                Dear <strong>{$receiver}</strong>,<br><br>
                A new attendance <strong>{$requestType}</strong> request has been submitted by <strong>{$applicant}</strong>.<br><br>
                {$attendanceDetails}
                <strong>Status:</strong> {$status}
            ",

            'forward' => "
                Dear <strong>{$receiver}</strong>,<br><br>
                The attendance request has been approved by <strong>{$actor}</strong> and forwarded for your approval.<br>
                <strong>Forwarded At:</strong> {$actionTime}<br><br>
                {$attendanceDetails}
                <strong>Status:</strong> {$status}
            ",

            'final_pending' => "
                Dear <strong>{$receiver}</strong>,<br><br>
                The attendance request requires your <strong>final approval</strong>.<br>
                <strong>Last Approved By:</strong> {$actor}<br>
                <strong>Approved At:</strong> {$actionTime}<br><br>
                {$attendanceDetails}
                <strong>Status:</strong> {$status}
            ",

            'approved' => "
                Dear <strong>{$receiver}</strong>,<br><br>
                The attendance request of <strong>{$applicant}</strong> has been <strong>approved</strong>.<br>
                <strong>Approved By:</strong> {$actor}<br>
                <strong>Approved At:</strong> {$actionTime}<br><br>
                {$attendanceDetails}
            ",

            'rejected' => "
                Dear <strong>{$receiver}</strong>,<br><br>
                The attendance request of <strong>{$applicant}</strong> has been <strong>rejected</strong>.<br>
                <strong>Rejected By:</strong> {$actor}<br>
                <strong>Rejected At:</strong> {$actionTime}<br><br>
                {$attendanceDetails}
            ",

            'deleted' => "
                Dear <strong>{$receiver}</strong>,<br><br>
                The attendance request has been <strong>cancelled</strong> by <strong>{$actor}</strong>.<br>
                <strong>Cancelled At:</strong> {$actionTime}<br><br>
                {$attendanceDetails}
            ",

                'onQuery' => "
                    Dear <strong>{$receiver}</strong>,<br><br>
                
                    A clarification has been requested regarding the following attendance request.<br><br>
                
                    <strong>Query Details:</strong><br>
                    <em>{$query}</em><br><br>
                
                    <strong>Requested By:</strong> {$actor}<br>
                    <strong>Query Raised At:</strong> {$actionTime}<br><br>
                
                    {$attendanceDetails}
                ",

                'onQueryAnswer' => "
                    Dear <strong>{$receiver}</strong>,<br><br>
                
                    A response has been submitted for the previously raised query on the following attendance request.<br><br>
                
                    <strong>Query:</strong><br>
                    <em>{$query}</em><br><br>
                
                    <strong>Query Answer:</strong><br>
                    <em>{$queryAnswer}</em><br><br>
                
                    <strong>Responded By:</strong> {$actor}<br>
                    <strong>Responded At:</strong> {$actionTime}<br><br>
                
                    {$attendanceDetails}
                ",

            default => "
                Dear <strong>{$receiver}</strong>,<br><br>
                {$attendanceDetails}
            ",
        };
    }
}
