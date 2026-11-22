<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\GenerateAttendanceReportJob;
use Carbon\Carbon;

class GenerateDailyAttendanceReportCommand extends Command
{
    protected $signature = 'report:attendance-daily';
    protected $description = 'Generate daily attendance report';

    public function handle(): int
    {
        //$from = Carbon::today()->startOfDay();
        //$to = now();

        // Current time
        $to = now();

        // Cumulative weekly: Sunday of current week to current execution date
        $from = Carbon::now()->startOfWeek(Carbon::SUNDAY)->startOfDay();

        $attendanceTypes = ['all']; // ['late', 'absent', 'ontime', 'all'];
        $employee = 'all'; //  all or id
        $reportType = "daily";
        $shiftType = 'all'; //all, roster, regular
        $superDepartment = 'all'; // or specific id
        $reportBladeName = "reports.department-wise-attendance-reports-pdf";

        $payload = [
            'from' => $from,
            'to' => $to,
            'reportType' => $reportType,
            'employee' => $employee,
            'attendanceTypes' => $attendanceTypes,
            'shiftType' => $shiftType,
            'superDepartmentId' => $superDepartment,
            'reportBladeName' => $reportBladeName,
        ];

        GenerateAttendanceReportJob::dispatch($payload);

        $this->info('Daily attendance report job dispatched.');

        return self::SUCCESS;
    }
}
