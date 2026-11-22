<?php

namespace App\Jobs;

use App\Models\Payslip;
use App\Models\PayslipPayment;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateEmployeePayslipBalance implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public $ids = null)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        User::active_employees()
            ->when($this->ids, function ($query) {
                $query->whereIn('id', $this->ids);
            })
            ->chunk(50, function ($employees) {
                foreach ($employees as $employee) {
                    $totalGotPaid = PayslipPayment::where('user_id', $employee->id)
                        ->when(config('app.customer') === 'asp', function($q) {
                            $q->where('payslip_date', '>', '2025-06-01');
                        })
                        ->sum('paid_amount');
                    $totalSalary = Payslip::where('user_id', $employee->id)
                        ->when(config('app.customer') === 'asp', function($q) {
                            $q->where('month_date', '>', '2025-06-01');
                        })
                        ->sum('total_salary');
                    $employee->payroll_balance = round($totalSalary - $totalGotPaid, 2);
                    $employee->save();
                }
            });
    }
}
