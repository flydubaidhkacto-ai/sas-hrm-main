<?php

namespace App\Jobs;

use App\Models\AutoReward;
use App\Models\DutyBonus;
use App\Models\EmployeeReward;
use App\Models\PayrollCard;
use App\Models\User;
use App\Traits\GenerateTotalDutyOfEmployee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class CheckEmployeeReward implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, GenerateTotalDutyOfEmployee;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::now();
        $firstDate = $now->copy()->startOfMonth();
        $lastDate = $now->copy()->endOfMonth();

        $employees = User::active_employees()
            ->whereHas('payroll_card')
            ->get();
        $dutyBonuses = DutyBonus::query()
            ->get();
        foreach ($dutyBonuses as $dutyBonus) {
            // $attendanceRecords = [];
            // $autoRewardType = AutoReward::first()?->type ?? 0;
            // $autoRewardAmount = AutoReward::first()?->amount ?? 0;
            // if (!$autoRewardType || !$autoRewardAmount) {
            //     return;
            // }
            // foreach ($employees as $employee) {

            //     $attendanceRecords = $this->generate_total_duty_of_employee($employee, $firstDate, $lastDate);
            //     $any_early_checkout = false;
            //     $any_late = false;

            //     // find if any early checkout or late exists
            //     foreach ($attendanceRecords['data'] as $timestamp => $record) {
            //         if($record['attendance'] ?? null) {
            //             if ($record['attendance']->did_early_check_out) {
            //                 $any_early_checkout = true;
            //                 break;
            //             }
            //             if ($record['attendance']->got_late_check_in) {
            //                 $any_late = true;
            //                 break;
            //             }
            //         }
            //     }

            //     if ($attendanceRecords['total_absent_days'] == 0 && $attendanceRecords['total_leave_days'] == 0)
            //     {
            //         if (!$any_early_checkout && !$any_late)
            //         {
            //             $basicSalary = $employee->payroll_card->basic_salary;

            //             $amount = 0;
            //             if ($autoRewardType == 0) {
            //                 $amount = $basicSalary + ($basicSalary * $autoRewardAmount) / 100;
            //             } else {
            //                 $amount = $basicSalary + $autoRewardAmount;
            //             }

            //             $amount = round($amount, 4);

            //             $empRew = [
            //                 'user_id' => $employee->id,
            //                 'name' => 'Auto Reward of the month',
            //                 'amount' => $amount,
            //                 'added_by' => User::first()->id,
            //                 'date' => $now->format('Y-m-d H:i:s'),
            //             ];
            //             EmployeeReward::create($empRew);
            //         }
            //     }
            // }
        }
    }
}
