<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function newDesignation()
    {
        return $this->belongsTo(Designation::class, 'new_designation');
    }

    public function newDepartment()
    {
        return $this->belongsTo(Department::class, 'new_department');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_id');
    }

    public function first_approve()
    {
        return $this->belongsTo(User::class, 'first_approve_id');
    }

    public function second_approve()
    {
        return $this->belongsTo(User::class, 'second_approve_id');
    }
}
