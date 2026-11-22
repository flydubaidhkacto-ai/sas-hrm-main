<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeReward extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'date' => 'datetime',
    ];

    function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    function added()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
