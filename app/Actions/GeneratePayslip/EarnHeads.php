<?php

namespace App\Actions\GeneratePayslip;

use App\Models\DutyBonusRecord;
use App\Models\EmpDailyExpense;
use App\Models\EmployeeReward;
use App\Models\LeaveApplication;
use App\Models\ProvidentFundPayment;
use App\Models\Setting;
use Carbon\Carbon;

trait EarnHeads
{
    function hourly_earn_by_hourly_from_attendances($dailyRate, $date, $data)
    {
        $max_seconds = $this->dates[$date]['max_working_seconds'];

        if (!$data['attendance'] || $max_seconds <= 0) {
            return null; // aabsent
        }
        $salry_per_seconds = $dailyRate / $max_seconds;
        $working_time = time_to_seconds($data['attendance']->working_time);
        // check if athere any paid leave
        $paid_leave_seonds = 0;
        $paid_hourly_leaves = LeaveApplication::final_approved()
            ->where('employee_id', $this->user->id)
            ->whereDate('start_date', $date)
            ->where('is_hourly', true)
            ->whereHas('leave_type', function($query) {
                $query->where('is_paid', true);
            })
            ->get();
        foreach($paid_hourly_leaves as $leave) {
            $paid_leave_seonds += round(
                Carbon::parse($leave->start_time)->diffInSeconds(
                    Carbon::parse($leave->end_time)
                )
            );
        }

        $just_working_time = $working_time + 0;
        $working_time += $paid_leave_seonds;
        $amount = $working_time * $salry_per_seconds;
        $this->earned_basic_salary += $amount;
        $this->system_notes[] = [
            'type' => 'earn',
            'name' => $date . ': Hourly Salary',
            'description' => 'Hourly Salary: ' . seconds_to_hh_mm_ss($just_working_time) . (
                $paid_leave_seonds > 0 ? ' (Hourly Leave: ' . seconds_to_hh_mm_ss($paid_leave_seonds) . ')' : ''
            ) . ' (Rate: ' . number_format($salry_per_seconds * 60 * 60, 2, '.', '') . " Tk)",
            'value' => $amount,
            'is_incremental' => true,
            'balance' => $this->earned_basic_salary,
        ];
    }

