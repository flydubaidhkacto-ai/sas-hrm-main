<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvidentFundPayment extends Model
{
    protected $guarded = [];

    function user()
    {
        return $this->belongsTo(User::class);
    }

    function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }
}
