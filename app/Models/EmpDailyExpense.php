<?php

namespace App\Models;

use App\Traits\ApprovalProcessModal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpDailyExpense extends Model
{
    use HasFactory, ApprovalProcessModal;

    protected $approval_process_type = 'emp_daily_expense';

    protected $guarded = [];

    function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
    function payslip()
    {
        return $this->belongsTo(Payslip::class, 'paid_payslip_id');
    }

    public function rel_to_expensed_category()
    {
        return $this->belongsTo(EmpDailyExpenseCategory::class, 'expensed_category_id');
    }
}
