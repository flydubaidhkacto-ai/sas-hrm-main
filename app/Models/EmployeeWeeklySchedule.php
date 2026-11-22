<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeWeeklySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_id',
        'weekday',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the employee that owns the schedule.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee that owns the schedule.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the location for the schedule.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by weekday.
     */
    public function scopeForWeekday($query, $weekday)
    {
        return $query->where('weekday', $weekday);
    }

    /**
     * Scope to filter by active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get schedules for a specific user and weekday.
     */
    public static function getSchedulesForUserAndDay($userId, $weekday)
    {
        return static::forUser($userId)
            ->forWeekday($weekday)
            ->active()
            ->with('location')
            ->get();
    }

    /**
     * Get all weekdays as array.
     */
    public static function getWeekdays()
    {
        return [
            'sunday' => 'Sunday',
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
        ];
    }

    /**
     * Get formatted weekday name.
     */
    public function getFormattedWeekdayAttribute()
    {
        return ucfirst($this->weekday);
    }

    /**
     * Check if today is the scheduled weekday.
     */
    public function isToday()
    {
        return strtolower(now()->format('l')) === $this->weekday;
    }
}
