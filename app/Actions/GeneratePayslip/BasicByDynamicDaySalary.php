<?php

namespace App\Actions\GeneratePayslip;

use App\Models\LeaveApplication;
use Carbon\Carbon;

trait BasicByDynamicDaySalary
{
    /**
     * Cover items:
     * 1. Roster
     * 2. Leave
     * 3. Hourly Leave
     * 4. Attendance
     * 5. Weekend/Holiday
     */
    function basicByDynamicDaySalary()
    {
        $total_holidays = collect($this->dates)
            ->filter(fn($data) => $data['holiday'])
            ->count();
        // $this->dynamic_daily_salary = $this->payroll_card->basic_salary / (($this->available_working_days ?: 30) + $total_holidays);
        if(config('services.dynamic_daily_salary_by_days')) {
            // divide salary by 30 days. and multiply by total working days
            $this->dynamic_daily_salary = $this->payroll_card->basic_salary / 30;
        }else
        if(config('services.dynamic_salary_by_month')) {
            // fastpack: dynamic all day salary
            // divide salary by all days and fill them later
            $this->dynamic_daily_salary = $this->payroll_card->basic_salary / count(array_keys($this->dates));
        } else {
            // divide salary by only working working days
            $this->dynamic_daily_salary = $this->payroll_card->basic_salary / (($this->available_working_days ?: 30) + $total_holidays);
        }
        $this->system_notes[] = [
            'type' => 'info',
            'name' => 'Available Working Days',
            'description' => 'Available Working Days: ' . $this->available_working_days,
            'value' => $this->available_working_days,
            'is_incremental' => null,
            'balance' => $this->earned_basic_salary,
        ];
        $attendance_count = 0;
        foreach ($this->dates as $date => $data) {
            if($data['attendance']) {
                $attendance_count++;
            }
        }

        foreach ($this->dates as $date => $data) {
            if($data['is_weekend'])
            {
                $isEligibleForTheHoliday = $this->is_eligible_for_holiday_weekend($date);
                if($isEligibleForTheHoliday) {
                    if(config('services.dynamic_salary_by_month')) {
                        $this->earned_basic_salary += $this->dynamic_daily_salary;
                    }
                    $this->system_notes[] = [
                        'type' => config('services.dynamic_salary_by_month') ? 'earn' : 'info',
                        'name' => $date . ': Weekend [' . Carbon::parse($date)->format('l') . ']',
                        'description' => 'Weekend',
                        'value' => 0,
                        'is_incremental' => null,
                        'balance' => $this->earned_basic_salary,
                    ];
                }
            }
            else if($data['holiday'] || (
                $data['leave'] && $data['leave']->leave_type?->is_paid
            ))
            {
                if($data['holiday']) {
                    $isEligibleForTheHoliday = $this->is_eligible_for_holiday_weekend($date);

                    if($attendance_count > 0 && $isEligibleForTheHoliday) {
                        $this->earned_basic_salary += $this->dynamic_daily_salary;
                        $this->system_notes[] = [
                            'type' => 'earn',
                            'name' => $date . ': Holiday',
                            'description' => 'Holiday',
                            'value' => $this->dynamic_daily_salary,
                            'is_incremental' => true,
                            'balance' => $this->earned_basic_salary,
                        ];
                    }else {
                        $this->system_notes[] = [
                            'type' => 'info',
                            'name' => $date . ': Holiday',
                            'description' => 'Not eligible for holiday: no attendance',
                            'value' => 0,
                            'is_incremental' => null,
                            'balance' => $this->earned_basic_salary,
                        ];
                    }
                }
                else {
                    $this->earned_basic_salary += $this->dynamic_daily_salary;
                    $this->system_notes[] = [
                        'type' => 'earn',
                        'name' => $date . ': Paid Leave',
                        'description' => 'Paid Leave: ' . $data['leave']->leave_type->name,
                        'value' => $this->dynamic_daily_salary,
                        'is_incremental' => true,
                        'balance' => $this->earned_basic_salary,
                    ];
                }
            }
            else if($data['attendance'])
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
        }
    }
}
