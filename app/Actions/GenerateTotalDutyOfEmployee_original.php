<?php

namespace App\Actions;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\RosterItem;
use App\Models\User;
use Carbon\Carbon;


class GenerateTotalDutyOfEmployee
{
    /**
     * Generate total duty of employee
     * @param User $employee
     * @param Carbon $from
     * @param Carbon $to
     * @return array [
     * 'data' => [],
     * 'total_worked_days' => 0,
     * 'total_worked_seconds' => 0,
     * 'total_overtime_seconds' => 0,
     * 'total_late_days' => 0,
     * 'total_late_seconds' => 0,
     * 'total_leave_days' => 0,
     * 'total_absent_days' => 0,
     * 'total_holidays' => 0,
     * 'user' => User,
     * 'from' => Carbon,
     * 'to' => Carbon,
     * ]
     */
    static function execute(User $employee, Carbon $from, Carbon $to)
    {
        if ($employee->join_date) {
            $_joindate = Carbon::parse($employee->join_date);
            if ($from->lessThan($_joindate)) {
                $from = $_joindate;
            }
        }

        $list = [
            'data' => [],
            'total_worked_days' => 0,
            'total_worked_seconds' => 0,
            'total_overtime_seconds' => 0,
            'total_late_days' => 0,
            'total_late_seconds' => 0,
            'total_leave_days' => 0,
            'total_absent_days' => 0,
            'total_holidays' => 0,
            'total_weekends' => 0,
            'user' => $employee,
            'from' => $from->format('d M, Y'),
            'to' => $to->format('d M, Y'),
            'all_shift_names' => [],
        ];
        $att = Attendance::with(['employee.designation', 'employee.department.super_department', 'shift'])
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->where('employee_id', $employee->id)
            ->get();

        foreach ($att as $at) {
            $list['data'][strtotime($at->date)] = [
                'attendance' => $at,
            ];
            if($at->shift && !in_array($at->shift?->name, $list['all_shift_names']))
            {
                $list['all_shift_names'][] = $at->shift?->name;
            }
        }

        $dates = generateDateRange($from, $to);
        $rosterDates = RosterItem::where('employee_id', $employee->id)
                ->whereIn('date', collect($dates)->map(fn($d) => $d . ' 00:00:00'))
                ->with(['roster', 'shift'])
                ->get();
        foreach($rosterDates as $rosterDate)
        {
            $formatted = $rosterDate->roster?->formatted() ?: [];
            foreach($formatted as $i => $rosterItem) {
                if($rosterItem['employees'] && in_array($employee->id, $rosterItem['employees']))
                {
                    $date = explode(' ', $rosterDate->date)[0];
                    // $selectedShift = $rosterItem['shifts'][$date] ?? null;
                    $selectedNoShift = $rosterDate->roster?->no_shift_duty[$i][$date] ?? null;
                    if($selectedNoShift)
                    {
                        $isPaidLeave = $selectedNoShift !== "LWP";
                        $list['data'][strtotime($date)]['leave'] = (object) [
                            'is_paid' => $isPaidLeave,
                            'is_roster' => true,
                            'title' => "Roster: " . $selectedNoShift,
                            'type' => $selectedNoShift,
                            'leave_type' => (object) [
                                'name' => $selectedNoShift,
                                'is_paid' => $isPaidLeave,
                                'bg_color' => 'blue',
                            ]
                        ];
                    }
                }
            }
        }

        $leaves = collect();
        foreach($dates as $date) {
            $tmpLeave = LeaveApplication::final_approved()
                ->where('employee_id', $employee->id)
                ->where('is_hourly', false)
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->get();
            $leaves = $leaves->merge($tmpLeave);
        }
        // dd($leaves);

        foreach ($leaves as $leave) {
            $dates_tmp = [];
            $start_tmp = carbon($leave->start_date);
            $end_tmp = carbon($leave->end_date);
            while ($start_tmp <= $end_tmp) {
                if($start_tmp->isBetween($from, $to)) {
                    $dates_tmp[] = $start_tmp->format('Y-m-d');
                }
                $start_tmp->addDay();
            }
            foreach ($dates_tmp as $date) {
                $list['data'][strtotime($date)] = [
                    'leave' => $leave->load('leave_type'),
                ];
            }
        }

        $shift = $employee->shift;
        foreach ($dates as $date) {
            // if (!isset($list['data'][strtotime($date)])) {
                //- check if it is weekend
                if (
                    $shift && $shift->{strtolower(carbon($date)->format('l'))} === false
                ) {
                    $list['data'][strtotime($date)]['weekend'] = true;
                }
                $holiday = Holiday::query()
                    ->where('date', $date)
                    ->first();
                if ($holiday) {
                    $list['data'][strtotime($date)]['holiday'] = $holiday->name;
                } else if(!isset($list['data'][strtotime($date)])) {
                    $list['data'][strtotime($date)]['absent'] = true;
                }
            // }
        }
        ksort($list['data']);
        foreach ($list['data'] as $time => $item) {
            if ($item['attendance'] ?? false) {
                $list['total_worked_days']++;
                if ($item['attendance']->working_time) {
                    $list['total_worked_seconds'] += time_to_seconds($item['attendance']->working_time);
                }
                if ($item['attendance']->overtime_time) {
                    $list['total_overtime_seconds'] += time_to_seconds($item['attendance']->overtime_time);
                }
                if (
                    $item['attendance']->got_late_check_in
                    && $item['attendance']->shift
                    && $item['attendance']->worked_from
                ) {
                    $shiftStarts = carbon(
                        $item['attendance']->date . " " . $item['attendance']->shift->start
                    );
                    $to = carbon($item['attendance']->worked_from);
                    $list['total_late_seconds'] += $shiftStarts->diffInSeconds($to);
                    $list['total_late_days']++;
                }
            } else
                if ($item['leave'] ?? false) {
                $list['total_leave_days']++;
            } else
                if ($item['absent'] ?? false) {
                $list['total_absent_days']++;
            } else
                if ($item['weekend'] ?? false) {
                $list['total_weekends']++;
            }
            if ($item['holiday'] ?? false) {
                $list['total_holidays']++;
            }
        }
        if ($list['total_late_seconds'] < 0) {
            $list['total_late_seconds'] = 0;
        }
        return $list;
    }
}
