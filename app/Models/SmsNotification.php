<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsNotification extends Model
{
    protected $table = "sms_notifications";

    protected $fillable = ['user_id', 'types'];

    public function rel_to_user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
