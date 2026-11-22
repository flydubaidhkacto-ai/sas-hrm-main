<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationReadUser extends Model
{
    use HasFactory;
    protected $guarded = [];

    function notification()  {
        return $this->belongsTo(Notification::class,'id', 'notification_id');
    }

    function user() {
        return $this->belongsTo(User::class);
    }

    function display_user() {
        return $this->belongsTo(User::class);
    }
}
