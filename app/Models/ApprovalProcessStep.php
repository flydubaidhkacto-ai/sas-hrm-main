<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalProcessStep extends Model
{
    protected $guarded = [];
    protected $casts = [
        'is_publisher' => 'boolean',
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function approvalProcess()
    {
        return $this->belongsTo(ApprovalProcess::class);
    }
}
