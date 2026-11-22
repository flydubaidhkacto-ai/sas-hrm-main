<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleRefuel extends Model
{
    protected $guarded = [];
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
