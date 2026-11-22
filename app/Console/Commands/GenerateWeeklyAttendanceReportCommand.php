<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\GenerateAttendanceReportJob;
use Carbon\Carbon;

class GenerateWeeklyAttendanceReportCommand extends Command
{
    protected $signature = 'report:attendance-weekly';
    protected $description = 'Generate weekly attendance report';

    public function handle(): int
    {
//        $from = Carbon::parse('2026-02-08')->startOfDay();
//        $to   = Carbon::parse('2026-02-14')->endOfDay();

        // last 7 days
        $from = Carbon::now()->subDays(7)->startOfDay();
        //$to = Carbon::now()->endOfDay();
        $to = Carbon::now()->subDays(1)->endOfDay();



        $attendanceTypes = ['all']; //['late','absent','ontime', all]
        $employee = 'all'; // all or id
        $reportType = "weekly";
        $shiftType = 'all'; //all, roster, regular
        $superDepartment = 'all';

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

        $this->info('Weekly attendance report job dispatched.');

        return self::SUCCESS;
    }
}