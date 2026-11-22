<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProvidentFundPaymentRecord extends Model
{
    protected $guarded = [];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
