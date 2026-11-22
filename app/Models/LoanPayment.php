<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanPayment extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        self::deleting(function ($loanPayment) {
            $loanPayment->schedulePayment()
                ->update(['loan_payment_id' => null]);
        });
    }
    function loan()
    {
        return $this->belongsTo(Loan::class);
    }
    function user()
    {
        return $this->belongsTo(User::class);
    }
    function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }
    function schedulePayment()
    {
        return $this->hasOne(LoanPaymentScheduleForInterest::class);
    }
}