    protected float $provident_fund_amount = 0;
    function add_earn_heads()
    {
        foreach ($this->earn_head_items as $i => $item) {
            if ($item['category'] === 'basic') {
                $this->earn_head_items[$i]['total_amount'] = round($this->earned_basic_salary, 2);
            }
            if ($item['category'] === 'overtime') {
                $total_overtimed_salary = 0;
                foreach ($this->dates as $tmp_date => $data) {
                    if (
                        ($data['attendance'] ?? false)
                        && ($data['attendance']->overtime_time ?? false)
                    ) {
                        $salary_per_seconds = $data['max_working_seconds'] ? $this->dynamic_daily_salary / $data['max_working_seconds'] : 0;

                        $overtimed_seconds = time_to_seconds($data['attendance']->overtime_time ?? '00:00:00');
                        if($overtimed_seconds > 0) {
                            // full salary for daily
                            if($this->paytype === 'daily')
                            {
                                $tmp_amount = 0;
                                if($salary_per_seconds) {
                                    if ($item['amount_type'] === 'fixed') {
                                        $tmp_amount = $item['amount'];
                                    } else if ($item['amount_type'] === 'percentage') {
                                        // percentage
                                        $tmp_amount = $item['amount'] / 100 * ($this->dynamic_daily_salary);
                                    } else {
                                        $tmp_amount = $item['amount'] * ($this->dynamic_daily_salary);
                                    }
                                }
                                $total_overtimed_salary += $tmp_amount;
                                $tmp_amount_per_seconds = $this->dynamic_daily_salary / (($data['max_working_seconds'] ?? 0) ?: 3600);
                            }else {
                                $tmp_amount_per_seconds = 0;
                                if($salary_per_seconds) {
                                    if ($item['amount_type'] === 'fixed') {
                                        $tmp_amount_per_seconds = $item['amount'] / 60 / 60;
                                    } else if ($item['amount_type'] === 'percentage') {
                                        // percentage
                                        $tmp_amount_per_seconds = $item['amount'] / 100 * ($salary_per_seconds ?? 0);
                                    } else {
                                        $tmp_amount_per_seconds = $item['amount'] * ($salary_per_seconds ?? 0);
                                    }
                                }
                                $total_overtimed_salary += $overtimed_seconds * $tmp_amount_per_seconds;
                            }

                            $this->system_notes[] = [
                                'type' => 'info',
                                'name' => $tmp_date . ' :Overtime',
                                'description' => 'Overtime hour: ' . seconds_to_hh_mm_ss($overtimed_seconds) . ' (Rate: ' . number_format($tmp_amount_per_seconds * 60 * 60, 2, '.', '') . " Tk)",
                                'value' => 0,
                                'is_incremental' => null,
                                'balance' => $this->earned_total_salary,
                            ];
                        }
                    }
                }
                $this->earned_total_salary += $total_overtimed_salary;
                $this->earned_earn_heads += $total_overtimed_salary;
                $this->earn_head_items[$i]['total_amount'] = round($total_overtimed_salary, 2);
                $this->system_notes[] = [
                    'type' => 'earn',
                    'name' => 'Overtime Earned',
                    'description' => 'Overtime Earned: ' . round($total_overtimed_salary, 2) . " Tk",
                    'value' => $total_overtimed_salary,
                    'is_incremental' => true,
                    'balance' => $this->earned_total_salary,
                ];
            }
            if ($item['category'] === 'emp_daily_expenses') {
                $expenses = EmpDailyExpense::final_approved()
                    ->where('employee_id', $this->user->id)
                    ->whereDate('date', '>=', $this->from_date->format('Y-m-d'))
                    ->whereDate('date', '<=', $this->end_date->format('Y-m-d'))
                    ->get();
                if ($expenses->count()) {
                    $expense_total = 0;
                    foreach ($expenses as $expense) {
                        $expense_total += $expense->amount;
                    }
                    $this->earned_earn_heads += $expense_total;
                    $this->earned_total_salary += $expense_total;
                    $this->earn_head_items[$i]['total_amount'] = number_format($expense_total, 2, '.', '');
                    $this->system_notes[] = [
                        'type' => 'earn',
                        'name' => 'Employee Daily Expenses',
                        'description' => 'Employee Daily Expenses: ' . number_format($expense_total, 2, '.', '') . " Tk",
                        'value' => $expense_total,
                        'is_incremental' => true,
                        'balance' => $this->earned_total_salary,
                    ];
                }
            }
            if ($item['category'] === 'duty_bonuses') {
                // no leave, no overtime, no holiday or weekend
                $raw_total_worked_days = 0;
                // no unpaid leave, no overtime, no holiday or weekend
                $raw_total_worked_days_with_paid_leave = 0;
                foreach ($this->dates as $date => $data) {
                    if(!$data['is_weekend'] && !$data['holiday'] && !$data['leave'] && $data['attendance']) {
                        $raw_total_worked_days++;
                    }
                    if(
                        !$data['is_weekend'] && !$data['holiday'] && (
                            (
                                $data['leave'] && $data['leave']->type == "Medical Leave"
                            ) || $data['attendance']
                        )
                    ) {
                        $raw_total_worked_days_with_paid_leave++;
                    }
                }
                //1. 1000 th fixed  - att + medical
                $canHave1000TkBonus = $raw_total_worked_days_with_paid_leave >= $this->available_working_days;

                if($canHave1000TkBonus) {
                    $this->earned_earn_heads += 1000;
                    $this->earned_total_salary += 1000;
                    $this->earn_head_items[$i]['total_amount'] = number_format(1000, 2, '.', '');
                    $this->earn_head_items[$i]['name'] .= '(+1000 Tk)';
                    $this->system_notes[] = [
                        'type' => 'earn',
                        'name' => 'Duty Bonus',
                        'description' => '1k Duty Bonus: ' . number_format(1000, 2, '.', '') . " Tk",
                        'value' => 1000,
                        'is_incremental' => true,
                        'balance' => $this->earned_total_salary,
                    ];
                }

                //2. 1 day salary - ignore if there is medical
                if(
                    $raw_total_worked_days >= $this->available_working_days
                )
                {
                    $this->earned_earn_heads += $this->dynamic_daily_salary;
                    $this->earned_total_salary += $this->dynamic_daily_salary;
                    $this->earn_head_items[$i]['total_amount'] = number_format($this->dynamic_daily_salary, 2, '.', '');
                    $this->system_notes[] = [
                        'type' => 'earn',
                        'name' => 'Duty Bonus',
                        'description' => '1 day salary Duty Bonus: ' . number_format($this->dynamic_daily_salary, 2, '.', '') . " Tk",
                        'value' => $this->dynamic_daily_salary,
                        'is_incremental' => true,
                        'balance' => $this->earned_total_salary,
                    ];
                }

            }
            if ($item['category'] === 'allowance' || $item['category'] === 'other') {
                $tmp = 0;
                if ($item['amount_type'] === 'fixed') {
                    $tmp = $item['amount'];
                } else if ($item['amount_type'] === 'percentage') {
                    // percentage
                    $tmp = $item['amount'] / 100 * $this->main_gross_salary;
                } else {
                    $tmp = $item['amount'] * $this->main_basic_salary;
                }
                $this->earned_total_salary += ($tmp);
                $this->earned_earn_heads += ($tmp);
                $this->earn_head_items[$i]['total_amount'] = number_format($tmp, 2, '.', '');
                $this->system_notes[] = [
                    'type' => 'earn',
                    'name' => 'Earch Head',
                    'description' => 'Earch Head: ' . number_format($tmp, 2, '.', '') . " Tk",
                    'value' => $tmp,
                    'is_incremental' => true,
                    'balance' => $this->earned_total_salary,
                ];
            }
            if ($item['category'] === 'rewards') {
                # Step 6: Add Rewards
                $rewards = EmployeeReward::query()
                    ->where('user_id', $this->user->id)
                    ->whereDate('date', '>=', $this->from_date->format('Y-m-d'))
                    ->whereDate('date', '<=', $this->end_date->format('Y-m-d'))
                    ->get();
                $rewards_amount = 0;
                $refs = [];
                foreach ($rewards as $reward) {
                    $refs[] = $reward->id;
                    $rewards_amount += $reward->amount;
                }
                if ($rewards_amount) {
                    $this->earned_total_salary += $rewards_amount;
                    $this->earned_earn_heads += $rewards_amount;
                    $this->earn_head_items[$i]['total_amount'] = number_format($rewards_amount, 2, '.', '');
                    $this->system_notes[] = [
                        'type' => 'earn',
                        'name' => 'Rewards',
                        'description' => 'Rewards: ' . number_format($rewards_amount, 2, '.', '') . " Tk (Ref: " . implode(", ", $refs) . ")",
                        'value' => $rewards_amount,
                        'is_incremental' => true,
                        'balance' => $this->earned_total_salary,
                    ];
                }
            }
        }
    }
    protected function generateProvidentFundPayment()
    {
        // create provident fund records
        if ($this->provident_fund_amount !== null) {
            $lastFund = ProvidentFundPayment::query()
                ->where('user_id', $this->user->id)
                ->orderBy('month_date', 'desc')
                ->first();
            $sumOfPreviousFundInterests = ProvidentFundPayment::query()
                ->where('user_id', $this->user->id)
                ->sum('interest_amount');
            $initial_pf_balance = $this->user->initial_pf_balance;
            $lastFundBalance = $lastFund ? $lastFund->balance : $initial_pf_balance;
            $last_balance_with_interest = $lastFund ? $lastFund->balance_with_interest : (
                $initial_pf_balance
            );
            $newBalance = $this->provident_fund_amount + $lastFundBalance;

            if (Setting::provident_fund_have_interests()) {
                $rate = Setting::provident_fund_interest_rate();
                if ($rate == 0 || $rate < 0) $interest_amount = 0;
                else {
                    $closing_fund_balance = $last_balance_with_interest;
                    $interest_amount = ($closing_fund_balance * $rate / 12 / 100);
                }

                return ProvidentFundPayment::query()->create([
                    'user_id' => $this->user->id,
                    'payslip_id' => $this->payslip->id,
                    'basic_salary' => $this->basic_salary_for_percentage,
                    'balance' => $newBalance,
                    'interest_amount' => $interest_amount,
                    'interest' => $rate,
                    'balance_with_interest' => round($interest_amount + $sumOfPreviousFundInterests + $newBalance, 2),
                    'month_date' => $this->payslip->month_date,
                ]);
            } else {
                return ProvidentFundPayment::query()->create([
                    'user_id' => $this->user->id,
                    'payslip_id' => $this->payslip->id,
                    'basic_salary' => $this->basic_salary_for_percentage,
                    'balance' => $newBalance,
                    'interest_amount' => 0,
                    'interest' => 0,
                    'balance_with_interest' => round($newBalance, 2),
                    'month_date' => $this->payslip->month_date,
                ]);
            }
        }
    }
}
