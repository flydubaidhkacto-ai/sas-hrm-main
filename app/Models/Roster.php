<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    protected $guarded = [];
    protected $casts = [
    'no_shift_duty' => 'array',
    'wfh_assignments' => 'array',
    'is_active' => 'boolean'
];

    static function active()
    {
        return self::query()->where('is_active', true);
    }
    static function draft()
    {
        return self::query()->where('is_active', false);
    }
    public function items()
    {
        return $this->hasMany(RosterItem::class);
    }

    public function superDepartment()
    {
        return $this->belongsTo(SuperDepartment::class);
    }

    public function wfhAttendances()
    {
        return $this->hasMany(Attendance::class)->where('duty_type', 'Work From Home');
    }

    public function hasWfhAttendance(int $employeeId, string $date, int $shiftId): bool
    {
        return $this->is_active && $this->wfhAttendances->contains(fn ($attendance) =>
            (int) $attendance->employee_id === $employeeId
            && carbon($attendance->date)->format('Y-m-d') === $date
            && (int) $attendance->roster_shift_id === $shiftId
        );
    }

    public function formatted()
    {
        $data = [];
        $group_item_ids = [];

        // ✅ NEW: order by group + order
        $items = $this->items()->orderBy('group')->orderBy('order')->get();

        foreach ($items as $item) {

            $date = carbon($item->date)->format('Y-m-d');

            if (!isset($data[$item->group])) {
                $data[$item->group]['employees'] = [];
                $data[$item->group]['shifts'] = [];
            }

            if (!in_array($item->employee_id, $data[$item->group]['employees'])) {
                $data[$item->group]['employees'][] = $item->employee_id;
            }

            $key = $item->group . '-' . $date . '-' . $item->roster_shift_id;

            if (!in_array($key, $group_item_ids)) {
                $data[$item->group]['shifts'][$date][] = $item->shift;
                $group_item_ids[] = $key;
            }
        }

        ksort($data);

        foreach ($data as $group => $item) {
            $data[$group]['employees'] = array_unique($data[$group]['employees']);
            ksort($data[$group]['employees']);
            ksort($data[$group]['shifts']);
        }

        return $data;
    }
}
