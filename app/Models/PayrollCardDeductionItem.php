<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollCardDeductionItem extends Model
{
    use HasFactory;
    protected $guarded = [];

    function payrollCard()
    {
        return $this->belongsTo(PayrollCard::class, 'payroll_card_id');
    }

}
