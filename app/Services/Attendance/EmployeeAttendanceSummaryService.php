<?php

namespace App\Services\Attendance;

use App\Actions\GenerateTotalDutyOfEmployee;
use App\Models\User;
use Carbon\Carbon;
use InvalidArgumentException;

class EmployeeAttendanceSummaryService
{
    public function getAttendanceSummary(
        int $employeeId,
        Carbon $startDate,
        Carbon $endDate
    ): array
    {
        if ($startDate->greaterThan($endDate)) {
            throw new InvalidArgumentException(
                'Start date must be less than or equal to end date'
            );
        }

        $employee = User::findOrFail($employeeId);

        $summary = GenerateTotalDutyOfEmployee::execute(
            $employee,
            $startDate,
            $endDate
        );

        return $this->format($summary, $startDate, $endDate);
    }

    private function format(
        array $summary,
        Carbon $startDate,
        Carbon $endDate
    ): array
    {
        $presence = $summary['total_worked_days'];
        $absent = $summary['total_absent_days'];
        $late = $summary['total_late_days'];
        $leave = $summary['total_leave_days'];

        $workingDays = $presence + $absent + $leave;

        return [
            'period' => [
                'from' => $startDate->format('d M Y'),
                'to' => $endDate->format('d M Y'),
            ],

            'days' => compact('presence', 'absent', 'late', 'leave'),

            'percentage' => [
                'presence' => $workingDays > 0
                    ? round(($presence / $workingDays) * 100, 1)
                    : 0,

                'absent' => $workingDays > 0
                    ? round(($absent / $workingDays) * 100, 1)
                    : 0,

                'late' => $presence > 0
                    ? round(($late / $presence) * 100, 1)
                    : 0,
            ],
        ];
    }
}
