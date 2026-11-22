<?php

namespace App\Reports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceReportExcel implements FromCollection, WithHeadings
{
    public function __construct(private $data) {}

    public function collection()
    {
        return collect($this->data)->map(function ($row, $i) {

            $employee = $row['employee'] ?? null;
            $attendance = $row['attendance'] ?? null;

            $shiftStart = $row['shift_start'] ?? '-';
            $shiftEnd = $row['shift_end'] ?? '-';
            $checkIn = $row['check_in'] ?? '-';
            $checkOut = $row['check_out'] ?? '-';

            // leave display handle (PDF style)
            $statusLabel = ucfirst($row['status']['label'] ?? '-');

            if (!empty($row['status']['leave_display'])) {
                $statusLabel .= ' (' . $row['status']['leave_display'] . ')';
            }

            return [
                'SL' => $i + 1,
                'Emp ID' => $employee->id ?? '-',
                'Employee Name' => $employee->name ?? '-',
                'Join Date' => $employee->join_date ?? '-',
                'Super Department' => $employee->department->super_department->name ?? '-',
                'Department' => $employee->department->name ?? '-',
                'Designation' => $employee->designation->name ?? '-',
                'Employee Type' => $employee->employee_type ?? '-',
                'Date' => $row['date'] ?? '-',
                'Shift' => $employee->shift->name ?? '-',
                'Shift Start' => $shiftStart,
                'Shift End' => $shiftEnd,
                'Punched In' => $checkIn,
                'Punched Out' => $checkOut,
                'Attendance Status' => $statusLabel,
                'Time Missed' => $row['status']['late_time'] ?? '-',
                'Request Type' => ucfirst($row['request_type'] ?? '-'),
                'Request Status' => ucfirst($row['request_status'] ?? '-'),
                'Request Note' => ucfirst($row['request_note'] ?? '-'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'SL',
            'Emp ID',
            'Employee Name',
            'Join Date',
            'Super Department',
            'Department',
            'Designation',
            'Employee Type',
            'Date',
            'Shift',
            'Shift Start',
            'Shift End',
            'Punched In',
            'Punched Out',
            'Attendance Status',
            'Time Missed',
            'Request Type',
            'Request Status',
            'Request Note',
        ];
    }
}
