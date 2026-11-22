<?php

namespace App\Jobs;

use App\Actions\GeneratePayslip\Generate;
use App\Actions\GenerateTotalDutyOfEmployee;
use App\Http\Controllers\ReportController;
use App\Models\Attendance;
use App\Models\AutoAbsentFine;
use App\Models\AutoLateFine;
use App\Models\Fine;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InitAutoAbsentFineEmployees implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public ?string $date = null, public $user_id = null)
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
        $date = $this->date ?? now()->format('Y-m-d');
        $rules = AutoAbsentFine::query()->get();
        if ($rules->count() > 0) {
            User::active_employees()
                ->when($this->user_id, function ($query) {
                    $query->where('id', $this->user_id);
                })
                ->whereHas('shift')
                ->whereHas('payroll_card')
                ->with('payroll_card')
                ->chunk(50, function ($employees) use ($rules, $date) {
                    foreach ($employees as $employee) {
                        $g = new Generate($employee, carbon($date));
                        $g->get_meta_data();
                        $g->generateDates();
                        $g->earn_basic_salary();

                        $dataOfDuty = GenerateTotalDutyOfEmployee::execute(
                            $employee, carbon($date), carbon($date)
                        );
                        // dump($dataOfDuty['total_absent_days'], $date);
                        if($dataOfDuty['total_absent_days']) {
                            foreach ($rules as $rule) {
                                $amount = $rule->fine_amount ?: 0;
                                if ($rule->fine_amount_type === 'days') {
                                    $amount = round((
                                        $g->dynamic_daily_salary * $rule->fine_amount
                                    ), 2);
                                }
                                $reason = 'Auto Absent Fine for ' . carbon($date)->format('d F, Y');
                                $findPrev = Fine::where('user_id', $employee->id)
                                    ->where('reason', $reason)
                                    ->exists();
                                if($findPrev) continue;
                                try{
                                $newFine = Fine::create([
                                    'user_id' => $employee->id,
                                    'added_by_user_id' => 1,
                                    'amount' => $amount,
                                    'reason' => $reason,
                                    'cut_off_as' => 'once',
                                    'approved_at' => strtolower($rule->fine_status) === 'approved' ? now() : null,
                                    'status' => strtolower($rule->fine_status) == 'pending' ? 'pending' : 'approved',
                                    'created_at' => $date,
                                    'updated_at' => $date,
                                ]);
                                }catch(\Throwable $e){

                                }
                            }
                        }
                        // dump($newFine ?? null, $employee);
                    }
                });

        }
    }
}
