<?php

namespace App\Jobs;

use App\Models\MailLog;

class RetryFailedMailJob extends Job
{
    public function handle(): void
    {
        MailLog::where('status', 'failed')
            ->where('attempt', '<', 5)
            ->orderBy('id')
            ->chunk(50, function ($logs) {
                foreach ($logs as $log) {
                    dispatch(new SendMailJob($log));
                }
            });
    }
}
