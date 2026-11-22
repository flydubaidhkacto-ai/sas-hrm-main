<?php

namespace App\Actions;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\AutoAbsentFine;
use App\Models\AutoLateFine;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\User;
use Carbon\Carbon;

class GenerateTotalDutyOfEmployee
{
    /**
     * @param User $employee
     * @param Carbon $from
     * @param Carbon $to
     * @param float $dailySalary
     * @return array
     */
    public static function execute(
        User $employee,
        Carbon $from,
        Carbon $to,
        float $dailySalary = 0
    ) {

        if ($employee->join_date) {
            $_joinDate = Carbon::parse($employee->join_date);

            if ($from->lessThan($_joinDate)) {
                $from = $_joinDate;
            }
        }

        $list = [
            'data' => [],

            'total_worked_days' => 0,
            'total_worked_seconds' => 0,
            'total_overtime_seconds' => 0,

            // late
            'total_late_days' => 0,
            'approved_late_days' => 0,
            'total_late_seconds' => 0,
            'total_late_fine' => 0,

            // absent
            'total_absent_days' => 0,
            'total_absent_fine' => 0,

            // leave
            'total_leave_days' => 0,

            // lwp
            'total_lwp_days' => 0,
            'total_lwp_fine' => 0,

            // summary
            'total_working_days' => 0,
            'total_holidays' => 0,
            'total_weekends' => 0,

            'user' => $employee,
            'from' => $from->format('d M, Y'),
            'to' => $to->format('d M, Y'),

            'all_shift_names' => [],
        ];

        /**
         * APPROVED ATTENDANCE REQUESTS
         */
        $attendanceRequests = AttendanceRequest::query()
            ->where('user_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('date', [
                $from->format('Y-m-d'),
                $to->format('Y-m-d')
            ])
            ->get()
            ->keyBy(function ($item) {
                return carbon($item->date)->format('Y-m-d');
            });

        /**
         * ATTENDANCE
         */
        $attendances = Attendance::with([
            'employee.designation',
            'employee.department.super_department',
            'shift'
        ])
            ->where('employee_id', $employee->id)
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->get();

        foreach ($attendances as $attendance) {

            $key = strtotime(carbon($attendance->date)->format('Y-m-d'));

            $list['data'][$key]['attendance'] = $attendance;

            if (
                $attendance->shift &&
                !in_array($attendance->shift?->name, $list['all_shift_names'])
            ) {
                $list['all_shift_names'][] = $attendance->shift?->name;
            }
        }

        /**
         * DATE RANGE
         */
        $dates = generateDateRange($from, $to);

        /**
         * LEAVES
         */
        foreach ($dates as $date) {

            $leave = LeaveApplication::final_approved()
                ->where('employee_id', $employee->id)
                ->where('is_hourly', false)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->with('leave_type')
                ->first();

            if ($leave) {

                $key = strtotime($date);

                $list['data'][$key]['leave'] = $leave;
            }
        }

        /**
         * WEEKEND / HOLIDAY / ABSENT
         */
        $shift = $employee->shift;

        foreach ($dates as $date) {

            $key = strtotime($date);

            $carbonDate = carbon($date);

            $hasAttendance = isset($list['data'][$key]['attendance']);
            $hasLeave = isset($list['data'][$key]['leave']);

            /**
             * WEEKEND
             */
            if (
                $shift &&
                $shift->{strtolower($carbonDate->format('l'))} === false
            ) {
                $list['data'][$key]['weekend'] = true;
            }

            /**
             * HOLIDAY
             */
            $holiday = Holiday::whereDate('date', $date)->first();

            if ($holiday) {
                $list['data'][$key]['holiday'] = $holiday->name;
            }

            /**
             * ABSENT
             */
            if (
                !$hasAttendance &&
                !$hasLeave &&
                !isset($list['data'][$key]['weekend']) &&
                !isset($list['data'][$key]['holiday'])
            ) {
                $list['data'][$key]['absent'] = true;
            }
        }

        ksort($list['data']);

