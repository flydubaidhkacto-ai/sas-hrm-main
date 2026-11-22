<?php

namespace App\Jobs;

use App\Actions\SMSApi;
use App\Models\SMSQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunSMSJob implements ShouldQueue
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
    public function handle(): void
    {
        SMSQueue::query()
            ->limit(10)
            ->get()
            ->each(function($sms) {
                SMSApi::send($sms->phone, $sms->message, false);
                $sms->delete();
            });
    }
}
