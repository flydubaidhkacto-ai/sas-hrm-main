<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftSchedule extends Model
{
    protected $guarded = [];
    public function items()
    {
        return $this->hasMany(ShiftScheduleItem::class);
    }
    public function formatted()
    {
        $data = [];
        foreach ($this->items as $item) {
            $data[$item->group]['employees'][] = $item->employee_id;
            $data[$item->group]['shifts'][$item->date] = $item->shift;
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
