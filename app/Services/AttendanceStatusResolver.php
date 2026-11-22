<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\User;
use Carbon\Carbon;
use App\Helpers\DurationHelper;

class AttendanceStatusResolver
{
    /**
     * Resolve attendance status for an employee on a given date
     */
    public static function resolve(
        User $user,
        Carbon $date,
        ?Attendance $attendance,
        $rosterItem = null,
        ?Carbon $shiftStart = null,
        ?Carbon $shiftEnd = null
    )
    {


        /*
       4️⃣ Regular employee weekend (final fix)
       */
        $isRosterEmployee = ($attendance && (int)$attendance->is_in_roster === 1) || !empty($rosterItem);

        if (!$isRosterEmployee) {

            /*
             1️⃣ Holiday (highest priority)
             */
            $holiday = Holiday::forUser($user)
                ->whereDate('date', $date)
                ->first();
            if ($holiday) {
                return [
                    'status' => 'holiday',
                    'label' => 'Holiday (' . $holiday->name . ')',
                ];
            }


            // Only regular employees reach here
            $shift = $user->shift;
            $dayOfWeek = strtolower($date->format('l')); // monday, tuesday, etc.

            if ($shift && isset($shift->{$dayOfWeek}) && $shift->{$dayOfWeek} === false) {
                return [
                    'status' => 'weekend',
                    'label' => 'Weekend',
                ];
            }
        }




        /*
        2️⃣ Leave override
        */
        $leave = LeaveApplication::where('employee_id', $user->id)
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        // Hourly leave
        if ($leave && $leave->is_hourly == 1) {
            $leaveTime = ($leave->start_time ?? '') . ' - ' . ($leave->end_time ?? '');
            $duration = DurationHelper::calculate(
                $leave->is_hourly,
                $leave->start_date,
                $leave->end_date,
                $leave->start_time,
                $leave->end_time
            );
            $leaveDisplay = $leaveTime . ($duration['label'] ? " ({$duration['label']})" : '');

            if ($attendance && $attendance->got_late_check_in) {
                return [
                    'status' => 'ontime',
                    'label' => 'On Time + Hourly Leave (' . $leave->status . ')',
                    'leave_display' => $leaveDisplay,
                ];
            }

            return [
                'status' => 'ontime',
                'label' => 'On Time + Hourly Leave (' . $leave->status . ')',
                'leave_display' => $leaveDisplay,
            ];
        }

        // Full day leave
        if ($leave && $leave->is_hourly != 1) {
            return [
                'status' => 'leave',
                'label' => $leave->type
                    ? $leave->type . ' (' . $leave->status . ')'
                    : 'Leave',
            ];
        }


        /*
        3️⃣ Roster no_shift_duty
        */
        if ($isRosterEmployee) {
            if ($rosterItem && $rosterItem->roster ?->no_shift_duty) {
                $group = $rosterItem->group;
                $dateKey = $date->format('Y-m-d');

                $code = $rosterItem->roster->no_shift_duty[$group][$dateKey] ?? null;

                if ($code) {
                    if ($code === 'C') {
                        return ['status' => 'coff', 'label' => 'Compensation Off'];
                    }
                    if ($code === 'W') {
                        return ['status' => 'weekend', 'label' => 'Weekend'];
                    }
                    if (in_array($code, ['L', 'S', 'LWP', 'ML'])) {
                        return ['status' => strtolower($code), 'label' => $code];
                    }
                }
            }
        }

        /*
        ⏳ Shift Not Started / Future Shift (FIXED)
        */
                if (!$attendance && $shiftStart) {

                    $now = Carbon::now();

                    // Future date
                    if ($date->gt($now->copy()->startOfDay())) {
                        return [
                            'status' => 'not_started',
                            'label' => 'Shift Not Started',
                        ];
                    }

                    // Today but shift not started yet
                    if ($date->isSameDay($now) && $now->lt($shiftStart)) {
                        return [
                            'status' => 'not_started',
                            'label' => 'Shift Not Started',
                        ];
                    }
                }

        /*
        5️⃣ Absent
        */
        if (!$attendance || !$attendance->check_in) {
            return ['status' => 'absent', 'label' => 'Absent'];
        }

        // Validate that the check-in belongs to the requested date.
        // Cross-day punches should not mark the previous date as present.
        $checkInDate = Carbon::parse($attendance->check_in)->format('Y-m-d');
        if ($checkInDate !== $date->format('Y-m-d')) {
            return ['status' => 'absent', 'label' => 'Absent'];
        }

        /*
        6️⃣ Late
        */
        if ((int)($attendance->got_late_check_in ?? 0) === 1) {
            return ['status' => 'late', 'label' => 'Late'];
        }

        /*
        7️⃣ On Time
        */
        return ['status' => 'ontime', 'label' => 'On Time'];
    }
}
