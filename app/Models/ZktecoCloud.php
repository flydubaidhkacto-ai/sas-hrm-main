<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZktecoCloud extends Model
{
    protected $guarded = [];
    protected $casts = [
        'data' => 'array',
    ];
    function commands()
    {
        return $this->hasMany(ZktecoCloudCommand::class);
    }
    function departments()
    {
        return $this->belongsToMany(Department::class);
    }
}
