<?php

namespace App\Actions\GeneratePayslip;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

trait BasicBy30DaySalary
{
    /**
     * Cover items:
     * 1. Roster
     * 2. Leave
     * 3. Hourly Leave
     * 4. Attendance
     * 5. Weekend/Holiday
     */
    protected float $left_out_salary_after_available_working_days = 0;
    protected int $left_out_days = 0;
    protected int $grace_days = 0;
    protected array $weekend_dates = [];

    function basicBy30DaySalary()
    {
        if(config('app.customer') === 'asp') {
            return $this->basicBy30DaySalaryForAsp();
        }
        $this->dynamic_daily_salary = $this->main_basic_salary / 30;

        foreach ($this->dates as $date => $data) {
            if($data['attendance'] && !$data['is_weekend'] && !$data['holiday'])
            {
                if($this->paytype == 'daily') {
                    $this->earned_basic_salary += $this->dynamic_daily_salary;
                    $this->system_notes[] = [
                        'type' => 'earn',
                        'name' => $date . ': Daily Salary',
                        'description' => 'Daily Salary',
                        'value' => $this->dynamic_daily_salary,
                        'is_incremental' => true,
                        'balance' => $this->earned_basic_salary,
                    ];
                }
                elseif($this->paytype == 'hourly') {
                    $this->hourly_earn_by_hourly_from_attendances(
                        $this->dynamic_daily_salary,
                        $date,
                        $data
                    );
                }
            }
            else if(
                $data['is_weekend']
            )
            {
                $this->weekend_dates[] = $date;
            }
        }
        $this->get_left_out_salary_after_available_working_days();
        $this->add_grace_days_salary();
    }

