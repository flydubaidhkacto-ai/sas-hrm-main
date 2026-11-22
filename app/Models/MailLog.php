<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    protected $fillable = [
        'from_email',
        'to_email',
        'cc_email',
        'bcc_email',
        'subject',
        'module',
        'body',
        'attachments',
        'status',
        'error_message',
        'attempt',
        'meta',
        'sent_at',
    ];

    protected $casts = [
        'to_email'  => 'array',
        'cc_email'  => 'array',
        'bcc_email' => 'array',
        'meta'      => 'array',
        'sent_at' => 'datetime',
        'attachments' => 'array',
    ];
}
