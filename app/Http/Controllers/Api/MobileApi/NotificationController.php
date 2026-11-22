<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationReadUser;
use App\Classes\ApiResponseClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB as FacadesDB;

class NotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $user = $request->auth_user;
        $now = Carbon::now();

        $notification = Notification::where(function ($query) use ($user) {
            $query->whereNull('user_id')->orwhere('user_id', $user->id);
        })
            ->whereDoesntHave('notification_read_users', function ($qr) use ($user) {
                $qr->where('display_user_id', $user->id);
            })
            ->where('time_of_notify', '<', $now)
            ->get();

        // dump($noti);

        $notification = $notification->reverse();


        $notification_count = Notification::where(function ($query) use ($user) {
            $query->whereNull('user_id')->orwhere('user_id', $user->id);
        })
            ->WhereDoesntHave('notification_read_users', function ($qr) use ($user) {
                $qr->where('user_id', $user->id);
            })
            ->where('time_of_notify', '<', $now)
            ->count();



        $count = Notification::where(function ($query) use ($user) {
            $query->whereNull('user_id')->orwhere('user_id', $user->id);
        })
            ->WhereDoesntHave('notification_read_users', function ($qr) use ($user) {
                $qr->where('user_id', $user->id);
            })
            ->where('time_of_notify', '<', $now)
            ->count();

            $unreadNotifications = $notification->reject(function ($item) use ($user) {
                return FacadesDB::table('notification_read_users')
                        ->where('notification_id', $item->id)
                        ->where('user_id', $user->id)
                        ->exists();
            });

        $unread_notifications_count = $unreadNotifications->count();

        return ApiResponseClass::sendResponse([
            'notifications' => $notification,
            'count' => $count,
            'unread_notifications' => $unreadNotifications,
            'notification_count' => $notification_count,
            'unread_notifications_count' => $unread_notifications_count
        ], 'Notifications Fetched Successfully');
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->auth_user;

        $now = Carbon::now();
        $notifications = Notification::where(function ($query) use ($user) {
            $query->whereNull('user_id')->orWhere('user_id', $user->id);
        })
            ->where('time_of_notify','<',$now )
            ->whereDoesntHave('notification_read_users', function ($qr) use ($user) {
                $qr->where('display_user_id', $user->id)
                   ->where('user_id', $user->id);
            })
            ->get();

            foreach ($notifications as $notification) {
                NotificationReadUser::updateOrCreate(
                    [
                        'notification_id' => $notification->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'display_user_id' => null,
                    ]
                );
            }

        return ApiResponseClass::sendResponse([
            'message' => 'All notifications marked as read'
        ], 'All Notifications Marked as Read Successfully');
    }

    public function vanishSingle(Request $request, $id)
    {
        $user = $request->auth_user;

        $displayNotification = NotificationReadUser::where('user_id', $user->id)
                                                ->where('notification_id', $id)
                                                ->first();
        if ($displayNotification) {
            $displayNotification ->update(['display_user_id' => $user->id]);
        }else{
            NotificationReadUser::updateOrCreate(
                [
                    'notification_id' => $id,
                    'user_id' => $user->id,
                ],
                [
                    'display_user_id' => $user->id,
                ]
            );
        }

        return ApiResponseClass::sendResponse([
            'message' => 'Notification vanished'
        ], 'Notification Vanished Successfully');
    }

    public function show(Request $request,$id)
    {
        $user = $request->auth_user;
        $notification = Notification::findOrFail($id);

       $NotificationReadUser = NotificationReadUser::updateOrCreate(
            [
                'notification_id' => $id,
                'user_id' => $user->id,
            ],
            [
                'display_user_id' => null,
            ]
        );

        return ApiResponseClass::sendResponse([
            'data' => $notification,
            'notification_read_user' => $NotificationReadUser,
        ], 'Notification Fetched Successfully');
    }

    public function my_notification(Request $request)
    {
        $user = $request->auth_user;

        // $notifications = Notification::where('user_id', $user->id)
        $notifications = Notification::query()
        ->where(function ($query) use ($user) {
            $query->whereNull('user_id')->orWhere('user_id', $user->id);
        })
        ->whereDoesntHave('notification_read_users', function($query) use ($user) {
            $query->where('display_user_id', $user->id);
        })
        ->where('time_of_notify','<', now() )
        ->get();

        return ApiResponseClass::sendResponse([
            'data' => $notifications
        ], 'Notifications Fetched Successfully');
    }


    public function vanishAll(Request $request)
    {
        $user = $request->auth_user;

        $now = Carbon::now();
        $notifications = Notification::where(function ($query) use ($user) {
            $query->whereNull('user_id')->orWhere('user_id', $user->id);
        })
            ->where('time_of_notify','<', $now )
            ->whereDoesntHave('notification_read_users', function ($qr) use ($user) {
                $qr->where('display_user_id', $user->id)
                   ->where('user_id', $user->id);
            })
            ->get();

            foreach ($notifications as $notification) {
                NotificationReadUser::updateOrCreate(
                    [
                        'notification_id' => $notification->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'display_user_id' => $user->id,
                    ]
                );
            }

        return ApiResponseClass::sendResponse([
            'message' => 'All notifications marked as read'
        ], 'All Notifications Marked as Read Successfully');
    }
}
