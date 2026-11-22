<?php

namespace App\Jobs;

use App\Models\Fine;
use App\Models\HalfDayFine;
use App\Models\User;
use App\Traits\GenerateTotalDutyOfEmployee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HalfDayFineJob implements ShouldQueue
{
    use Queueable;
    use GenerateTotalDutyOfEmployee;

    public $emp;
    /**
     * Create a new job instance.
     */
    public function __construct(public int $emp_id)
    {
        $this->emp = User::findOrFail($emp_id);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $emp_log = $this->generate_total_duty_of_employee($this->emp, now(), now());

        $half_day_fines = HalfDayFine::orderBy('minutes', 'desc')->get();

        $amount = 0;
        $added_by_user_id = 0;
        $flag = false;
        foreach ($half_day_fines as $fines) {

            $fines_seconds = ($fines->minutes * 60);

            if ($emp_log['total_late_seconds'] <= $fines_seconds) {
                $flag = true;
                // Fixed
                if ($fines->amount_type == 'fixed') {

                    $amount = ($fines->deduction_amount / $fines_seconds) * $emp_log['total_late_seconds'];
                    $added_by_user_id = $fines->created_by;
                    Log::debug('Amount: ' . $amount);
                    Log::debug('Added By User ID: ' . $added_by_user_id);
                    Log::debug('Emp ID: ' . $this->emp->id);
                    break;
                }

                // Basic Percent
                if ($fines->amount_type == 'basicPercent') {


                    $amount = ((($fines->deduction_amount / 100) * $this->emp->payroll_card->basic_salary) / $fines_seconds) * $emp_log['total_late_seconds'];
                    $added_by_user_id = $fines->created_by;

                    Log::debug('Amount: ' . $amount);
                    Log::debug('Added By User ID: ' . $added_by_user_id);
                    Log::debug('Emp ID: ' . $this->emp->id);
                    break;
                }
            }
        }


        // Working Hour
        if ($flag) {
            Log::debug('active');
            Fine::create([
                'amount' => $amount,
                'reason' => 'Auto Late Fine',
                'cut_off_as' => 'once',
                'user_id' => $this->emp->id,
                'added_by_user_id' => $added_by_user_id,
                'approved_at' => now(),
            ]);
        }
    }
}
