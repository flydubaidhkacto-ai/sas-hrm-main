<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PayslipPayment extends Model
{
    protected $guarded = [];

    static function boot()
    {
        parent::boot();
        static::created(function ($model) {
            $payslip = $model->payslip();
            $amount = round($payslip->total_salary - $model->paid_amount, 2);
            $model->user->increment('payroll_balance', $amount);
            $model->update([
                'new_balance' => $model->user->payroll_balance
            ]);
        });
        static::deleted(function ($model) {
            $payslip = $model->payslip();
            $amount = round($payslip->total_salary - $model->paid_amount, 2);
            $model->user->decrement('payroll_balance', $amount);
            $model->update([
                'new_balance' => $model->user->payroll_balance
            ]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payslip()
    {
        return Payslip::where('month_date', $this->payslip_date)
            ->where('user_id', $this->user_id)
            ->first();
    }
}
