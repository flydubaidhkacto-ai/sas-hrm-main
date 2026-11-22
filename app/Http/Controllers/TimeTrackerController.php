<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TimeTracker;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;


class TimeTrackerController extends Controller
{
    public function startTimer(Task $task)
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        TimeTracker::create([
            'task_id' => $task->id,
            'user_id' => user()->id,
            'start_time' => now(),
            'end_time' => null,
            'status' => 'active'
        ]);
        Toastr::success('Timer started successfully!');
        return redirect()->back();
    }

    // public function pushTimer(Task $task)
    // {
    //     $tracker = TimeTracker::where('task_id', $task->id)
    //         ->where('status', 'active')
    //         ->first();

    //     if ($tracker) {
    //         $hours = now()->diffInHours($tracker->start_time);
    //         $tracker->update([
    //             'end_time' => now(),
    //             'hours' => $hours,
    //             'status' => 'completed'
    //         ]);
    //     }

    //     return response()->json(['message' => 'Time tracked successfully']);
    // }
    public function stopTimer(Task $task)
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);
        $tracker = TimeTracker::where('task_id', $task->id)->where('user_id', user()->id)->where('status', 'active')->first();

        if ($tracker) {
            $tracker->update([
                'status' => 'stopped',
                'end_time' => now()
            ]);
        }
        Toastr::success('Timer stopped successfully!');
        return redirect()->back();
    }

    public function timesheet()
    {
        abort_if((!role_can('work-employee') && !role_can('work-admin')), 403);

        if (role_can('work-employee') && !user()->is_super_admin()) {
            $timeTrackers = TimeTracker::with('task', 'employee_info')->where('user_id', user()->id)->get();
        } else {
            $timeTrackers = TimeTracker::with('task', 'employee_info')->get();
        }


        // $projects = Task::whereIn('id', $timeTrackers->pluck('task_id'))->get();
        return view('works.time_tracker.timesheet', compact('timeTrackers'));
    }
}
