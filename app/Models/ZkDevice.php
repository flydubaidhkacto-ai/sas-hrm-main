<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZkDevice extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'department_ids' => 'array',
    ];
    function departments()
    {
        if(!$this->department_ids) return null;
        return Department::whereIn('id', $this->department_ids);
    }
}
