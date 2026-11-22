<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleService extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function vehicle(){
        return $this->belongsTo(Vehicle::class,'vehicle_id');
    }
    public function employee(){
        return $this->belongsTo(User::class,'responsible_person_id');
    }
}
