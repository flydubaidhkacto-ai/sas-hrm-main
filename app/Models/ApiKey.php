<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'expire_at' => 'datetime'
    ];
    function clients ()
    {
        return $this->hasMany(ApiClients::class);
    }
    function devices ()
    {
        return $this->hasMany(ZkDevice::class);
    }
}
