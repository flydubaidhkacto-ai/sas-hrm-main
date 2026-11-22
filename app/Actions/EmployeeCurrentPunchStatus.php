<?php

namespace App\Actions;

use App\Actions\EmployeePunched\FindEmployeeShiftByPunchDateTime;
use App\Models\AllowedPunchRegion;
use App\Models\Attendance;
use App\Models\InOutRecord;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class EmployeeCurrentPunchStatus {
    static function execute(User $user)
    {
        $checkScheduleData = FindEmployeeShiftByPunchDateTime::getData($user, now());
        $is_checked_in = false;

        $totalSeconds = 0;
        $shift_start_at = null;
        $shift_end_at = null;
        $skip_last_collection = false;
        $attendance = null;
        if($checkScheduleData['shift_start_at']) {
            $currentDate = $checkScheduleData['shift_start_at']->toDateString();
            $currentTime = $checkScheduleData['shift_start_at']->toTimeString();

            if ($checkScheduleData['is_roster_day'] == true) {

                foreach ($checkScheduleData['roster_items'] as $roster) {
                    if (Carbon::parse($roster['date'])->format('Y-m-d') == $currentDate) {

                        if ($shift_start_at == null || $shift_start_at > Carbon::parse($roster['shift']->start)->format('H:i:s')) {
                            $shift_start_at = Carbon::parse($roster['shift']->start)->format('H:i:s');
                        }

                        if ($shift_end_at == null || $shift_end_at < Carbon::parse($roster['shift']->end)->format('H:i:s')) {
                            $shift_end_at = Carbon::parse($roster['shift']->end)->format('H:i:s');
                        }
                    }
                }
                $shift_start_at = Carbon::parse("$currentDate $shift_start_at");
                $shift_end_at = Carbon::parse("$currentDate $shift_end_at");
                $attendance = Attendance::query()
                    ->where('employee_id', $user->id)
                    ->where('date', $currentDate)
                    ->first();
                $count_how_many_time_employee_active = $attendance ? $attendance->used_in_out_records()
                    ->orderBy('date_time', 'asc')->get() : collect([]);
                $total_records = count($count_how_many_time_employee_active);

                $is_checked_in = $total_records % 2 == 0 ? false : true;

                // dd($total_records);
                $skip_last_collection = $total_records % 2 == 0 ? false : true;

                $active_seconds = 0;
                foreach ($count_how_many_time_employee_active as $key => $value) {
                    if ($skip_last_collection && ($key + 1) == $total_records) {
                        $last_time = $value->created_at ?: $value->date_time;

                        $totalSeconds += $last_time->diffInSeconds(now());

                        break;
                    }
                    if (isset($count_how_many_time_employee_active[$key + 1]) && $value->status == 1) {
                        // Get the current and next `date_time`
                        $currentTime = Carbon::parse($value->date_time);
                        $nextTime = Carbon::parse($count_how_many_time_employee_active[$key + 1]->date_time);

                        // Calculate the difference in seconds and add to total
                        $totalSeconds += $currentTime->diffInSeconds($nextTime);
                    }
                }
            } else {
                $shift_start_at = $checkScheduleData['shift_start_at'];
                $shift_end_at = $checkScheduleData['shift_end_at'];

                $attendance = Attendance::query()
                    ->where('employee_id', $user->id)
                    ->where('date', $currentDate)
                    ->first();
                $count_how_many_time_employee_active = $attendance ? $attendance->used_in_out_records()
                    ->orderBy('date_time', 'asc')->get() : collect([]);
                // dd(array_key_last($count_how_many_time_employee_active));
                $total_records = count($count_how_many_time_employee_active);

                $is_checked_in = $total_records % 2 == 0 ? false : true;
                $skip_last_collection = $total_records % 2 == 0 ? false : true;

                $active_seconds = 0;
                foreach ($count_how_many_time_employee_active as $key => $value) {
                    if ($skip_last_collection && ($key + 1) == $total_records) {
                        $last_time = $value->created_at ?: $value->date_time;

                        $totalSeconds += $last_time->diffInSeconds(now());

                        break;
                    }
                    if (isset($count_how_many_time_employee_active[$key + 1]) && $value->status == 1) {
                        // Get the current and next `date_time`
                        $currentTime = Carbon::parse($value->date_time);
                        $nextTime = Carbon::parse($count_how_many_time_employee_active[$key + 1]->date_time);

                        // Calculate the difference in seconds and add to total
                        $totalSeconds += $currentTime->diffInSeconds($nextTime);
                    }
                }
            }
        }

        return [
            'count' => $total_records ?? 0,
            'is_checked_in' => $is_checked_in,
            'active_time' => $totalSeconds,
            'start_time' => $shift_start_at,
            'end_time' => $shift_end_at,
            'start_watch' => $skip_last_collection,
            'restricted_areas' => Setting::restrict_attendance_location() && AllowedPunchRegion::count()
                ? AllowedPunchRegion::get() : collect([]),
        ];
    }
}
