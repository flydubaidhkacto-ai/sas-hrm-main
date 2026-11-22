<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunScheduledTerminationActivation implements ShouldQueue
{
    use Queueable;

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
    public function handle()
    {
        $emps = User::employees()
            ->whereNotNull('schedule_termination_at')
            ->where('schedule_termination_at', '<=', now())
            ->chunk(100, function($emps) {
                foreach($emps as $emp)
                {
                    $emp->update(['is_active' => false, 'schedule_termination_at' => null]);
                }
            });
        $emps = User::employees()
            ->whereNotNull('schedule_re_activation_at')
            ->where('schedule_re_activation_at', '<=', now())
            ->chunk(100, function($emps) {
                foreach($emps as $emp)
                {
                    $emp->update(['is_active' => true, 'schedule_re_activation_at' => null]);
                }
            });
    }
}
