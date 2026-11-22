<?php

namespace App\Actions;

use App\Models\Holiday;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class GenerateDailyWorkingData
{
    static function execute(Collection $attendances, Carbon $from_date, Carbon $end_date, User $employee)
    {
        $dates = [];
        $base_salary = $employee->payroll_card->basic_salary;
        $shift = $employee->shift ?: Shift::first();
        $total_max_seconds = 0;
        $worked_days = 0;
        $worked_seconds = 0;
        $overtimed_seconds = 0;

        # Step 1: Calculate working hours by date
        foreach ($attendances as $attendance) {
            if (!isset($dates[$attendance->date])) {
                $dates[$attendance->date] = [];
            }
            $dates[$attendance->date]['salary_per_seconds'] = 0;
            $dates[$attendance->date]['shift'] = $attendance->shift;
            $dates[$attendance->date]['attendance'] = $attendance;
            // if ($attendance->working_time !== '00:00:00' && $attendance->working_time) {
            $worked_days += 1;
            // }
            $worked_seconds += time_to_seconds($attendance->working_time ?? '00:00:00');
            $overtimed_seconds += time_to_seconds($attendance->overtime_time ?? '00:00:00');
            $dates[$attendance->date]['overtime_time'] = time_to_seconds($attendance->overtime_time ?? '00:00:00');
            $dates[$attendance->date]['worked_time'] = time_to_seconds($attendance->working_time ?? '00:00:00');
        }
        // dd($dates);
        $all_dates = generateDateRange($from_date, $end_date);

        // remove dates before join date
        if ($employee->join_date) {
            foreach ($all_dates as $date) {
                if (Carbon::parse($date)->lessThan(Carbon::parse($employee->join_date))) {
                    unset($all_dates[$date]);
                }
            }
        }
        //- Get paid by public holidays
        $public_holidays = Holiday::query()
            ->whereIn("date", $all_dates)
            ->get();
        $max_working_days = 0;
        $grace_days = 0;
        $holidays_count = $public_holidays->count();
        $total_weekends = 0;
        $max_working_seconds_on_a_day = 0;

        # Step 2: Calculate working hours by shift
        foreach (
            $all_dates as $date
        ) {
            $current_shift = $shift;
            if (($dates[$date] ?? false) && $dates[$date]['shift']) {
                $current_shift = $dates[$date]['shift'];
            }
            $max_seconds = 0;
            $today_shift_starts = carbon()->setTime(
                carbon($current_shift->start)->hour,
                carbon($current_shift->start)->minute,
                carbon($current_shift->start)->second
            );
            $today_shift_ends = carbon()->setTime(
                carbon($current_shift->end)->hour,
                carbon($current_shift->end)->minute,
                carbon($current_shift->end)->second
            );
            if ($current_shift->is_night_shift) $today_shift_ends->addDay();
            $max_seconds = $current_shift->duration;
            // remove the shift break time

            $dates[$date]['max_working_seconds'] = $max_seconds;
            $dates[$date]['max_working_hours'] = $max_seconds > 0 ? $max_seconds / 60 / 60 : 0;
            $total_max_seconds += $max_seconds;

            // dd(Holiday::query()->where('date', $date)->exists());
            if (
                $current_shift->{strtolower(carbon($date)->format('l'))}
            ) {
                $max_working_days++;
                $dates[$date]['is_working_day'] = true;
            }
            else {
                $total_weekends += 1;
                $dates[$date]['is_weekend'] = true;
            }
            $max_working_seconds_on_a_day = $max_seconds;
        }

        if ($public_holidays->count() > 0) {
            foreach ($public_holidays as $holiday) {
                $dates[$holiday->date]['is_holiday'] = true;
                $dates[$holiday->date]['holiday_name'] = $holiday->name;
                $dates[$holiday->date]['max_working_seconds'] = $max_working_seconds_on_a_day;
                $dates[$holiday->date]['max_working_hours'] = $max_working_seconds_on_a_day ? $max_working_seconds_on_a_day / 60 / 60 : 0;
            }
        }

        // for asp
        if(config('app.customer') == 'asp') {
            // keep the
            if($max_working_days < 30) {
                $grace_days = 30 - $max_working_days;
                $max_working_days = 30;
            }
        }

        $salary_per_day = $max_working_days ? number_format($base_salary / $max_working_days, 4, '.', '') : 0;

        # Step 3: Calculate salary per seconds/hours
        foreach (
            $all_dates as $date
        ) {
            // if (
            //     $dates[$date]['is_holiday'] ?? false
            // ) {
            //     $salary_per_seconds = $dates[$date]['max_working_seconds'] ? $salary_per_day / $dates[$date]['max_working_seconds'] : 0;
            //     $dates[$date]['salary_per_seconds'] = $salary_per_seconds;
            // }
            // if (
            //     $dates[$date]['is_working_day'] ?? false
            // ) {
                $salary_per_seconds = $dates[$date]['max_working_seconds'] ? $salary_per_day / $dates[$date]['max_working_seconds'] : 0;
                $dates[$date]['salary_per_seconds'] = $salary_per_seconds;
            // }
        }

        return [
            'total_max_seconds' => $total_max_seconds,
            'dates' => $dates,
            'worked_days' => $worked_days,
            'worked_seconds' => $worked_seconds,
            'overtimed_seconds' => $overtimed_seconds,
            'max_working_days' => $max_working_days,
            'salary_per_day' => $salary_per_day,
            'total_holidays' => $holidays_count,
            'total_weekends' => $total_weekends,
            'grace_days' => $grace_days,
        ];
    }
}
