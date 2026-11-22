<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiClients extends Model
{
    use HasFactory;
    protected $guarded = [];

    function departments()
    {
        return $this->hasMany(ApiClientDepartment::class, 'api_client_id');
    }
}
