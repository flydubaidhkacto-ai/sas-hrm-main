<?php

namespace App\Jobs;

use App\Actions\EmployeePunched;
use App\Models\Attendance;
use App\Models\InOutRecord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;



class EmployeePunchedJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public InOutRecord $punch, public ?Attendance $attendance = null) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $data = \App\Actions\EmployeePunched\Execute::run($this->punch, $this->attendance);
        if($data instanceof Attendance){
            // dispatch the EmployeePunchedJob again to check for half day fine
            $data->delete_attendnace_upon_late();
        }

        if ($data && $data->employee) {
            // dispatch the halfDayFineJob
            HalfDayFineJob::dispatch($data->employee_id);
        }
    }
}
