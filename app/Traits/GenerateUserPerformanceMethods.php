<?php

namespace App\Traits;

use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\PerformanceCategory;
use App\Models\PerformanceOfMonth;
use Carbon\Carbon;

trait GenerateUserPerformanceMethods
{
    function get_performance($month)
    {
        $date = carbon($month);
        // $month_performace
        return $this->generate_performance($date);
    }
    function generate_performance($date)
    {
        $month = $this->performances()
            ->where('month', $date->format('Y-m-d'))
            ->with('days')
            ->first();
        if (!$month) {
            $month = $this->performances()->create([
                'month' => $date->format('Y-m-d')
            ]);
        }
        $days = $month->days()->with('category')->get();//$this->generate_days($month, $date);
        $categories = PerformanceCategory::query()
            ->whereIn('id', $days->pluck('performance_category_id'))
            ->get();
        // dump($days);
        $data = [
            'categories' => $categories,
            'days' => $days,
            'categorized' => [],
            'month' => $month
        ];
        foreach ($days as $day) {
            $data['categorized'][$day->performance_category_id]['days'][$day->date] = $day;
        }
        foreach ($data['categorized'] as $i => $categorized) {
            $total = 0;
            $got = 0;
            foreach ($categorized['days'] as $day) {
                $total += $day->category?->max_value ?: 0;
                $got += $day->value;
            }
            $data['categorized'][$i]['total'] = $total;
            $data['categorized'][$i]['got'] = $got;
            $data['categorized'][$i]['average'] = round($got == 0 ? 0 : ($total / $got), 2);
            $data['categorized'][$i]['percentage'] = round(($total == 0 ? 0 : $got / $total) * 100, 2);
        }
        return $data;
    }
    function generate_days(PerformanceOfMonth $month, Carbon $date, $category, $categories = null)
    {
        // loop thorugh al lthe days in the month
        for ($i = 1; $i <= $date->copy()->endOfMonth()->day; $i++) {
            $this_date = $date->copy()->setDay($i);
            // foreach ($categories as $category) {
                $check_data = [
                    'date' => $this_date->format('Y-m-d'),
                    'performance_category_id' => $category->id
                ];
                $check = $month->days()
                    ->where($check_data)
                    ->with('category')
                    ->first();
                if ($check && !($category->from_system))
                    continue;

                $val = 0;
                $max_value = $category->max_value;
                if ($category->from_system) {
                    $nam = $category->name;
                    if ($nam == 'Attendance' || $nam == 'Present') {
                        $val = Attendance::where('employee_id', $this->id)
                            ->where('date', $this_date->format('Y-m-d'))
                            ->count();
                    } else if ($nam == 'Absent') {
                        $val = (
                            !Attendance::where('employee_id', $this->id)
                                ->where('date', $this_date->format('Y-m-d'))
                                ->count()
                            && !LeaveApplication::final_approved()
                                ->where('is_hourly', false)
                                ->where('employee_id', $this->id)
                                ->where('start_date', '>=', $this_date->format('Y-m-d'))
                                ->where('end_date', '<=', $this_date->format('Y-m-d'))
                                ->count()
                        ) ? 1 : 0;
                    } else if ($nam == 'Late') {
                        $val = Attendance::where('employee_id', $this->id)
                            ->where('date', $this_date->format('Y-m-d'))
                            ->where('got_late_check_in', true)
                            ->count();
                    } else if ($nam == 'Leave') {
                        $val = LeaveApplication::final_approved()
                            ->where('is_hourly', false)
                            ->where('employee_id', $this->id)
                            ->whereDate('start_date', '<=', $this_date->format('Y-m-d'))
                            ->whereDate('end_date', '>=', $this_date->format('Y-m-d'))
                            ->whereHas('leave_type', function ($q) {
                                $q->where('affects_leave_balance', true);
                            })
                            ->count() ? 1 : 0;
                        // dump($nam, $val);
                    } else if ($nam == 'Paid Leave') {
                        $val = LeaveApplication::final_approved()
                            ->where('is_hourly', false)
                            ->where('employee_id', $this->id)
                            ->whereDate('start_date', '<=', $this_date->format('Y-m-d'))
                            ->whereDate('end_date', '>=', $this_date->format('Y-m-d'))
                            ->whereHas('leave_type', function ($q) {
                                $q->where('is_paid', true);
                                $q->where('affects_leave_balance', true);
                            })
                            ->count() ? 1 : 0;
                    } else if ($nam == 'Unpaid Leave') {
                        $val = LeaveApplication::final_approved()
                            ->where('employee_id', $this->id)
                            ->where('is_hourly', false)
                            ->whereDate('start_date', '<=', $this_date->format('Y-m-d'))
                            ->whereDate('end_date', '>=', $this_date->format('Y-m-d'))
                            ->whereHas('leave_type', function ($q) {
                                $q->where('is_paid', false);
                            })
                            ->count() ? 1 : 0;
                    }
                }
                if ($check) {
                    $check->delete();
                }
                $day = $month->days()->create([
                    'value' => $val,
                    'max_value' => $max_value,
                    ...$check_data
                ]);
            // }
        }
    }
}
