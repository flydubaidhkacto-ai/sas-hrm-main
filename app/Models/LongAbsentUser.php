<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LongAbsentUser extends Model
{
    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'total_days',
        'dates',
    ];

    protected $casts = [
        'dates' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
