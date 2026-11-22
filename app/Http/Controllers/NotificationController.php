<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationReadUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;

class NotificationController extends Controller
{

    public function index()
    {
        abort_if(
            !role_can('notifications')
            && !user()->admin_of_super_departments,
            403
        );
        $all_user = User::active_employees()
            ->when(!role_can('notifications') && user()->admin_of_super_departments, function($q) {
                $q->whereHas('department', function ($q2) {
                    $q2->whereIn('super_department_id', user()->admin_of_super_departments);
                });
            })
            ->get();

        $notifications = Notification::query()
            ->orderBy('id', 'desc')
            ->when(!role_can('notifications') && user()->admin_of_super_departments, function($q) {
                $q->whereHas('user.department', function ($q2) {
                    $q2->whereIn('super_department_id', user()->admin_of_super_departments);
                });
            })
            ->paginate(10);
        return view('notification.notification', compact('notifications', 'all_user'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'notifyTime' => 'required|date'
        ]);
        if(
            !role_can('notifications')
            && !is_admin_of_super_department(
                User::find($request->employee)?->department?->super_department_id ?: 0
            )
        ) {
            Toastr::error('Please sleect an employee');
            return redirect()->back();
        }

        $submittedTime = Carbon::parse($validatedData['notifyTime']);

        Notification::create([
            'title' => $request->title,
            'message' => $request->message,
            'time_of_notify' => $submittedTime,
            'user_id' => $request->employee
        ]);

        Toastr::success('Notification Saved successfully :)','Success');
        return redirect()->back();
    }


    public function show($id)
    {
        $notification = Notification::findOrFail($id);

        $readNotification = NotificationReadUser::updateOrCreate(
            [
                'notification_id' => $id,
                'user_id' => user()->id,
            ],
            [
                'display_user_id' => null,
            ]
        );

        return view('notification.individualnotification', compact('notification'));
    }


    public function edit($id)
    {
        $notification = Notification::findOrFail($id);
        return view('notification.notification_edit', compact('notification'));
    }

    public function mark_all_read($id)
    {
        $now = Carbon::now();
        $notifications = Notification::where(function ($query) {
            $query->whereNull('user_id')->orWhere('user_id', user()->id);
        })
            ->where('time_of_notify','<',$now )
            ->whereDoesntHave('notification_read_users', function ($qr) {
                $qr->where('display_user_id', user()->id)
                   ->where('user_id', user()->id);
            })
            ->get();

            foreach ($notifications as $notification) {
                NotificationReadUser::updateOrCreate(
                    [
                        'notification_id' => $notification->id,
                        'user_id' => user()->id,
                    ],
                    [
                        'display_user_id' => null,
                    ]
                );
            }


        Toastr::success('Notification Update successfully :)','Success');
        return redirect()->back();
    }

    public function vanish($id,$user)
    {
        $displayNotification = NotificationReadUser::where('user_id', $user)
                                                ->where('notification_id', $id)
                                                ->first();
        if ($displayNotification) {
            $displayNotification ->update(['display_user_id' => user()->id]);
        }else{
            NotificationReadUser::create([
                'notification_id' => $id,
                'user_id' => $user,
                'display_user_id' => user()->id
            ]);
        }

        Toastr::success('Notification Deleted successfully :)','Success');
        return redirect()->back();
    }


    public function update(Request $request )
    {
        dd(2);
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'notifyTime' => 'required|date'
        ]);

        $submittedTime = Carbon::parse($validatedData['notifyTime']);
        $notification = Notification::find($request->id);
        $notification->update([
            'title' => $request->title,
            'message' => $request->message,
            'time_of_notify' => $submittedTime,
            'user_id' => $request->employee
        ]);

        Toastr::success('Notification Update successfully :)','Success');
        return redirect()->back();
    }
    public function destroy(Request $request)
    {
        $notification=Notification::findOrFail($request->id);
        $notification->delete();

        Toastr::success('Notification Deleted successfully :)','Success');
        return redirect()->back();
    }

    public function my_notification()
    {
        $notifications = Notification::where('user_id', user()->id)
            ->whereDoesntHave('notification_read_users', function($query) {
                $query->where('display_user_id', user()->id);
            })
            ->paginate(10);

        return view('notification.my_notification', compact('notifications'));
    }

    public function mark_all_deleted($id)
    {
        $now = Carbon::now();
        $notifications = Notification::where(function ($query) {
            $query->whereNull('user_id')->orWhere('user_id', user()->id);
        })
            ->where('time_of_notify','<',$now )
            ->whereDoesntHave('notification_read_users', function ($qr) {
                $qr->where('display_user_id', user()->id)
                   ->where('user_id', user()->id);
            })
            ->get();

            foreach ($notifications as $notification) {
                NotificationReadUser::updateOrCreate(
                    [
                        'notification_id' => $notification->id,
                        'user_id' => user()->id,
                    ],
                    [
                        'display_user_id' => user()->id,
                    ]
                );
            }


        Toastr::success('Notification Deleted successfully :)','Success');
        return redirect()->back();
    }
}