    function get_left_out_salary_after_available_working_days()
    {
        foreach($this->grouped_paid_holidays_leaves_weekends as $dates)
        {
            if(count($dates) > 0)
            {
                $startDate = Carbon::parse($dates[0]);
                $endDate = Carbon::parse($dates[count($dates) - 1]);
                $is_eligible = false;
                // start date of the month
                if($startDate->isSameDay($this->from_date))
                {
                    if(
                        $this->dates[$endDate->copy()->addDay()->format('Y-m-d')]['attendance'] ?? null
                    )
                    {
                        $is_eligible = true;
                    }
                }else
                // end date of the month
                if($endDate->isSameDay($this->end_date))
                {
                    if(
                        $this->dates[$startDate->copy()->subDay()->format('Y-m-d')]['attendance'] ?? null
                    )
                    {
                        $is_eligible = true;
                    }
                }
                else {
                    // in the middle of the month
                    if(
                        ($this->dates[$startDate->copy()->subDay()->format('Y-m-d')]['attendance'] ?? null)
                        && ($this->dates[$endDate->copy()->addDay()->format('Y-m-d')]['attendance'] ?? null)
                    )
                    {
                        $is_eligible = true;
                    }
                }
                if(!$is_eligible) continue;

                foreach($dates as $i => $date)
                {
                    if($this->dates[$date]['holiday'])
                    {
                        $this->earned_basic_salary += $this->dynamic_daily_salary;
                        $this->system_notes[] = [
                            'type' => 'earn',
                            'name' => $date . ': Holiday',
                            'description' => 'Holiday: ' . $date,
                            'value' => $this->dynamic_daily_salary,
                            'is_incremental' => true,
                            'balance' => $this->earned_basic_salary,
                        ];
                    }
                    else if($this->dates[$date]['leave'])
                    {
                        $this->earned_basic_salary += $this->dynamic_daily_salary;
                        $this->system_notes[] = [
                            'type' => 'earn',
                            'name' => $date . ': Paid Leave',
                            'description' => 'Paid Leave: [' . $this->dates[$date]['leave']->leave_type?->name . ']',
                            'value' => $this->dynamic_daily_salary,
                            'is_incremental' => true,
                            'balance' => $this->earned_basic_salary,
                        ];
                    }
                }
            }
        }
    }
    function add_grace_days_salary()
    {
        $this->grace_days = 30 - count($this->without_weekend_dates);
        if($this->grace_days < 0) return;
        $per_day_salary = $this->dynamic_daily_salary;
        $left_money = $per_day_salary * $this->grace_days;
        $att_count = $this->emplyee_raw_worked_days + (
            $this->emplyee_paid_leave_days ? $this->emplyee_paid_leave_days : 0
        ) + (
            $this->emplyee_unpaid_leave_days ? $this->emplyee_unpaid_leave_days : 0
        );

        $max_working_days = $this->available_working_days;
        if($att_count > $max_working_days) $att_count = $max_working_days;

        $present_percentage = $att_count / $max_working_days * 100;
        // if($present_percentage > 100) $present_percentage = 100;
        $avg_percentage = 100 / $this->grace_days;
        $amount = 0;
        if($present_percentage >= 99) $amount = $left_money;
        else {
            $increasing_percentage = 0;
            for($i = 0; $i < $this->grace_days; $i++)
            {
                $increasing_percentage += $avg_percentage;
                if($increasing_percentage >= $present_percentage) break;
                else $amount = round($left_money * ($increasing_percentage / 100));
            }
        }
        if($amount > 0) {
            $this->earned_basic_salary += $amount;
            $this->system_notes[] = [
                'type' => 'earn',
                'name' => 'Grace Days',
                'description' => 'Grace Days: ' . $this->grace_days . ' days with ' . round($present_percentage, 2) . '% attendance' . (
                    $this->emplyee_paid_leave_days ? ' (+' . $this->emplyee_paid_leave_days . ' days paid leave)' : ''
                ),
                'value' => $amount,
                'is_incremental' => true,
                'balance' => $this->earned_basic_salary,
            ];
        }

    }
    function basicBy30DaySalaryForAsp()
    {
        $this->dynamic_daily_salary = $this->main_basic_salary / 30;
        $this->earned_basic_salary = $this->dynamic_daily_salary * 30;
        $this->system_notes[] = [
            'type' => 'earn',
            'name' => 'Basic Salary',
            'description' => 'Basic Salary',
            'value' => $this->earned_basic_salary,
            'is_incremental' => true,
            'balance' => $this->earned_basic_salary,
        ];

        foreach ($this->dates as $date => $data) {
            // keep minus salary
            if($data['attendance'])
            {
                if($this->paytype == 'hourly') {
                    $this->earned_basic_salary -= $this->dynamic_daily_salary;
                    $this->hourly_earn_by_hourly_from_attendances(
                        $this->dynamic_daily_salary,
                        $date,
                        $data
                    );
                }
            }
            if(
                !$data['attendance'] && !$data['is_weekend'] && !$data['holiday']
                && (!$data['leave'] || !$data['leave']->leave_type?->is_paid)
            )
            {
                if($this->paytype == 'daily')
                {
                    $this->earned_basic_salary -= $this->dynamic_daily_salary;
                    $this->system_notes[] = [
                        'type' => 'deduct',
                        'name' => $date . '',
                        'description' => $data['leave'] ? 'Unpaid Leave' : 'Absent',
                        'value' => $this->dynamic_daily_salary,
                        'is_incremental' => false,
                        'balance' => $this->earned_basic_salary,
                    ];
                }
            }
        }

        // exclude weekends holidays and paid leave
        foreach($this->grouped_paid_holidays_leaves_weekends as $dates)
        {
            if(count($dates) > 0)
            {
                $is_eligible = $this->is_eligible_for_holiday_weekend($dates[0]);
                if($is_eligible) continue;
                foreach($dates as $i => $date)
                {
                    if($this->dates[$date]['holiday'] && !$this->dates[$date]['attendance'])
                    {
                        $this->earned_basic_salary -= $this->dynamic_daily_salary;
                        $this->system_notes[] = [
                            'type' => 'deduct',
                            'name' => $date . ': Holiday',
                            'description' => 'Holiday: ' . $date,
                            'value' => $this->dynamic_daily_salary,
                            'is_incremental' => false,
                            'balance' => $this->earned_basic_salary,
                        ];
                    }
                    else if($this->dates[$date]['leave'] && !$this->dates[$date]['leave']->leave_type?->is_paid)
                    {
                        $this->earned_basic_salary -= $this->dynamic_daily_salary;
                        $this->system_notes[] = [
                            'type' => 'deduct',
                            'name' => $date . ': Paid Leave',
                            'description' => 'Paid Leave: [' . $this->dates[$date]['leave']->leave_type?->name . ']',
                            'value' => $this->dynamic_daily_salary,
                            'is_incremental' => false,
                            'balance' => $this->earned_basic_salary,
                        ];
                    }
                    else if($this->dates[$date]['is_weekend'] && !$this->dates[$date]['attendance'])
                    {
                        $this->earned_basic_salary -= $this->dynamic_daily_salary;
                        $this->system_notes[] = [
                            'type' => 'deduct',
                            'name' => $date . ': Weekend',
                            'description' => 'Weekend',
                            'value' => $this->dynamic_daily_salary,
                            'is_incremental' => false,
                            'balance' => $this->earned_basic_salary,
                        ];
                    }
                }
            }
        }
        if($this->earned_basic_salary < 0) $this->earned_basic_salary = 0;
    }
}
