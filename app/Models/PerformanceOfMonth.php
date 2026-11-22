<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceOfMonth extends Model
{
    use HasFactory;
    protected $guarded = [];

    function days()
    {
        return $this->hasMany(DaysPerformanceMonth::class, 'performance_of_month_id', 'id');
    }
    function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
