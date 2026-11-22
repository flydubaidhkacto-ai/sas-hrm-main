<?php

namespace App\Models;

use App\Actions\EmployeePunched;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InOutRecord extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'date_time' => 'datetime:Y-m-d H:i:s',
        'added' => 'boolean',
        'location' => 'string',
    ];
    protected $appends = [
        'punch_type',
    ];

    function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    function used_in_attendance()
    {
        return $this->belongsToMany(Attendance::class, 'attendances_in_out_records');
    }
    function cloud_device()
    {
        return $this->belongsTo(ZktecoCloud::class, 'device_uid', 'device_sn');
    }
    function device()
    {
        return $this->belongsTo(ZkDevice::class, 'device_uid', 'uuid');
    }

    protected static function booted()
    {
        static::created(function ($model) {
            //\App\Actions\EmployeePunched\Execute::run($model);
        });

        static::updating(function ($model) {
            // dd($model);
            // throw new \Exception('Error Processing Request 2');
        });
        static::updated(function ($model) {
            // dd($model);
            // throw new \Exception('Error Processing Request');
        });

        static::deleted(function ($model) {
            // Event logic for when a record is deleted
        });
    }
    function getPunchTypeAttribute()
    {
        if ($this->status == 1) {
            return "<span class='text-success'>" . __('Check In') . "</span>";
        } else
        if ($this->status == 0) {
            return "<span class='text-warning'>" . __('Check Out') . "</span>";
        }

        return "<span class='text-success'>" . __('Punch') . "</span>";
    }
}
