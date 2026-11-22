<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiClientDepartment extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    function client()
    {
        return $this->belongsTo(ApiClients::class, 'api_client_id', 'id');
    }
    function department()
    {
        return $this->belongsTo(SuperDepartment::class, 'department_id', 'id');
    }
    function super_department()
    {
        return $this->belongsTo(SuperDepartment::class, 'super_department_id', 'id');
    }
}
