<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllowedPunchRegion extends Model
{
    protected  $guarded = [];

    static function isLocationWithin($latitude, $longitude): bool
    {
        foreach (self::all() as $allowedPunchRegion) {
            if(isLocationWithinRadius(
                $latitude, $longitude, $allowedPunchRegion->latitude,
                $allowedPunchRegion->longitude, $allowedPunchRegion->radius_in_meters
            )['result'] === true) {
                return true;
            }
        }
        return false;
    }
}
