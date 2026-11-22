<?php
namespace App\Repositories;

use App\Models\MailLog;

class MailLogRepository
{
    public function create(array $data): MailLog
    {
        return MailLog::create([
            'from_email'  => $data['from_email'],
            'to_email'    => $data['to_email'],
            'cc_email'    => $data['cc_email'] ?? null,
            'bcc_email'   => $data['bcc_email'] ?? null,
            'subject'     => $data['subject'],
            'module'     =>  $data['module'],
            'body'        => $data['body'],
            'meta'        => $data['meta'] ?? null,
            'attachments' => $data['attachments'] ?? [],
            'status'      => 'pending',
        ]);
    }
}
