<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [

    ];
    protected $casts = [
        'can_overtime' => 'boolean',
        'sunday' => 'boolean',
        'monday' => 'boolean',
        'tuesday' => 'boolean',
        'wednesday' => 'boolean',
        'thursday' => 'boolean',
        'friday' => 'boolean',
        'saturday' => 'boolean',
        'is_for_student' => 'boolean',
        'track_break_by_checkout' => 'boolean',
    ];
    protected $appends = [
        'is_night_shift',
    ];

    // global scope
    protected static function booted()
    {
        static::addGlobalScope('24_hours_shift', function ($builder) {
            if(config('services.have_24_shift') === false)
                $builder->where('is_24_hour_shift', false);
        });
    }

    function employees()
    {
        return $this->hasMany(User::class, 'shift_id', 'id');
    }

    public function student_shift()
    {
        return $this->hasMany(StudentShift::class, 'shift_id');
    }
    function getIsNightShiftAttribute()
    {
        return carbon($this->start)->greaterThan(carbon($this->end));
    }

    static function initFirst()
    {
        if(self::count() === 0)
        {
            return Shift::create([
                'name' => "General",
                'start' => "09:00:00",
                'end' => "17:00:00",
                'can_overtime' => true,
                'sunday' => true,
                'monday' => true,
                'tuesday' => true,
                'wednesday' => true,
                'thursday' => true,
                'friday' => false,
                'saturday' => true,
            ]);
        }
        return Shift::first();
    }
    function getDurationAttribute()
    {
        $start = carbon($this->start);
        $end = carbon($this->end);
        if($this->is_night_shift)
        {
            $end->addDay();
        }
        $minutes = $start->diffInMinutes($end);
        $minutes -= $this->break_time_minutes ?: 0;
        return $minutes * 60;
    }
}
