<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanPaymentScheduleForInterest extends Model
{
    protected $guarded = [];

    function loan()
    {
        return $this->belongsTo(Loan::class);
    }
    function loanPayment()
    {
        return $this->belongsTo(LoanPayment::class);
    }
}
