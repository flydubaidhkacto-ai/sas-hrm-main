<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserDocument extends Model
{
    protected $fillable = ['user_id', 'type', 'path'];

    static function boot()
    {
        parent::boot();
        static::deleting(function(self $userDocument) {
            Storage::delete($userDocument->path);
        });
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
