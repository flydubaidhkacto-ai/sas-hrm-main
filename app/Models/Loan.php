<?php

namespace App\Models;

use App\Traits\ApprovalProcessModal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory, ApprovalProcessModal;

    protected $approval_process_type = 'loan';
    protected $guarded = [];

    function employee()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    function added_by()
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }
    function paid_amount_till_now()
    {
        return $this->hasMany(LoanPayment::class, 'loan_id');
    }
    function paymentSchedules()
    {
        return $this->hasMany(LoanPaymentScheduleForInterest::class, 'loan_id');
    }
}
