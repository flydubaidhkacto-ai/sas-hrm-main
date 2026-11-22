<?php

namespace App\Helpers;

use Carbon\Carbon;

class AttendanceReportMailHelper
{
    /**
     * Build subject
     */
    public static function subject(
        string $reportType,
        Carbon $from,
        Carbon $to,
        array $types = [],
        $employeeId = 'all',
        $shiftType = 'all'
    ): string
    {

        $typeLabel = empty($types)
            ? 'All'
            : ucwords(strtolower(implode(', ', $types)));

        $dateLabel = $from->isSameDay($to)
            ? $from->format('d M Y')
            : $from->format('d M Y') . ' - ' . $to->format('d M Y');

        $employeeLabel = $employeeId? " (Employee: {$employeeId})": '';

        $shiftLabel = $shiftType? " (Shift Type: {$shiftType})": '';

        return ucwords(strtolower($reportType))
            . " Attendance Report — {$typeLabel} -{$shiftLabel} — {$dateLabel}";
    }

    /**
     * Build body
     */
    public static function body(
    string $reportType,
    Carbon $from,
    Carbon $to,
    array $types = [],
    $employeeId = 'all',
    $shiftType = 'all'
    ): string
    {

        $typeLabel = empty($types)
            ? 'All Attendance Types'
            : ucwords(strtolower(implode(', ', $types)));

        $dateLabel = $from->isSameDay($to)
            ? $from->format('d M Y')
            : $from->format('d M Y') . ' to ' . $to->format('d M Y');

        $employeeLine = $employeeId ? "<strong>Employee:</strong> {$employeeId}<br>" : '';
        $shiftLabel = $shiftType ? "<strong>Shift Type:</strong> {$shiftType}<br>" : '';

        return "
            Dear Concern,<br><br>

            Please find the <strong>{$reportType}</strong> attendance report.<br><br>

            <strong>Report Details:</strong><br>
            {$employeeLine}
            <strong>Attendance Type:</strong> {$typeLabel}<br>
            {$shiftLabel}
            <strong>Date Range:</strong> {$dateLabel}<br><br>

            The report files are attached with this email.<br><br>
        ";
    }
}