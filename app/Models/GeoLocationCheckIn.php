<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeoLocationCheckIn extends Model
{
    protected $fillable = [
        'user_id',
        'location_id',
        'location_name',
        'latitude',
        'longitude',
        'check_at',
        'note'
    ];
    protected $casts = [
        'check_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
