<?php

namespace App\Actions\GeneratePayslip;

use App\Actions\GenerateDailyWorkingData;
use App\Jobs\InitAutoAbsentFineEmployees;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\RosterItem;
use App\Models\Setting;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait GenerateDates
{
    protected $attendances;
    protected $grouped_paid_holidays_leaves_weekends = [];
    protected $emplyee_worked_days = 0;
    protected $emplyee_raw_worked_days = 0;
    protected $emplyee_paid_leave_days = 0;
    protected $emplyee_unpaid_leave_days = 0;
    protected $available_working_days = 0;
    protected $without_weekend_dates = [];
    function generateDates()
    {
        $this->dates = [];
        $defaultShift = $this->user->shift ?: Shift::first();
        $attendances = Attendance::query()
            ->where('employee_id', $this->user->id)
            ->whereDate('date', '>=', $this->working_from_date)
            ->whereDate('date', '<=', $this->end_date)
            ->get();
        foreach (generateDateRange($this->from_date, $this->end_date) as $date) {
            $att = $attendances->where('date', $date)->first();
            $shift = $att && $att->shift ? $att->shift : $defaultShift;
            $holiday = $this->holidays->where('date', $date)->first();
            $is_weekend = !($shift->{strtolower(carbon($date)->format('l'))});
            $leave = null;

            if($att) {
                if(!$holiday && !$is_weekend) {
                    $this->emplyee_raw_worked_days += 1;
                }
                $this->emplyee_worked_days += 1;
            }

            if(!$is_weekend) {
                $this->without_weekend_dates[] = $date;
            }
            if(!$holiday && !$is_weekend) {
                $leave = LeaveApplication::final_approved()
                    ->where('employee_id', $this->user->id)
                    ->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date)
                    ->where('is_hourly', false)
                    ->with('leave_type')
                    ->first();
                if($leave) {
                    // Leave types that don't affect leave balance (e.g., Work From Home)
                    // should NOT be counted as leave days in totals.
                    if ($leave->leave_type?->affects_leave_balance ?? true) {
                        $this->emplyoe_paid_leave_days += $leave->leave_type?->is_paid ? 1 : 0;
                        $this->emplyoe_unpaid_leave_days += $leave->leave_type?->is_paid ? 0 : 1;
                    }
                }
                $this->available_working_days += 1;
            }else {
                // Log::info('Not available date: ' . $date);
            }

            $today_shift_starts = carbon($date)->setTime(
                carbon($shift->start)->hour,
                carbon($shift->start)->minute,
                carbon($shift->start)->second
            );
            $today_shift_ends = carbon($date)->setTime(
                carbon($shift->end)->hour,
                carbon($shift->end)->minute,
                carbon($shift->end)->second
            );
            if (
                $today_shift_starts->isAfter($today_shift_ends)
            ) $today_shift_ends->addDay();

            $max_seconds = $shift->duration;

            // check roster
            $rosterDates = RosterItem::where('employee_id', $this->user->id)
                ->where('date', $date . ' 00:00:00')
                ->with(['roster', 'shift'])
                ->get();
            if(!$leave && $rosterDates->count() > 0) {
                foreach($rosterDates as $rosterDate)
                {
                    $formatted = $rosterDate->roster?->formatted() ?: [];
                    foreach($formatted as $i => $rosterItem) {
                        if($rosterItem['employees'] && in_array($this->user->id, $rosterItem['employees']))
                        {
                            $selectedNoShift = $rosterDate->roster?->no_shift_duty[$i][$date] ?? null;
                            if($selectedNoShift)
                            {
                                $isPaidLeave = $selectedNoShift !== "LWP";
                                $leave = (object) [
                                    'id' => 0,
                                    'is_paid' => $isPaidLeave,
                                    'is_roster' => true,
                                    'title' => "Roster: " . $selectedNoShift,
                                    'type' => $selectedNoShift,
                                    'leave_type' => (object) [
                                        'id' => 0,
                                        'name' => $selectedNoShift,
                                        'is_paid' => $isPaidLeave,
                                        'bg_color' => 'blue',
                                    ]
                                ];
                            }
                        }
                    }
                }
            }

            $this->dates[$date] = [
                'attendance' => $att,
                'shift' => $shift,
                'leave' => $leave,
                'holiday' => $holiday,
                'is_weekend' => $is_weekend,
                'is_roster' => $att && $att->is_in_roster,
                'max_working_seconds' => $max_seconds,
                'shift_starts' => $today_shift_starts,
                'shift_ends' => $today_shift_ends,
            ];
        }

        $days = [];
        foreach ($this->dates as $date => $data) {
            if($data['is_weekend'] || $data['holiday'] || (
                $data['leave'] && $data['leave']->leave_type?->is_paid && ($data['leave']->leave_type?->affects_leave_balance ?? true)
            )) {
                $days[] = $date;
            }
            if(count($days) > 0) {
                $this->grouped_paid_holidays_leaves_weekends[] = $days;
                $days = [];
            }
        }

        $this->system_notes[] = [
            'type' => 'info',
            'name' => 'Worked Days',
            'description' => 'Worked Days: ' . $this->emplyee_worked_days,
            'value' => $this->emplyee_worked_days,
            'is_incremental' => null,
            'balance' => $this->earned_basic_salary,
        ];

        return $this->dates;
    }

    function is_eligible_for_holiday_weekend($date) {
        $isEligibleForTheHoliday = false;
        if(
            config('services.need_to_have_before_after_attendance_for_paid_leave_weekend')
            || config('services.need_to_have_before_or_after_attendance_for_paid_leave_weekend')
        ) {
            // check before and after attendance or paid leave
            foreach($this->grouped_paid_holidays_leaves_weekends as $group) {
                if(
                    in_array($date, $group)
                ) {
                    $startDate = Carbon::parse($group[0]);
                    $endDate = Carbon::parse($group[count($group) - 1]);
                    // in the middle of the month
                    $isOnMonthStarting = $startDate->isSameDay($this->from_date);
                    $isOnMonthEnding = $endDate->isSameDay($this->end_date);
                    $haveAttBefore = (
                        ($this->dates[$startDate->copy()->subDay()->format('Y-m-d')]['attendance'] ?? null)
                        || (($leaveBefore = $this->dates[$startDate->copy()->subDay()->format('Y-m-d')]['leave'] ?? null) && $leaveBefore->leave_type?->is_paid && ($leaveBefore->leave_type?->affects_leave_balance ?? true))
                    );
                    $haveAttAfter = (
                        ($this->dates[$endDate->copy()->addDay()->format('Y-m-d')]['attendance'] ?? null)
                        || (($leaveAfter = $this->dates[$endDate->copy()->addDay()->format('Y-m-d')]['leave'] ?? null) && $leaveAfter->leave_type?->is_paid && ($leaveAfter->leave_type?->affects_leave_balance ?? true))
                    );
                    if(
                        config('services.need_to_have_before_after_attendance_for_paid_leave_weekend')
                        && (
                            ($isOnMonthStarting && $haveAttAfter)
                            || ($isOnMonthEnding && $haveAttBefore)
                            || ($haveAttBefore && $haveAttAfter)
                        )
                    )
                    {
                        $isEligibleForTheHoliday = true;
                    }else if(
                        config('services.need_to_have_before_or_after_attendance_for_paid_leave_weekend')
                        && (
                            ($isOnMonthStarting && $haveAttAfter)
                            || ($isOnMonthEnding && $haveAttBefore)
                            || ($haveAttBefore || $haveAttAfter)
                        )
                    )
                    {
                        $isEligibleForTheHoliday = true;
                    }
                    break;
                }
            }
        } else {
            $isEligibleForTheHoliday = true;
        }
        return $isEligibleForTheHoliday;
    }
}