        /**
         * MAIN LOOP
         */
        foreach ($list['data'] as $time => $item) {

            $dateKey = date('Y-m-d', $time);

            /**
             * HOLIDAY
             */
            if ($item['holiday'] ?? false) {
                $list['total_holidays']++;
            }

            /**
             * WEEKEND
             */
            if ($item['weekend'] ?? false) {
                $list['total_weekends']++;
            }

            /**
             * ATTENDANCE
             */
            if ($attendance = ($item['attendance'] ?? null)) {

                $list['total_worked_days']++;
                $list['total_working_days']++;

                if ($attendance->working_time) {
                    $list['total_worked_seconds'] += time_to_seconds(
                        $attendance->working_time
                    );
                }

                if ($attendance->overtime_time) {
                    $list['total_overtime_seconds'] += time_to_seconds(
                        $attendance->overtime_time
                    );
                }

                /**
                 * LATE
                 */
                if (
                    $attendance->got_late_check_in &&
                    $attendance->shift &&
                    $attendance->worked_from
                ) {

                    $shiftStart = carbon(
                        $attendance->date . ' ' . $attendance->shift->start
                    );

                    $workedFrom = carbon($attendance->worked_from);

                    $lateSeconds = $shiftStart->diffInSeconds(
                        $workedFrom,
                        false
                    );

                    if ($lateSeconds > 0) {

                        $list['total_late_seconds'] += $lateSeconds;

                        /**
                         * APPROVED LATE
                         */
                        if (isset($attendanceRequests[$dateKey])) {

                            $list['approved_late_days']++;

                        } else {

                            $list['total_late_days']++;
                        }
                    }
                }
            }

            /**
             * LEAVE
             */
            elseif ($leave = ($item['leave'] ?? null)) {

                $list['total_leave_days']++;
                $list['total_working_days']++;

                /**
                 * LWP
                 */
                if (
                    $leave->leave_type &&
                    (
                        strtoupper($leave->leave_type->name) === 'LWP' ||
                        !$leave->leave_type->is_paid
                    )
                ) {

                    $list['total_lwp_days']++;
                }
            }

            /**
             * ABSENT
             */
            elseif ($item['absent'] ?? false) {

                $list['total_absent_days']++;
                $list['total_working_days']++;
            }
        }

        /**
         * =========================
         * ABSENT FINE
         * =========================
         */
        $absentRules = AutoAbsentFine::query()
            ->where('fine_status', 'approved')
            ->orderBy('absent_days_amount')
            ->get();

        foreach ($absentRules as $rule) {

            $requiredDays = (int) ($rule->absent_days_amount ?? 0);

            if ($requiredDays <= 0) {
                continue;
            }

            $totalDays = $list['total_absent_days'] + $list['total_lwp_days'];

            if ($totalDays >= $requiredDays) {

                if ($rule->fine_amount_type === 'days') {

                    $list['total_absent_fine'] += (
                        $dailySalary * (float) $rule->fine_amount
                    );

                } else {

                    $list['total_absent_fine'] += (float) $rule->fine_amount;
                }
            }
        }

        /**
         * =========================
         * LWP FINE
         * =========================
         */
        $list['total_lwp_fine'] = (
            $list['total_lwp_days'] * $dailySalary
        );

        /**
         * =========================
         * LATE FINE
         * =========================
         */
        $lateRules = AutoLateFine::query()
            ->where('fine_status', 'approved')
            ->orderBy('late_days_amount')
            ->get();

        foreach ($lateRules as $rule) {

            $requiredDays = (int) ($rule->late_days_amount ?? 0);

            if ($requiredDays <= 0) {
                continue;
            }

            if ($list['total_late_days'] >= $requiredDays) {

                if ($rule->fine_amount_type === 'days') {

                    $list['total_late_fine'] += (
                        $dailySalary * (float) $rule->fine_amount
                    );

                } else {

                    $list['total_late_fine'] += (
                    (float) $rule->fine_amount
                    );
                }
            }
        }

        if ($list['total_late_seconds'] < 0) {
            $list['total_late_seconds'] = 0;
        }

        return $list;
    }
}