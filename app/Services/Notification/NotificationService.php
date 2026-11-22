<?php
namespace App\Services\Notification;

use App\Repositories\NotificationRepository;
use App\Services\Mail\MailLogService;

class NotificationService
{
    public function __construct(
        protected NotificationRepository $notificationRepo,
        protected MailLogService $mailService
    ) {}

public function notify(array $payload): void
{
    // 1️⃣ DB Notification
    if (!empty($payload['notification'])) {
        $this->notificationRepo->create($payload['notification']);
    }

    // 2️⃣ Mail
    if (!empty($payload['mail'])) {
        $this->mailService->send($payload['mail']);
    }
}
}
