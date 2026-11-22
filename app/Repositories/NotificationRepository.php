<?php
namespace App\Repositories;

use App\Models\Notification;

class NotificationRepository
{
    public function create(array $data): Notification
    {
        return Notification::create([
            'title'          => $data['title'],
            'message'        => $data['message'],
            'user_id'        => $data['user_id'],
            'url'            => $data['url'] ?? null,
            'time_of_notify' => now(),
        ]);
    }
}
