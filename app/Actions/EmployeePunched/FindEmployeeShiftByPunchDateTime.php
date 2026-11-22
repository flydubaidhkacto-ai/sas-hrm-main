<?php

namespace App\Actions\EmployeePunched;

use App\Models\Attendance;
use App\Models\RosterItem;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\ShiftScheduleItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class FindEmployeeShiftByPunchDateTime
{
    private ?Carbon $shift_start_at = null;
    private ?Carbon $shift_end_at = null;
    private ?Carbon $check_in_after = null;
    private ?Carbon $check_out_before = null;

    private bool $is_roster_day = false;
    private null|Shift $shift = null;
    private null|Collection $roster_items = null;
    protected $check_in_before_shift_time;

    public function __construct(public User $employee, public Carbon $punch_date_time, public ?Attendance $attendance = null)
    {
        $this->check_in_before_shift_time = intval(Setting::check_in_before_shift_time_config()
        ? Setting::check_in_before_shift_time()
        : 4) ?? 4;
    }
    protected function findShiftByDate(): void
    {
        if($this->employee?->shift?->is_24_hour_shift ?? false)
        {
            $this->shift = $this->employee->shift;
            $this->shift_start_at = $this->punch_date_time->copy()->startOfDay();
            $this->shift_end_at = $this->punch_date_time->copy()->endOfDay();
            $this->check_in_after = $this->shift_start_at->copy()->subHours($this->check_in_before_shift_time);
            $this->check_out_before = $this->shift_start_at->copy()->addDay()->subHours($this->check_in_before_shift_time);
            $this->is_roster_day = false;
            return;
        }
        $this->findRosterDay();
        if (!$this->is_roster_day) {
            $this->findShift();
        }
    }
    protected function findRosterDay(): void
    {
        foreach (
            [
                $this->punch_date_time->format('Y-m-d'),
                $this->punch_date_time->copy()->subDay()->format('Y-m-d'),
                $this->punch_date_time->copy()->addDay()->format('Y-m-d'),
            ] as $date
        ) {
            
            $items = RosterItem::query()
                ->whereHas('roster', function ($query) {
                    $query->where('is_active', true);
                })
                ->where('employee_id', $this->employee->id)
                ->where('date', $date)
                ->whereHas('shift', function ($query) {
                    $query->where('display_only', false);
                })
                ->whereHas('roster')
                ->get();

            $first_start_of_all_shift = null;
            $last_end_of_all_shift = null;
            foreach ($items as $item) {
                $__date = Carbon::parse($item->date);
                $start = Carbon::parse($__date->format('Y-m-d') . ' ' . $item->shift->start);
                $end = Carbon::parse($__date->format('Y-m-d') . ' ' . $item->shift->end);
                if ($start->greaterThan($end)) {
                    $end = $end->copy()->addDay();
                }
                if (!$first_start_of_all_shift || $start->lessThan($first_start_of_all_shift)) {
                    $first_start_of_all_shift = $start->copy();
                    // dump($start->format('Y-m-d H:i:s') . ' -- 1');
                }
                if (!$last_end_of_all_shift || $end->greaterThan($last_end_of_all_shift)) {
                    $last_end_of_all_shift = $end->copy();
                    // dump($end->format('Y-m-d H:i:s') . ' -- 2');
                }
            }
            if ($first_start_of_all_shift && $last_end_of_all_shift) {
                $check_in_after = $first_start_of_all_shift->copy()->subHours($this->check_in_before_shift_time);
                $check_out_before = $first_start_of_all_shift->copy()->addDay()->subHours($this->check_in_before_shift_time);
                if (
                    $check_in_after->lessThanOrEqualTo($this->punch_date_time) &&
                    $check_out_before->greaterThanOrEqualTo($this->punch_date_time)
                ) {
                    $this->is_roster_day = true;
                    $this->roster_items = $items;
                    $this->shift_start_at = $first_start_of_all_shift;
                    $this->shift_end_at = $last_end_of_all_shift;
                    // dd($this->shift_start_at, $this->shift_end_at);
                    $this->check_in_after = $check_in_after;
                    $this->check_out_before = $check_out_before;
                    return;
                }
            }
        }
    }
    protected function findShift(): void
    {
        $findScheduleShifts = ShiftScheduleItem::query()
            ->where('employee_id', $this->employee->id)
            ->whereIn('date', [
                $this->punch_date_time->format('Y-m-d'),
                $this->punch_date_time->copy()->subDay()->format('Y-m-d'),
                $this->punch_date_time->copy()->addDay()->format('Y-m-d'),
            ])
            ->whereHas('shift')
            ->get();
        foreach ($findScheduleShifts as $findScheduleShift) {
            if ($findScheduleShift->shift) {
                $shift = $findScheduleShift->shift;
                $start_time = Carbon::parse($findScheduleShift->date . ' ' . $shift->start);
                $end_time = Carbon::parse($findScheduleShift->date . ' ' . $shift->end);
                if ($start_time->greaterThan($end_time)) {
                    $end_time = $end_time->copy()->addDay();
                }
                $check_in_after = $start_time->copy()->subHours($this->check_in_before_shift_time);
                $check_out_before = $start_time->copy()->addDay()->subHours($this->check_in_before_shift_time);
                if (
                    $check_in_after->lessThanOrEqualTo($this->punch_date_time) &&
                    $check_out_before->greaterThanOrEqualTo($this->punch_date_time)
                ) {
                    $this->shift = $shift;
                    $this->shift_start_at = $start_time;
                    $this->shift_end_at = $end_time;
                    $this->check_in_after = $check_in_after;
                    $this->check_out_before = $check_out_before;
                    break;
                }
            }
        }
        if (!$this->shift) {
            if ($this->attendance && $this->attendance->shift) {
                $this->shift = $this->attendance->shift;
            }
            if (!$this->shift) {
                $this->shift = $this->employee->shift;
            }
            if (!$this->shift) return;

            // check for 3 dayes shift time range. today, yesterday, tomorrow
            foreach (
                [
                    $this->punch_date_time->format('Y-m-d'),
                    $this->punch_date_time->copy()->subDay()->format('Y-m-d'),
                    $this->punch_date_time->copy()->addDay()->format('Y-m-d')
                ] as $date
            ) {
                $start_time = Carbon::parse($date . ' ' . $this->shift->start);
                $end_time = Carbon::parse($date . ' ' . $this->shift->end);
                if ($start_time->greaterThan($end_time)) {
                    $end_time = $end_time->copy()->addDay();
                }
                $check_in_after = $start_time->copy()->subHours($this->check_in_before_shift_time);
                $check_out_before = $start_time->copy()->addDay()->subHours($this->check_in_before_shift_time);
                if (
                    $check_in_after->lessThanOrEqualTo($this->punch_date_time) &&
                    $check_out_before->greaterThanOrEqualTo($this->punch_date_time)
                ) {
                    $this->shift_start_at = $start_time;
                    $this->shift_end_at = $end_time;
                    $this->check_in_after = $check_in_after;
                    $this->check_out_before = $check_out_before;
                    break;
                }
            }
        }
    }
    /**
     * @return array
     */
    static function getData(User $employee, Carbon $punch_date_time, ?Attendance $attendance = null): array
    {
        $_this = new self($employee, $punch_date_time, $attendance);
        $_this->findShiftByDate();
        return [
            'date' => $_this->punch_date_time,
            'is_roster_day' => $_this->is_roster_day,
            'shift' => $_this->shift,
            'roster_items' => $_this->roster_items,
            'shift_start_at' => $_this->shift_start_at,
            'shift_end_at' => $_this->shift_end_at,
            'check_in_after' => $_this->check_in_after,
            'check_out_before' => $_this->check_out_before,
        ];
    }
}
