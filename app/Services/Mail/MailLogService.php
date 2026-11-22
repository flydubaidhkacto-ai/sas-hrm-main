<?php
namespace App\Services\Mail;

use App\Repositories\MailLogRepository;
use App\Jobs\SendMailJob;

class MailLogService
{
    public function __construct(
        protected MailLogRepository $mailLogRepo
    ) {}

public function send(array $data)
{
    $mailLog = $this->mailLogRepo->create($data);

    SendMailJob::dispatch($mailLog);
//
//    $job = new SendMailJob($mailLog);
//    $job->handle();

    return $mailLog;
}
}
