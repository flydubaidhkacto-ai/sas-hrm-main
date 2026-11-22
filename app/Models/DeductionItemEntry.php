<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeductionItemEntry extends Model
{
    protected $guarded = [];

    function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
