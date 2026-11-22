<?php

namespace App\Actions;

use App\Jobs\SendAttendanceRelatedSMS;
use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveApplication;
use App\Models\OvertimeSchedule;
use App\Models\OvertimeScheduleDate;
use App\Models\Setting;
use App\Models\Shift;
use App\Services\FindEmployeeShiftByPunchDateTime;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CalculateWorkingTimeOfAttendance
{
    // Execute from a single function
    static function execute(
        Attendance $attendance,
        $return_in_out_data = false,
        ?FindEmployeeShiftByPunchDateTime $findEmployeeShiftByPunchDateTime = null
    ): Attendance|null|array {
        $calculate = new self($attendance, $return_in_out_data, $findEmployeeShiftByPunchDateTime);
        return $calculate->handle();
    }

    /**
     * Run as a Class to organize and devide the code
     */
    protected Attendance $attendance;
    protected bool $return_in_out_data;
    protected ?FindEmployeeShiftByPunchDateTime $findEmployeeShiftByPunchDateTime;
    protected ?Collection $inOuts;
    protected $system_notes = "";
    protected ?array $shift_data;
    protected ?array $calculatedTimes = null;
    protected $worked_from;
    protected $worked_to;
    protected $did_early_check_out;
    protected $lastBreakOutTime;
    protected $min_missed_minutes_to_be_early_out;
    protected $working_time;
    protected $overtime;
    protected $breaktime;

    /**
     * Normal Shift related attributes
     */
    protected ?Shift $shift;
    protected ?Carbon $shift_starts;
    protected ?Carbon $shift_ends;
    protected bool $can_overtime = false;
    protected OvertimeScheduleDate|bool $is_full_overtime_this_date = false;
    protected int $max_full_day_overtime_in_seconds = 0;
    protected $checkedIn;
    protected $checkedOut;
    protected $workingTime;
    protected $workingOvertime;
    protected $breakTime;
    protected $workedFrom;
    protected $workedTo;
    protected $lastOut;
    protected $got_late_check_in;
    protected $late_time = null;
    protected $grace_minutes;
    protected $min_missed_minutes_to_be_late;
    protected $max_working_in_seconds;
    protected $max_overtime_hours;
    protected $max_overtime_in_seconds;
    protected $max_full_day_overtime_hours;
    protected ?OvertimeSchedule $can_overtime_this_date;
    protected $max_is_full_day_overtime_hours;

    function __construct(Attendance $attendance, $return_in_out_data = false, ?FindEmployeeShiftByPunchDateTime $findEmployeeShiftByPunchDateTime = null)
    {
        $this->attendance = $attendance;
        $this->return_in_out_data = $return_in_out_data;
        $this->findEmployeeShiftByPunchDateTime = $findEmployeeShiftByPunchDateTime;
    }
    function handle()
    {
        if(!$this->attendance->employee) return null;
        if (!$this->findEmployeeShiftByPunchDateTime) {
            $this->findEmployeeShiftByPunchDateTime = new FindEmployeeShiftByPunchDateTime(
                $this->attendance->employee,
                carbon($this->attendance->check_in),
                $this->attendance
            );
        }

        $this->shift_data = $this->findEmployeeShiftByPunchDateTime->getData();
        $this->inOuts = $this->attendance
            ->used_in_out_records()
            ->orderBy('date_time', 'asc')
            ->get();
        $this->shift = $this->shift_data['shift'] ?? $this->attendance->employee?->shift;

        $attendance_ignore_in_between_punches = Setting::attendance_ignore_in_between_punches();
        $this->system_notes .= "[attendance_ignore_in_between_punches]: " . ($attendance_ignore_in_between_punches ? 'true' : 'false') . "";
        if ((!$this->shift_data['shift'] && !$this->shift_data['is_roster_day']) || !$this->attendance->employee) return null;

        $this->max_overtime_hours = Setting::query()
            ->where('key', 'overtime_default_limit')
            ->first()?->value ?? 12;
        $this->max_overtime_in_seconds = $this->max_overtime_hours * 60 * 60;
        $this->max_full_day_overtime_hours = Setting::query()
            ->where('key', 'overtime_date_default_limit')
            ->first()?->value ?? 0;
        $this->max_full_day_overtime_in_seconds = $this->max_full_day_overtime_hours * 60 * 60;

        $can_overtime_user = $this->attendance->employee?->can_overtime;
        $this->can_overtime_this_date = OvertimeSchedule::query()
            ->where('user_id', $this->attendance->employee?->id)
            ->whereDate('date', $this->attendance->date)
            ->first();
        $this->can_overtime = $can_overtime_user || (
            $this->can_overtime_this_date || Setting::can_everyone_overtime()
        );
        // check max overtime limit from schedule
        if ($this->can_overtime && $this->can_overtime_this_date?->max_overtime_hours) {
            $this->max_overtime_hours = $this->can_overtime_this_date->max_overtime_hours;
            $this->max_overtime_in_seconds = $this->max_overtime_hours * 60 * 60;
        }

        $this->is_full_overtime_this_date = (OvertimeScheduleDate::query()
            ->where('user_id', $this->attendance->employee?->id)
            ->whereDate('date', $this->attendance->date)
            ->exists() || Setting::can_everyone_special_overtime())
            && (
                !$this->shift->{strtolower(carbon($this->attendance->date)->format('l'))}
                || Holiday::forUser($this->attendance->employee)
                    ->where('date', $this->attendance->date)
                    ->exists()
            );

        // check max overtime limit from schedule
        if ($this->is_full_overtime_this_date)
        {
            if($this->is_full_overtime_this_date instanceof OvertimeScheduleDate && $this->is_full_overtime_this_date->max_overtime_hours)
            {
                $this->max_is_full_day_overtime_hours = $this->is_full_overtime_this_date->max_overtime_hours;
                $this->max_full_day_overtime_in_seconds = $this->max_is_full_day_overtime_hours * 60 * 60;
            }
            else
            {
                $this->max_is_full_day_overtime_hours = (int) (Setting::query()
                    ->where('key', 'overtime_date_default_limit')
                    ->first()?->value ?? 12);
                $this->max_full_day_overtime_in_seconds = $this->max_is_full_day_overtime_hours * 60 * 60;
            }
        }

        // dd(
        //     $this->max_is_full_day_overtime_hours,
        //     $this->max_full_day_overtime_in_seconds,
        //     $this->is_full_overtime_this_date
        // );

        $this->min_missed_minutes_to_be_late = Setting::where('key', 'reports.late.min_late_minutes')->first()?->value ?: 5;
        if (is_numeric($this->min_missed_minutes_to_be_late)) {
            $this->min_missed_minutes_to_be_late = intval($this->min_missed_minutes_to_be_late * 1);
        } else {
            $this->min_missed_minutes_to_be_late = 5;
        }

        $this->grace_minutes = $this->attendance->employee?->grace_minutes ?? 0;
        if (is_numeric($this->grace_minutes)) {
            $this->grace_minutes = intval($this->grace_minutes * 1);
        } else {
            $this->grace_minutes = 0;
        }

        $this->got_late_check_in = false;
        // dd($this->shift_data);
        if ($this->shift_data['is_roster_day']) {
            $this->calculateInOutRecordsWorkingTimeForRosteredShift();
        } else {
            // dd($this->shift_data);
            $this->shift_starts = $this->shift_data['shift_start_at'];
            $this->shift_ends = $this->shift_data['shift_end_at'];
            $this->max_working_in_seconds = $this->shift_starts->diffInSeconds($this->shift_ends);
            $this->shift = $this->shift_data['shift'];
            $this->calculateInOutRecordsWorkingTimeForRegularShift();

            if($this->working_time > $this->max_working_in_seconds)
            {
                $this->working_time = $this->max_working_in_seconds;
            }

            $this->attendance->update([
                'working_time' => $this->working_time,
                'overtime_time' => $this->overtime,
                'worked_to' => $this->workedTo ?? $this->workedFrom,
                'worked_from' => $this->workedFrom,
                'breaktime' => $this->breaktime,
                'check_in' => $this->checkedIn,
                'check_out' => $this->checkedOut,
                'got_late_check_in' => $this->got_late_check_in,
                'late_time' => $this->late_time,
                'system_notes' => $this->system_notes ?? '',
            ]);
        }
        // dd($this->calculatedTimes, $this->return_in_out_data);
        if ($this->return_in_out_data) return $this->calculatedTimes;

        $this->sendSMSNotification();

        if ($this->got_late_check_in && !$this->attendance->late_notified_via_email_at) {
            \App\Jobs\SendLateAttendanceEmail::dispatch($this->attendance);
        }

        return $this->attendance;
    }
    function calculateInOutRecordsWorkingTimeForRosteredShift()
    {
        // get list of unix seconds from shift start and end
        $mappedInOuts = [];
        $shift_unix_seconds = [];
        foreach ($this->shift_data['roster_items'] as $roster_item) {
            $seconds_list = [];
            $start = Carbon::parse($this->attendance->date . ' ' . $roster_item->shift->start);
            $end = Carbon::parse($this->attendance->date . ' ' . $roster_item->shift->end);
            // dd($roster_item->shift->start);
            while ($start->lessThan($end)) {
                $seconds_list[] = $start->timestamp;
                $start->addSecond();
            }
            $shift_unix_seconds = array_merge($shift_unix_seconds, $seconds_list);
        }

        // get list of unix seconds from in-out records
        $in_out_unix_seconds = [];
        $isCheckIn = true;
        $lastCheckIn = null;

        $i = 0;
        foreach ($this->inOuts as $inOut) {
            if (isset($mappedInOuts[$i])) {
                $mappedInOuts[$i][1] = $inOut;
                $i++;
            } else {
                $mappedInOuts[$i] = [
                    $inOut,
                    null,
                    ['worked' => 0, 'overtime' => 0, 'break' => 0]
                ];
            }
        }
        foreach ($mappedInOuts as $i => $mappedInOut) {
            $in = $mappedInOut[0];
            if(!$in) continue;
            if (!$this->checkedIn) $this->checkedIn = $in->date_time?->format('Y-m-d H:i:s');

            // check if late
            if(
                $i == 0 && $in
                && $this->shift_data['shift_start_at']
                && $in->date_time->greaterThan(
                    $this->shift_data['shift_start_at']
                        ->copy()
                        ->addMinutes($this->min_missed_minutes_to_be_late ?? 0)
                )
            )
            {
                $missed = $this->shift_data['shift_start_at']->diffInSeconds($in->date_time);
                $this->got_late_check_in = true;
                $this->attendance->update([
                    'got_late_check_in' => true,
                    'late_time' => gmdate('H:i:s', $missed),
                ]);
            }

            $out = $mappedInOut[1];
            if(!$out) continue;
            if (!$this->checkedOut) $this->checkedOut = $out->date_time?->format('Y-m-d H:i:s');

            $workingSeconds = [];
            if ($in && $out) {
                $tmpStart = Carbon::parse($in->date_time);
                while ($tmpStart->lessThan($out->date_time)) {
                    $workingSeconds[] = $tmpStart->timestamp;
                    $tmpStart->addSecond();
                }
                $in_out_unix_seconds = array_merge($in_out_unix_seconds, $workingSeconds);
                $this->checkedOut = $out->date_time->format('Y-m-d H:i:s');
                $mappedInOuts[$i][2]['worked'] = count(array_intersect($shift_unix_seconds, $workingSeconds));
            }
        }
        // dd($mappedInOuts);
        // get common unix seconds from both lists
        $actual_working_seconds = array_intersect($shift_unix_seconds, $in_out_unix_seconds);
        // get working time from common unix seconds
        sort($actual_working_seconds);
        if (count($actual_working_seconds) > 0) {
            $this->worked_from = date('Y-m-d H:i:s', $actual_working_seconds[0]);
            $this->worked_to = date('Y-m-d H:i:s', $actual_working_seconds[count($actual_working_seconds) - 1]);
        }

        $this->workingTime = count($actual_working_seconds);
        $this->workingTime = seconds_to_hh_mm_ss($this->workingTime);
        $this->attendance->update([
            'working_time' => $this->workingTime,
            'worked_from' => $this->worked_from,
            'worked_to' => $this->worked_to,
            'check_in' => $this->checkedIn,
            'check_out' => $this->checkedOut,
            'roster_id' => $this->shift_data['roster_items']->first()?->roster_id,
            'is_in_roster' => true,
        ]);
        // dd($this->attendance);
        $this->calculatedTimes = [
            'inOuts' => $this->inOuts,
            'mappedInOuts' => $mappedInOuts,
            'checkedIn' => $this->checkedIn,
            'checkedOut' => $this->checkedOut,
            'workingTime' => $this->workingTime,
            'workingOvertime' => $this->workingOvertime,
            'breakTime' => $this->breakTime,
            'workedFrom' => $this->workedFrom,
            'workedTo' => $this->workedTo,
            'did_early_check_out' => $this->did_early_check_out,
        ];
        // dd($this->calculatedTimes);
    }
    function calculateInOutRecordsWorkingTimeForRegularShift()
    {
        /**
         * $inOuts, $shift, $today_shift_starts, $today_shift_ends,
         * $can_overtime, $is_full_overtime_this_date, $max_full_day_overtime_in_seconds,
         * $checkedIn, $checkedOut, $workingTime, $workingOvertime, $breakTime, $workedFrom, $workedTo
         *
         * Map in-out records to in-out pairs
         * [
         *     [in, out, ['worked', 'overtime', 'break']],
         *     [in, null, ['worked', 'overtime', 'break']],
         *     [null, out, ['worked', 'overtime', 'break']], // this is only possible when the punch status is not ignored
         * ]
         */
        // dd($this->inOuts);
        $attendance_ignore_in_between_punches = Setting::attendance_ignore_in_between_punches();
        $mappedInOuts = [];
        $min_missed_minutes_to_be_early_out = Setting::where('key', 'reports.late.min_late_minutes')->first()?->value ?: 5;
        if (is_numeric($min_missed_minutes_to_be_early_out)) {
            $min_missed_minutes_to_be_early_out = intval($min_missed_minutes_to_be_early_out * 1);
        } else {
            $min_missed_minutes_to_be_early_out = 5;
        }

        $i = 0;
        foreach ($this->inOuts as $inOut) {
            if (isset($mappedInOuts[$i])) {
                $mappedInOuts[$i][1] = $inOut;
                $i++;
            } else {
                $mappedInOuts[$i] = [
                    $inOut,
                    null,
                    ['worked' => 0, 'overtime' => 0, 'break' => 0]
                ];
            }
        }
        $this->lastBreakOutTime = null;

        if ($attendance_ignore_in_between_punches) {
            $mappedInOuts = [[
                $this->inOuts->first(),
                $this->inOuts->count() > 1 ? $this->inOuts->last() : null,
                ['worked' => 0, 'overtime' => 0, 'break' => 0]
            ]];
        }
        if($this->inOuts->count()  == 1)
        {
            $this->checkedIn = $this->inOuts->first()->date_time->copy();
        }

        foreach ($mappedInOuts as $index => $in_and_out) {
            $in = $in_and_out[0] ?? null;
            $out = $in_and_out[1] ?? null;
            if ($in) {
                $in_date_time = ($in->date_time)->copy();
                if ($in_date_time->lessThan($this->shift_starts)) {
                    $workedFrom = $this->shift_starts->copy();
                }
            }
            if ($in && $out) {
                $in_date_time = ($in->date_time)->copy();
                $out_date_time = ($out->date_time)->copy();
                $this->checkedIn = $this->checkedIn ?: $in_date_time->copy();
                if ($index === 0) $this->checkedIn = $in_date_time->copy();
                $this->checkedOut = $out_date_time->copy();
                $__workingTime = 0;
                $__workingOvertime = 0;

                if (
                    $in_date_time->lessThan($this->shift_starts)
                    && $out_date_time->lessThanOrEqualTo($this->shift_starts)
                ) {
                    // no time
                    //dd(1);
                    $workedFrom = $this->shift_starts->copy();
                } else
                if (
                    $in_date_time->lessThan($this->shift_starts)
                    && $out_date_time->greaterThan($this->shift_starts)
                    && $out_date_time->lessThanOrEqualTo($this->shift_ends)
                ) {
                    $this->workedFrom = $this->shift_starts->copy();
                    $__workingTime += $this->workedFrom->diffInSeconds($out_date_time);
                    $this->workedTo = $out_date_time->copy();
                    // dd(2, $workedFrom->diffInSeconds($out_date_time));
                } else
                if (
                    $in_date_time->greaterThanOrEqualTo($this->shift_starts)
                    && $out_date_time->lessThanOrEqualTo($this->shift_ends)
                ) {
                    if (!$this->workedFrom)
                        $this->workedFrom = $in_date_time->copy();
                    if (!$this->workedTo || ($this->workedTo && $out_date_time->greaterThan($this->workedTo)))
                        $this->workedTo = $out_date_time->copy();
                    $__workingTime += $in_date_time->diffInSeconds($out_date_time);
                    // dd(3, $in_date_time->diffInSeconds($out_date_time));
                } else
                if (
                    $in_date_time->greaterThanOrEqualTo($this->shift_starts)
                    && $in_date_time->lessThan($this->shift_ends)
                    && $out_date_time->greaterThan($this->shift_ends)
                ) {
                    if (!$this->workedFrom)
                        $this->workedFrom = $in_date_time->copy();
                    $this->workedTo = $this->shift_ends->copy();
                    $__workingTime += $in_date_time->diffInSeconds($this->shift_ends);
                    if ($this->can_overtime) {
                        $__workingOvertime += $this->shift_ends->diffInSeconds($out_date_time);
                        $this->workedTo = $out_date_time->copy();
                    }
                    // dd(
                    //     4, $workingTime, $workingOvertime,
                    //     $today_shift_starts->format('Y-m-d H:i:s'),
                    //     $today_shift_ends->format('Y-m-d H:i:s'),
                    //     $in_date_time->format('Y-m-d H:i:s'),
                    //     $out_date_time->format('Y-m-d H:i:s'),
                    // );
                } else
                if (
                    $in_date_time->lessThan($this->shift_starts)
                    && $out_date_time->greaterThan($this->shift_ends)
                ) {
                    $this->workedFrom = $this->shift_starts->copy();
                    $this->workedTo = $out_date_time->copy();
                    $__workingTime += $this->shift_starts->diffInSeconds($this->shift_ends);
                    if ($this->can_overtime) {
                        $__workingOvertime += $this->shift_ends->diffInSeconds($out_date_time);
                    }
                    // dd(5, $this->shift_starts->diffInSeconds($this->shift_ends));
                } else
                if (
                    $in_date_time->greaterThanOrEqualTo($this->shift_ends)
                    && $out_date_time->greaterThanOrEqualTo($this->shift_ends)
                    && $this->can_overtime
                ) {
                    if (!$this->workedFrom)
                        $this->workedFrom = $in_date_time->copy();
                    $__workingOvertime += $in_date_time->diffInSeconds($out_date_time);
                    // dd(6,
                    //     $workingTime, $workingOvertime,
                    //     $today_shift_starts->format('Y-m-d H:i:s'),
                    //     $today_shift_ends->format('Y-m-d H:i:s'),
                    //     $in_date_time->format('Y-m-d H:i:s'),
                    //     $out_date_time->format('Y-m-d H:i:s'),
                    // );
                } else {
                    // dump(
                    //     "in: " . $in_date_time->format('Y-m-d H:i'),
                    //     "out: " . $out_date_time->format('Y-m-d H:i'),
                    // );
                }

                $this->workingTime += $__workingTime;
                $this->workingOvertime += $__workingOvertime;
                $mappedInOuts[$index][2]['worked'] += $__workingTime;
                $mappedInOuts[$index][2]['overtime'] += $__workingOvertime;
                if (
                    $in_date_time->lessThan($this->shift_ends)
                    && $this->lastBreakOutTime
                    && $in_date_time->greaterThan($this->lastBreakOutTime)
                ) {
                    $__breakTime = $this->lastBreakOutTime->diffInSeconds($in_date_time);
                    $this->breakTime += $__breakTime;
                    $mappedInOuts[$index][2]['break'] += $__breakTime;
                }
                if ($out_date_time->lessThan($this->shift_ends)) {
                    $this->lastBreakOutTime = $out_date_time->copy();
                }
                $this->did_early_check_out = $out_date_time->copy()
                    ->addMinutes($this->min_missed_minutes_to_be_early_out)
                    ->lessThan($this->shift_ends);
            }
        }
        $rawWorkingTime = $this->workingTime + 0;

        // dump($shift->break_time_before_ot_minutes, $workingOvertime);
        if ($this->shift->break_time_before_ot_minutes) {
            $this->workingOvertime -= ($this->shift->break_time_before_ot_minutes * 60);
            $this->system_notes .= "<br> Break time before overtime of " . $this->shift->break_time_before_ot_minutes . " minutes are applied. ";
            if ($this->workingOvertime < 0) $this->workingOvertime = 0;
        }
        // dd($workingOvertime);
        if ($this->workingOvertime < 0) $this->workingOvertime = 0;
        // dump($this->is_full_overtime_this_date, $this->attendance->date);
        if ($this->is_full_overtime_this_date) {
            $this->system_notes .= "<br> Full day overtime is enabled, So the in-out Working hours will be counted as Overtime Hours";
            $this->workingOvertime = $this->workingOvertime + $this->workingTime;

            $this->workingTime = 0;
            if ($this->workingOvertime > ($this->max_full_day_overtime_in_seconds - ($this->shift->break_time_before_ot_minutes * 60))) {
                $this->workingOvertime = $this->max_full_day_overtime_in_seconds - ($this->shift->break_time_before_ot_minutes * 60);
                $this->system_notes .= "<br> Full day overtime is limited to " . seconds_to_hh_mm($this->max_full_day_overtime_in_seconds) . " hours";
            }
        }
        // dd($this->workingOvertime, $this->workingTime);
        $this->calculatedTimes = [
            'inOuts' => $this->inOuts,
            'mappedInOuts' => $mappedInOuts ?? [],
            'checkedIn' => $this->checkedIn,
            'checkedOut' => $this->checkedOut,
            'workingTime' => $this->workingTime,
            'rawWorkingTime' => $rawWorkingTime,
            'workingOvertime' => $this->workingOvertime,
            'breakTime' => $this->breakTime,
            'workedFrom' => $this->workedFrom,
            'workedTo' => $this->workedTo,
            'did_early_check_out' => $this->did_early_check_out,
        ];
        // dd($this->calculatedTimes);

        if ($this->inOuts->count() === 1) {
            $in = $this->inOuts->first();
            $inTime = carbon($in->date_time);
            $this->worked_from = $inTime->copy();
            $this->checkedIn = $inTime->copy();
            if ($inTime->greaterThan($this->shift_starts)) {
                $this->worked_from = $inTime->copy();
                $this->checkedIn = $inTime->copy();
                if ($inTime->greaterThan($this->shift_starts->copy()->addMinutes($this->min_missed_minutes_to_be_late))) {
                    $this->got_late_check_in = true;
                    $this->late_time = gmdate('H:i:s', $this->shift_starts->diffInSeconds($inTime));
                }
            } else {
                $this->worked_from = $this->shift_starts->copy();
                $this->checkedIn = $inTime->copy();
            }
            $this->attendance->update([
                'working_time' => 0,
                'overtime_time' => 0,
                'worked_to' => null,
                'worked_from' => $this->worked_from,
                'breaktime' => 0,
                'check_in' => $inTime,
                'check_out' => null,
                'got_late_check_in' => $this->got_late_check_in,
                'late_time' => $this->late_time,
            ]);
            $this->attendance->used_in_out_records()->sync([$in->id]);
            return $this->attendance;
        }

        $this->system_notes .= '<br> Working time is ' . gmdate('H:i:s', $this->workingTime);

        // add grace time
        if (
            $this->grace_minutes && $this->workingTime
            && (($this->workingTime / 60) > 0)
        ) {
            // check if he needs some grace time or not
            $left = $this->max_working_in_seconds - $this->workingTime;
            if ($left > 0) {
                $grace_seconds = $this->grace_minutes * 60;
                if ($grace_seconds > $left) $grace_seconds = $left;

                $this->workingTime += $grace_seconds;
                $this->system_notes .= "<br> Grace time of " . $this->grace_minutes . " minutes are applied. ";
            }
        }

        // find and add hours from hourly leaves
        $hourly_leaves = LeaveApplication::where('employee_id', $this->attendance->employee_id)
            ->where('is_hourly', true)
            ->whereMonth('start_date', $this->attendance->date)
            ->get();
        foreach ($hourly_leaves as $hourly_leave) {
            $totalSeconds = carbon($hourly_leave->start_time)->diffInSeconds(carbon($hourly_leave->end_time));
            $this->workingTime += $totalSeconds;
            $this->system_notes .= "<br> Hourly leave of " . gmdate('H:i:s', $totalSeconds) . " is added to working time. ";
        }
        // dd(!$this->can_overtime);
        // apply overtime limit
        if ($this->is_full_overtime_this_date) {
            if (!$this->can_overtime) {
                $this->system_notes .= "<br> Overtime is disabled from the Employee profile. Please update his overtime to \"Yes\" to start counting.";
                $this->workingOvertime = 0;
                if (!$this->shift->{strtolower(carbon($this->attendance->date)->format('l'))}) {
                    $this->system_notes .= "<br> The date fall into weekend. So no Working hour added";
                } else {
                    $this->workingTime = $this->calculatedTimes['rawWorkingTime'] ?? 0;
                    $this->system_notes .= "<br> Putting working hour back to \"W. hour\"";
                }
            }
        } else {
            if ($this->can_overtime) {
                if ($this->max_overtime_in_seconds) {
                    if ($this->workingOvertime > $this->max_overtime_in_seconds) {
                        $this->workingOvertime = $this->max_overtime_in_seconds;
                        $this->system_notes .= "<br> Overtime limit of " . ($this->max_overtime_hours ?? 0) . " Hour exceeded. ";
                    }
                }
            } else {
                if ($this->workingOvertime) {
                    $this->system_notes .= "<br> Overtime did not count because he wasn't assigned to overtime for this date. ";
                }
                $this->workingOvertime = 0;
            }
        }
        // dd($workingOvertime);
        if ($this->shift->break_time_minutes) {
            $break_time_minutes_in_seconds = $this->shift->break_time_minutes * 60;
            $tmp = $this->workingTime - $break_time_minutes_in_seconds;
            if ($tmp < 0) $tmp = 0;
            $this->workingTime = $tmp;
            $this->breakTime = $break_time_minutes_in_seconds;
            $this->system_notes .= "<br> Break time of " . $this->shift->break_time_minutes . " minutes are applied.  ";
        }

        /**
         * Update the workingOvertime with settings information
         */
        // if (Setting::overtime_policy_configuration()) {
        //     $over_time_settings = Setting::auto_over_time_policy();


        //     if ("1_hour" == $over_time_settings) {
        //         $this->workingTime = 3600 > $this->workingTime ? 0 : $this->workingTime - ($this->workingTime % 3600);
        //     }

        //     if ("30_minute" == $over_time_settings) {
        //         $this->workingTime = 1800 > $this->workingTime ? 0 : $this->workingTime - ($this->workingTime % 1800);
        //     }

        //     if ("15_minute" == $over_time_settings) {
        //         $this->workingTime = 900 > $this->workingTime ? 0 : $this->workingTime - ($this->workingTime % 900);
        //     }
        // }


        // dd($calculatedTimes);
        // Convert total working time to hours and minutes
        $this->working_time = gmdate('H:i:s', $this->workingTime);
        $this->overtime = gmdate('H:i:s', $this->workingOvertime);
        $this->breaktime = gmdate('H:i:s', $this->breakTime);
        if (($this->workingTime || $this->workingOvertime) && $this->workedFrom) {
            if ($this->workedFrom->greaterThan($this->shift_starts->copy()->addMinutes($this->min_missed_minutes_to_be_late))) {
                $this->got_late_check_in = true;
                $this->late_time = gmdate('H:i:s', $this->shift_starts->diffInSeconds($this->workedFrom));
            }
        }
    }
    function sendSMSNotification()
    {
        $lastOut = $this->lastOut;
        if (!$this->calculatedTimes) return;
        $calculatedTimes = $this->calculatedTimes;
        foreach ($calculatedTimes['mappedInOuts'] ?? [] as $index => $mapped) {
            $in = $mapped[0] ?? null;
            $out = $mapped[1] ?? null;
            if ($in) {
                // detect late punch
                if ($index === 0 && $this->attendance->got_late_check_in) {
                    if ($in->admin_sms_notified == 0 && Setting::can_company_in_out_sms_notification()) {
                        // late to company
                        SendAttendanceRelatedSMS::dispatch('late', true, $in, $this->attendance);
                    }
                    if ($in->sms_notified == 0 ) {
                        // late to employee
                        SendAttendanceRelatedSMS::dispatch('late', false, $in, $this->attendance);
                    }
                }

                if ($in->admin_sms_notified == 0 && Setting::can_company_in_out_sms_notification()) {
                    // send to company
                    SendAttendanceRelatedSMS::dispatch('in', true, $in, $this->attendance);
                }
                if ($in->sms_notified == 0 ) {
                    // send to employee
                    SendAttendanceRelatedSMS::dispatch('in', false, $in, $this->attendance);
                }
                if ($in->sms_notified == 0 && $index === 0) {
                    // send to employee
                    SendAttendanceRelatedSMS::dispatch('first_in', false, $in, $this->attendance);
                }
                $in->update([
                    'admin_sms_notified' => 1,
                    'sms_notified' => 1,
                ]);
            };
            if ($out && ($out->admin_sms_notified == 0 || $out->sms_notified == 0)) {
                $lastOut = $out;
                if ($out->admin_sms_notified == 0 && Setting::can_company_in_out_sms_notification()) {
                    // send to company
                    SendAttendanceRelatedSMS::dispatch('out', true, $out, $this->attendance);
                }
                if ($out->sms_notified == 0 ) {
                    // send to employee
                    SendAttendanceRelatedSMS::dispatch('out', false, $out, $this->attendance);
                }
                $out->update([
                    'admin_sms_notified' => 1,
                    'sms_notified' => 1,
                ]);
            }
        }
        // send summary sms
        if (
            $lastOut && (Setting::can_company_summary_sms_notification() || Setting::can_summary_sms_notification())
            && ($this->attendance->summary_sms_notified == 0 || $this->attendance->admin_summary_sms_notified == 2)
            && $this->shift_ends->copy()->subHour()->lessThan($lastOut->date_time)
        ) {
            // send to employee
            if ($this->attendance->summary_sms_notified == 0 && Setting::can_summary_sms_notification()) {
                // send to employee
                SendAttendanceRelatedSMS::dispatch('duty_summary', false, $lastOut, $this->attendance);
            }
            if ($this->attendance->admin_summary_sms_notified == 0 && Setting::can_company_summary_sms_notification()) {
                // send to admin
                SendAttendanceRelatedSMS::dispatch('duty_summary', true, $lastOut, $this->attendance);
            }
            $this->attendance->update([
                'admin_summary_sms_notified' => 1,
                'summary_sms_notified' => 1,
            ]);
        }
    }
}
