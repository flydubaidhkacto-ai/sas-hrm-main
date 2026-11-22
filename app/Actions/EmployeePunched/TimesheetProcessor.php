<?php
namespace App\Actions\EmployeePunched;

use Carbon\Carbon;

class TimesheetProcessor
{
    protected $maxHoursPerShift = 15;

    public function process($timestamps)
    {
        // Sort timestamps
        $sorted = collect($timestamps)
            ->map(fn($ts) => $ts instanceof Carbon ? $ts : Carbon::parse($ts))
            ->sort()
            ->values()
            ->toArray();

        $completedShifts = [];
        $openShifts = [];
        $pendingCheckIn = null;

        foreach ($sorted as $timestamp) {
            if ($pendingCheckIn === null) {
                // This is a check-in
                $pendingCheckIn = $timestamp;
            } else {
                // This could be a check-out
                $hoursDiff = $pendingCheckIn->diffInHours($timestamp);

                if ($hoursDiff <= $this->maxHoursPerShift) {
                    // Valid check-out
                    $completedShifts[] = [
                        'check_in' => $pendingCheckIn,
                        'check_out' => $timestamp,
                    ];
                    $pendingCheckIn = null;
                } else {
                    // Too long, auto-close previous and treat this as new check-in
                    $completedShifts[] = [
                        'check_in' => $pendingCheckIn,
                        'check_out' => null,
                    ];
                    $pendingCheckIn = $timestamp;
                }
            }
        }

        // Handle any pending check-in
        if ($pendingCheckIn !== null) {
            $now = Carbon::now();
            $hoursSince = $pendingCheckIn->diffInHours($now);

            if ($hoursSince > $this->maxHoursPerShift) {
                // Auto-checkout after max hours
                $completedShifts[] = [
                    'check_in' => $pendingCheckIn,
                    'check_out' => null,
                ];
            } else {
                // Store as open shift
                $openShifts[] = [
                    'check_in' => $pendingCheckIn,
                    'hours_elapsed' => round($hoursSince, 2)
                ];
            }
        }

        return [
            'completed' => $this->summarizeByDate($completedShifts),
            'open_shifts' => $this->formatOpenShifts($openShifts)
        ];
    }

    protected function formatOpenShifts($openShifts)
    {
        return collect($openShifts)->map(function ($shift) {
            return [
                'check_in' => $shift['check_in']->format('Y-m-d H:i:s'),
                'check_in_time' => $shift['check_in']->format('H:i:s'),
                'date' => $shift['check_in']->format('Y-m-d'),
                'hours_elapsed' => $shift['hours_elapsed'],
                'status' => $shift['hours_elapsed'] < 1 ? 'just_started' : 'in_progress'
            ];
        })->toArray();
    }

    protected function summarizeByDate($shifts)
    {
        $grouped = [];

        foreach ($shifts as $shift) {
            $date = $shift['check_in']->format('Y-m-d');

            if (!isset($grouped[$date])) {
                $grouped[$date] = [
                    'date' => $date,
                    'day_of_week' => $shift['check_in']->format('l'),
                    'shifts' => [],
                    'total_hours' => 0
                ];
            }

            $hours = ($shift['check_out']?->diffInMinutes($shift['check_in']) ?: 0) / 60;

            $grouped[$date]['shifts'][] = [
                'check_in' => $shift['check_in']->format('Y-m-d H:i:s'),
                'check_out' => $shift['check_out']?->format('Y-m-d H:i:s'),
                'check_in_time' => $shift['check_in']->format('H:i:s'),
                'check_out_time' => $shift['check_out']?->format('H:i:s'),
                'hours_worked' => round($hours, 2),
                'is_night_shift' => $shift['check_in']->day !== $shift['check_out']?->day,
                'is_auto_checkout' => $shift['is_auto_checkout']
            ];

            $grouped[$date]['total_hours'] += $hours;
        }

        // Format total hours
        foreach ($grouped as &$day) {
            $day['total_hours'] = round($day['total_hours'], 2);
            $day['shift_count'] = count($day['shifts']);
        }

        return $grouped;
    }
}

