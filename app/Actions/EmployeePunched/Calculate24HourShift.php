<?php
namespace App\Actions\EmployeePunched;

use App\Models\Attendance;
use App\Models\InOutRecord;
use App\Models\Setting;
use Carbon\Carbon;

class Calculate24HourShift {
    static function execute(InOutRecord $in_out_record): ?Attendance
    {
        $shift = $in_out_record->employee->shift;
        $max_working_seconds = ($shift->max_working_minutes ?: (60 * 8)) * 60;
        $max_overtime_seconds = ($shift->max_overtime_minutes ?? 0) * 60;
        $total_max_seconds = $max_working_seconds + $max_overtime_seconds;

        // Determine check-in/out status using simplified logic
        $is_check_out = self::determineCheckoutStatus($in_out_record, $total_max_seconds);
        // Save the state
        $in_out_record->status = $is_check_out ? 1 : 0;
        $in_out_record->save();

        // Get the attendance record
        $attendance = self::getOrCreateAttendance($in_out_record, $total_max_seconds);

        if (!$attendance) {
            return null;
        }

        // Recalculate working hours
        self::recalculateWorkingHours($attendance, $max_working_seconds, $max_overtime_seconds);

        return $attendance;
    }

    static function determineCheckoutStatus(InOutRecord $in_out_record, $total_max_seconds): bool
    {
        // Check device-specific state if enabled
        if (Setting::block_auto_inout_state()) {
            if ($in_out_record->cloud_device?->inoutstate !== null) {
                return $in_out_record->cloud_device->inoutstate == 1;
            } else if ($in_out_record->device?->inoutstate !== null) {
                return $in_out_record->device->inoutstate == 1;
            }
        }

        // Auto-determine by checking last punch
        $last_punch = InOutRecord::where('user_id', $in_out_record->user_id)
            ->where('id', '!=', $in_out_record->id)
            ->where('date_time', '>=', $in_out_record->date_time->copy()->subSeconds($total_max_seconds))
            ->where('date_time', '<', $in_out_record->date_time)
            ->orderBy('date_time', 'desc')
            ->first();

        // If no recent punch or last was checkout, this is check-in
        return $last_punch && $last_punch->status == 0;
    }

    static function getOrCreateAttendance(InOutRecord $in_out_record, $total_max_seconds): ?Attendance
    {
        $is_checkout = $in_out_record->status == 1;

        // For check-in, use current date. For checkout, find the matching check-in
        if ($is_checkout) {
            $matching_checkin = InOutRecord::where('user_id', $in_out_record->user_id)
                ->where('status', 0)
                ->where('id', '!=', $in_out_record->id)
                ->where('date_time', '>=', $in_out_record->date_time->copy()->subSeconds($total_max_seconds))
                ->where('date_time', '<', $in_out_record->date_time)
                ->orderBy('date_time', 'desc')
                ->first();

            if (!$matching_checkin) {
                // $in_out_record->logs = "Unexpected: No matching check-in found";
                // $in_out_record->save();
                return null;
            }

            $date = $matching_checkin->date_time->format('Y-m-d');
        } else {
            $date = $in_out_record->date_time->format('Y-m-d');
        }

        return Attendance::firstOrCreate([
            'employee_id' => $in_out_record->user_id,
            'date' => $date,
        ], [
            'shift_id' => $in_out_record->employee->shift_id,
            'roster_id' => null,
            'check_in' => null,
            'check_out' => null,
            'working_time' => '00:00:00',
            'overtime_time' => '00:00:00',
            'worked_from' => null,
            'worked_to' => null,
            'breaktime' => '00:00:00',
            'got_late_check_in' => false,
        ]);
    }

    static function recalculateWorkingHours(Attendance $attendance, $max_working_seconds, $max_overtime_seconds): void
    {
        // Get all punches for this date (and early next day for night shifts)
        $date = Carbon::parse($attendance->date);
        $punches = InOutRecord::where('user_id', $attendance->employee_id)
            ->where(function($query) use ($date) {
                $query->whereDate('date_time', $date)
                    ->orWhere(function($q) use ($date) {
                        $q->whereDate('date_time', $date->copy()->addDay())
                          ->whereTime('date_time', '<=', '12:00:00');
                    });
            })
            ->orderBy('date_time')
            ->get();

        // Sync the punches
        $attendance->used_in_out_records()->sync($punches->pluck('id')->toArray());

        // Process into pairs
        $pairs = [];
        $pending_checkin = null;

        foreach ($punches as $punch) {
            if ($punch->status == 0 && !$pending_checkin) {
                $pending_checkin = $punch;
            } elseif ($punch->status == 1 && $pending_checkin) {
                $pairs[] = ['check_in' => $pending_checkin, 'check_out' => $punch];
                $pending_checkin = null;
            }
        }

        // Calculate totals
        $total_working_seconds = 0;
        $first_checkin = null;
        $last_checkout = null;

        foreach ($pairs as $pair) {
            $worked = $pair['check_in']->date_time->diffInSeconds($pair['check_out']->date_time);
            $total_working_seconds += $worked;

            if (!$first_checkin || $pair['check_in']->date_time->lt($first_checkin->date_time)) {
                $first_checkin = $pair['check_in'];
            }
            if (!$last_checkout || $pair['check_out']->date_time->gt($last_checkout->date_time)) {
                $last_checkout = $pair['check_out'];
            }
        }

        // Apply limits
        $working_seconds = min($total_working_seconds, $max_working_seconds);
        $overtime_seconds = 0;

        if ($total_working_seconds > $max_working_seconds) {
            $overtime_seconds = min($total_working_seconds - $max_working_seconds, $max_overtime_seconds);
        }

        // Update attendance
        $attendance->update([
            'working_time' => seconds_to_hh_mm_ss($working_seconds),
            'overtime_time' => seconds_to_hh_mm_ss($overtime_seconds),
            'worked_from' => $first_checkin?->date_time,
            'worked_to' => $last_checkout?->date_time,
            'check_in' => $first_checkin?->date_time,
            'check_out' => $last_checkout?->date_time,
        ]);
    }

    /**
     * For other codes
     */
    static function mappedInOuts(Attendance $attendance): array
    {
        $inouts = $attendance->used_in_out_records()->orderBy('date_time')->get();
        $mappedInOuts = [];
        $i = 0;
        if($inouts->first()?->status == 1)
        {
            // remove first check out if exist because this is previous dates data
            $inouts = $inouts->slice(1);
        }
        foreach ($inouts as $inOut) {
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
            $out = $mappedInOut[1];
            if($out && $in) {
                $worked = $in->date_time->diffInSeconds($out->date_time);
                $mappedInOuts[$i][2]['worked'] = $worked;
            }
        }
        return $mappedInOuts;
    }
}
