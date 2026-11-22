<?php

namespace App\Models;

use \App\Services\FcmService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Google\Auth\OAuth2;


class Notification extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function notification_read_users()
    {
        return $this->hasMany(NotificationReadUser::class);
    }


    function for_user()
    {
        return $this->belongsTo(User::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function usersss()
    // {
    //     return $this->belongsToMany(User::class);
    // }

    protected static function boot()
    {
        parent::boot();

        // Define the 'deleting' event handler
        static::deleted(function ($post) {

            $notifications = NotificationReadUser::where('notification_id', $post->id)->get();
            foreach ($notifications as $notification) {
                $notification->delete();
            }
        });

        static::created(function(self $model)
        {
            if(user())
            {
                NotificationReadUser::create(
                    [
                        'notification_id' => $model->id,
                        'user_id' => user()->id,
                        'display_user_id' => user()->id
                    ]
                );
            }

            // send notification to user
            $tokens = User::query()
                ->whereNotNull('firebase_token')
                ->when($model->user_id, function($query) use($model) {
                    return $query->where('id', '=', $model->user_id);
                })
                ->pluck('firebase_token')
                ->toArray();
            // dispatch an anonymous job
            dispatch(function() use($model, $tokens) {
                foreach ($tokens as $token) {
                    try {
                        self::sendFcmV1Notification(
                            $token, $model->title,
                            str_replace("<br>", "\n", str_replace("  ", '', $model->message)),
                            $model->mobile_url
                        );
                    } catch(\Exception $e) {
                        if(config('app.debug')) throw $e;
                    }
                }
            });
        });
    }

    static function sendFcmV1Notification($deviceToken, $title, $body, $url = null)
    {
        FcmService::send(
            $deviceToken, $title, $body,
            ($url ? ['url' => $url] : [])
        );
    }

}
