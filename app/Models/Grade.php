<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grade extends Model
{
    use HasFactory;
    protected $guarded = [];
    function payroll_cards()
    {
        return $this->hasMany(PayrollCard::class);
    }
    function items()
    {
        return $this->hasMany(GradeItem::class);
    }
}
