<?php

namespace App\Models;

use App\Traits\ApprovalProcessModal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    use HasFactory, ApprovalProcessModal;

    protected $approval_process_type = 'leave';
    protected $guarded = [];
    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'send_employee_sms' => 'boolean',
        'is_hourly' => 'boolean',
        'applied_dates' => 'array',
        'published_for_view' => 'boolean',
        'is_station_leave' => 'boolean',
    ];
    protected $appends = ['attachment_url'];

    // boot function
    protected static function booted()
    {
        static::created(function ($model) {
            $model->updateDuration();
        });
        static::updated(function ($model) {
            if($model->status === 'approved') {
                Fine::asp_delete_auto_absent_fines_if_present_later(
                    generateDateRange(
                        carbon($model->start_date),
                        carbon($model->end_date)
                    ),
                    $model->employee_id
                );
            }
        });
    }
    function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
    function leave_type()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
    function getAttachmentUrlAttribute()
    {
        return $this->attachment ? url('uploaded_files/' . $this->attachment) : null;
    }
    function updateDuration() {
        if($this->is_hourly) {
            $start = carbon($this->start_date->format('Y-m-d') . ' ' . $this->start_time);
            $end = carbon($this->end_date->format('Y-m-d') . ' ' . $this->end_time);
        } else {
            $start = $this->start_date;
            $end = $this->end_date;
        }
        $duration = self::getDurationDays($start, $end, $this->employee, $this->is_hourly);
        $this->duration_count = $duration['total'];
        $this->applied_dates = $duration['dates'];
        $this->save();
    }

    static function getDurationDays(Carbon $start, Carbon $end, User $user, bool $is_hourly = false)
    {
        $start_date = $start->copy();
        $end_date = $end->copy();
        $holidays = Holiday::where('date', '>=', $start_date)
            ->where('date', '<=', $end_date)
            ->pluck('date')
            ->toArray();
        $weekends = [];
        $shift = $user->shift ?: Shift::where('is_for_student', false)->first();
        if(!$shift) return ['dates' => [], 'total' => 0];
        if($is_hourly) {
            $diff = $start->diffInMinutes($end);
            $shiftDurationSeconds = $shift->duration;
            $shiftDurationMinutes = $shiftDurationSeconds > 0 ? $shiftDurationSeconds / 60 : 0;
            $leaveInDays = $shiftDurationMinutes > 0 ? $diff / $shiftDurationMinutes : 0;
            $leaveInDays = round($leaveInDays, 3);
            return ([
                'dates' => [$start_date->format('Y-m-d')],
                'total' => $leaveInDays,
                // 'offdays' => [],
                // 'weekends' => [],
                // 'holidays' => [],
            ]);
        }

        $all_dates = [];
        for ($i = $start_date; $i <= $end_date; $i->addDay()) {
            $all_dates[] = $i->format('Y-m-d');
            if ($shift && !$shift->{strtolower($i->format('l'))}) {
                $weekends[] = $i->format('Y-m-d');
            }
        }
        $offdays = array_merge($weekends, $holidays);
        $dates = array_diff($all_dates, $offdays);
        if(config('app.customer') === 'sas' || config('app.leave_duration_has_bridge') === false) {
            $dates = $all_dates;
        }
        return [
            'dates' => array_values($dates),
            'total' => count($dates),
            // 'offdays' => $offdays,
            // 'weekends' => $weekends,
            // 'holidays' => $holidays,
        ];
    }

    function prevApplication() {
        return LeaveApplication::query()
                ->where('id', '<', $this->id)
                ->where('status', 'approved')
                ->where('is_hourly', false)
                ->where('employee_id', $this->employee_id)
                ->first();
    }
}
