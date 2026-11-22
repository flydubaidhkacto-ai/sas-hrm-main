<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalReminder extends Model
{
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'approver_id',
        'reminder_type',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
