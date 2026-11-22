<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\AutoLateFine;
use App\Models\Fine;
use App\Models\LeaveApplication;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InitAutoLateFineEmployees implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $rules = AutoLateFine::query()->get();
        if ($rules->count() > 0) {
            foreach ($rules as $rule_index => $rule) {
                User::active_employees()
                    ->whereHas('shift')
                    ->whereHas('payroll_card')
                    ->with('payroll_card')
                    ->chunk(50, function ($employees) use ($rule, $rule_index) {
                        foreach ($employees as $employee) {
                            $amount = $rule->fine_amount;
                            if ($rule->fine_amount_type === 'days') {
                                $amount = round((
                                    $employee->payroll_card->salary / count($employee->total_working_days()) * $rule->fine_amount
                                ), 2);
                            }
                            // if($rule->type == 'late')
                            // {
                            $attendances = Attendance::where('employee_id', $employee->id)
                                    ->whereDate('date', '>=', now()->startOfMonth()->format('Y-m-d'))
                                    ->whereDate('date', '<=', now()->endOfMonth()->format('Y-m-d'))
                                    ->where($rule->type == 'late' ? 'got_late_check_in' : 'did_early_check_out', 1)
                                    ->whereNull(
                                        $rule->type == 'late' ? 'late_fine_id' : 'early_out_fine_id'
                                    )
                                    ->orderBy('date', 'asc')
                                    ->get();

                                // Exclude attendances that have approved hourly leave on the same date
                                // so employees are not penalized for late check-in during hourly leave
                                if ($rule->type == 'late') {
                                    $hourlyLeaveDates = LeaveApplication::where('employee_id', $employee->id)
                                        ->where('is_hourly', true)
                                        ->where('status', 'approved')
                                        ->whereDate('start_date', '>=', now()->startOfMonth()->format('Y-m-d'))
                                        ->whereDate('end_date', '<=', now()->endOfMonth()->format('Y-m-d'))
                                        ->pluck('start_date')
                                        ->map(fn($d) => carbon($d)->format('Y-m-d'))
                                        ->toArray();

                                    $attendances = $attendances->filter(function ($att) use ($hourlyLeaveDates) {
                                        $attDate = carbon($att->date)->format('Y-m-d');
                                        return !in_array($attDate, $hourlyLeaveDates);
                                    });
                                }

                                $total_late = $attendances->count();

                                if ($rule->late_days === 'any_days_on_month' && $total_late == $rule->late_days_amount) {
                                    $newFine = Fine::create([
                                        'user_id' => $employee->id,
                                        'added_by_user_id' => 1,
                                        'amount' => $amount,
                                        'reason' => 'Auto Late Fine for ' . ($attendances->map(fn ($a) => carbon($a->date)->format('d-M-Y')))->implode(', '),
                                        'cut_off_as' => 'once',
                                        'approved_at' => $rule->fine_status === 'approved' ? now() : null,
                                    ]);
                                    $attendances->each(function ($attendance) use ($newFine) {
                                        $attendance->update(['late_fine_id' => $newFine->id]);
                                    });
                                }
                                // dump($rule->late_days);
                                if ($rule->late_days === 'consecutive_days') {
                                    $consecutive_late = [];
                                    $numConsecutive = $rule->late_days_amount;
                                    $dates = $attendances->pluck('date')->toArray();
                                    $count = count($dates);

                                    for ($i = 0; $i < $count - $numConsecutive + 1; $i++) {
                                        $consecutive = true;
                                        $consecutiveDates = [$dates[$i]];

                                        for ($j = 0; $j < $numConsecutive - 1; $j++) {
                                            $date1 = strtotime($consecutiveDates[$j]);
                                            $date2 = strtotime($dates[$i + $j + 1]);
                                            // Calculate the difference in seconds between consecutive dates
                                            $diff = abs($date2 - $date1);
                                            // If the difference is not equal to 86400 seconds (1 day), then dates are not consecutive
                                            if ($diff != 86400) {
                                                $consecutive = false;
                                                break;
                                            }
                                            // Add consecutive date to the list
                                            $consecutiveDates[] = $dates[$i + $j + 1];
                                        }
                                        if ($consecutive) {
                                            $atts = Attendance::whereIn('date', $consecutiveDates)
                                                ->where('employee_id', $employee->id)
                                                ->get();
                                            $newFine = Fine::create([
                                                'user_id' => $employee->id,
                                                'added_by_user_id' => 1,
                                                'amount' => $amount,
                                                'reason' => 'Auto Late Fine for ' . ($atts->map(fn ($a) => carbon($a->date)->format('d-M-Y')))->implode(', '),
                                                'cut_off_as' => 'once',
                                                'approved_at' => $rule->fine_status === 'approved' ? now() : null,
                                            ]);

                                            $atts->update(['late_fine_id' => $newFine->id]);
                                        }
                                    }
                                }
                            // }else
                            // if($rule->type == 'early-out')
                            // {

                            // }
                        }
                    });
            }
        }
    }
}
