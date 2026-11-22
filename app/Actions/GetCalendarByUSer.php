<?php
namespace App\Actions;

use App\Models\Holiday;
use App\Models\RosterItem;
use App\Models\ShiftScheduleItem;
use App\Models\User;

class GetCalendarByUSer {
    static function execute(User $user, int $month, int $year) {
        $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $shift = $user->shift;
        $events = [];

        // Fetch holidays for the month
        $holidays = Holiday::whereYear('date', $year)->whereMonth('date', $month)->get()->keyBy('date');

        // Determine allowed working days from shift
        $weekDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $allowedDays = [];

        if ($shift) {
            foreach ($weekDays as $index => $day) {
                if ($shift->$day == 1) {
                    $allowedDays[] = $index; // Store allowed workdays
                }
            }
        }

        // Batch query roster & shift schedules for performance
        $rosterItems = RosterItem::where('employee_id', $user->id)
            ->whereHas('roster', function ($query) {
                $query->where('is_active', true);
            })
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->mapWithKeys(function ($item) {
                return [date('Y-m-d', strtotime($item->date)) => $item];
            });

        $shiftScheduleItems = ShiftScheduleItem::where('employee_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy('date');

        // Loop through each day of the month
        for ($day = 1; $day <= $totalDays; $day++) {
            $currentDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $currentDayIndex = date('w', strtotime($currentDate));

            // Priority Order: Holiday > Roster > Shift Schedule > General Working Day
            if (isset($holidays[$currentDate])) {
                $events[] = [
                    'id'    => "holiday-{$day}",
                    'title' => $holidays[$currentDate]->name,
                    'start' => $currentDate,
                    'color' => "#FF0000", // Red for holidays
                ];
                continue;
            }

            if (isset($rosterItems[$currentDate])) {
                $events[] = [
                    'id'    => "roster-{$day}",
                    'title' => "Roster",
                    'start' => $currentDate,
                    'color' => "#ff5733", // Red-Orange
                ];
                continue;
            }

            if (isset($shiftScheduleItems[$currentDate])) {
                $events[] = [
                    'id'    => "shift-{$day}",
                    'title' => "Shift Schedule",
                    'start' => $currentDate,
                    'color' => "#3357ff", // Blue
                ];
                continue;
            }

            if (in_array($currentDayIndex, $allowedDays)) {
                $events[] = [
                    'id'    => "general-{$day}",
                    'title' => "General Working Day",
                    'start' => $currentDate,
                    'color' => "#28a745", // Green
                ];
            }
        }
        return $events;
    }
}
