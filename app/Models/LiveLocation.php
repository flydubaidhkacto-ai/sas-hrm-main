<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LiveLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'ui_id',
        'device_name',
        'platform',
        'date_time',
        'status'
    ];
    function location_employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
