<?php

namespace App\Helpers;

use Carbon\Carbon;

class DurationHelper
{
    /**
     * Calculate leave duration (hourly or daily)
     *
     * @param bool $isHourly Is this an hourly leave?
     * @param \Carbon\Carbon|string $startDate Start date (Carbon or Y-m-d string)
     * @param \Carbon\Carbon|string $endDate End date (Carbon or Y-m-d string)
     * @param string|null $startTime Start time for hourly leave (H:i:s or H:i)
     * @param string|null $endTime End time for hourly leave (H:i:s or H:i)
     * @return array ['value' => float, 'unit' => string, 'label' => string]
     */
    public static function calculate(
        bool $isHourly,
        $startDate,
        $endDate,
        ?string $startTime = null,
        ?string $endTime = null
    ): array
    {

        // ------------------------ HOURLY LEAVE ------------------------
        if ($isHourly) {

            // Ensure $startDate/$endDate are Carbon objects
            $startDate = $startDate instanceof Carbon ? $startDate : Carbon::parse($startDate);
            $endDate = $endDate instanceof Carbon ? $endDate : Carbon::parse($endDate);

            // Combine date + time safely
            $start = $startDate->copy()->setTimeFromTimeString($startTime);
            $end = $endDate->copy()->setTimeFromTimeString($endTime);

            // Get duration in minutes -> convert to hours
            $minutes = $start->diffInMinutes($end);
            $hours = round($minutes / 60, 2);

            $unit = $hours > 1 ? 'Hours' : 'Hour';

            return [
                'value' => $hours,
                'unit' => $unit,
                'label' => "{$hours} {$unit}",
            ];
        }

        // ------------------------ DAILY LEAVE ------------------------
        $startDate = $startDate instanceof Carbon ? $startDate->copy()->startOfDay() : Carbon::parse($startDate)->startOfDay();
        $endDate = $endDate instanceof Carbon ? $endDate->copy()->startOfDay() : Carbon::parse($endDate)->startOfDay();

        // diffInDays returns 0 for same day -> +1 for inclusive
        $days = $startDate->diffInDays($endDate) + 1;

        $unit = $days > 1 ? 'Days' : 'Day';

        return [
            'value' => $days,
            'unit' => $unit,
            'label' => "{$days} {$unit}",
        ];
    }


    /**
     * Attendance time calculation helper
     *
     * @param Carbon|null $shiftStartCarbon
     * @param Carbon|null $shiftEndCarbon
     * @param mixed $attendance
     * @return array
     */
    public static function calculateAttendanceTime($shiftStartCarbon, $shiftEndCarbon, $attendance): array
    {
        // default response
        // ==============================
        $result = [
            'worked_minutes' => 0,
            'expected_minutes' => 0,
            'missed_minutes' => 0,
            'missed_time_text' => '',
        ];

        // check punch existence (IMPORTANT)
        // ==============================
            $checkInCarbon = $attendance ?->check_in ? Carbon::parse($attendance->check_in) : null;
            $checkOutCarbon = $attendance ?->check_out ? Carbon::parse($attendance->check_out) : null;

            // ❗ only run when BOTH exist
            if (!$checkInCarbon || !$checkOutCarbon) {
                return $result;
            }

            // expected minutes
            // ==============================
            $expectedMinutes = 0;

            if ($shiftStartCarbon && $shiftEndCarbon) {
                $expectedMinutes = $shiftStartCarbon->diffInMinutes($shiftEndCarbon);
            }

            // worked minutes (REAL PUNCH)
            // ==============================
            $workedMinutes = $checkInCarbon->diffInMinutes($checkOutCarbon);


            // missed minutes
            // ==============================
            $missedMinutes = 0;

            if ($expectedMinutes > 0) {
                $missedMinutes = max(0, $expectedMinutes - $workedMinutes);
            }

            // human readable
            // ==============================
            $missedText = '';

            if ($missedMinutes > 0) {

                $hours = intdiv((int) $missedMinutes, 60);
                $minutes = (int) $missedMinutes % 60;

                if ($hours > 0) {
                    $missedText = $hours . ' hour' . ($hours > 1 ? 's' : '');

                    if ($minutes > 0) {
                        $missedText .= ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
                    }
                } else {
                    $missedText = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
                }
            }

            // return
            // ==============================
            return [
                'worked_minutes' => $workedMinutes,
                'expected_minutes' => $expectedMinutes,
                'missed_minutes' => $missedMinutes,
                'missed_time_text' => $missedText,
            ];
        }
}
