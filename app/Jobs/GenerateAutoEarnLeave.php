<?php

namespace App\Jobs;

use App\Models\EarnLeaveHistory;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateAutoEarnLeave implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public ?Carbon $nowTime = null)
    {
        if(!$this->nowTime)
        {
            $this->nowTime = now();
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->amania_monthly_2();
        $this->from_weekly_attendance();
    }
    public function amania_monthly_2()
    {
        if(config('app.customer') !== 'amania') return;
        $date = carbon(now()->format('Y-m') . '-01');
        // change date to next month if salary period is broken
        if(
            config('services.have_broken_payslip_period')
            && is_numeric(config('services.have_broken_payslip_period'))
            && intval($date->format('d')) >= config('services.have_broken_payslip_period')
        ) {
            $date->addMonth();
        }
        $earnLeave = LeaveType::earn_leave();

        $history = EarnLeaveHistory::whereDate('created_at', $date->format('Y-m-d'))
            ->first();
        if(!$history)
        {
            User::active_employees()->chunk(100, function ($emps) use ($date, $earnLeave) {
                foreach ($emps as $emp) {
                    $currentBalance = LeaveBalance::employee_balance($emp->id, $earnLeave->id, $date->format('Y'))['limit'];
                    $newBal = $currentBalance + 2;

                    // insert balance
                    Setting::emp_leave_balance(
                        $emp->id,
                        $earnLeave->id,
                        $date->format('Y'),
                        $newBal
                    );
                    // add history
                    EarnLeaveHistory::create([
                        'user_id' => $emp->id,
                        'new_balance' => $newBal,
                        'old_balance' => $currentBalance,
                        'added' => 2,
                        'created_at' => $date,
                        'note' => 'Monthly 2 days earn leave - ' . $date->format('d F, Y')
                    ]);
                }
            });
        }
    }
    public function from_weekly_attendance()
    {
        $enabled_one_earn_leave_after_each_weekend = Setting::enabled_one_earn_leave_after_each_weekend();
        $one_earn_leave_after_each_weekend = Setting::one_earn_leave_after_each_weekend();
        $earnLeave = LeaveType::earn_leave();
        if(
            $enabled_one_earn_leave_after_each_weekend &&   $one_earn_leave_after_each_weekend
        )
        {
            //check today is weekday
            if(strtolower($this->nowTime->format('l')) !== strtolower($one_earn_leave_after_each_weekend))
            {
                return;
            }

            // check history
            $history = EarnLeaveHistory::whereDate('created_at', $this->nowTime->format('Y-m-d'))
                ->first();
            if(!$history)
            {
                User::active_employees()->chunk(100, function ($emps) use ($earnLeave, $one_earn_leave_after_each_weekend) {
                    foreach ($emps as $emp) {
                        $currentBalance = LeaveBalance::employee_balance($emp->id, $earnLeave->id, $this->nowTime->format('Y'))['limit'];
                        $newBal = $currentBalance + 1;

                        // insert balance
                        Setting::emp_leave_balance(
                            $emp->id,
                            $earnLeave->id,
                            $this->nowTime->format('Y'),
                            $newBal
                        );
                        // add history
                        EarnLeaveHistory::create([
                            'user_id' => $emp->id,
                            'new_balance' => $newBal,
                            'old_balance' => $currentBalance,
                            'added' => 1,
                            'created_at' => $this->nowTime,
                            'note' => 'Weekly Earn Leave of ' . $one_earn_leave_after_each_weekend . ' - ' . $this->nowTime->format('d F, Y')
                        ]);
                    }
                });
            }
        }
    }
}
