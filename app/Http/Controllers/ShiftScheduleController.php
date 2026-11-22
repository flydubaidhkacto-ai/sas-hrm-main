<?php

namespace App\Http\Controllers;

use App\Models\ShiftSchedule;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class ShiftScheduleController extends Controller
{
    function can()
    {
        abort_if(
            !role_can('shift_scheduling'),
            403,
            'You are not allowed to access this page'
        );
    }
    function index(Request $request)
    {
        $this->can();
        // dd(
        //     ShiftSchedule::query()
        //         ->latest()
        //         ->with('items.shift')
        //         ->first()
        //         ->formatted()
        // );
        return view('shift-scheduling.index', [
            'schedules' => ShiftSchedule::query()
                ->latest()
                ->with('items')
                ->paginate(10)
        ]);
    }
    function create(Request $request)
    {
        $this->can();
        $from = $request->from ? carbon($request->from) : now()->subWeek()->startOfDay();
        $to = $request->to ? carbon($request->to) : now()->endOfDay();
        $dates = [];
        for ($date = $from; $date->lte($to); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }
        return view('shift-scheduling.create', [
            'employees' => User::active_employees()
                ->with([
                    'designation',
                    'department',
                    'department.super_department',
                ])
                ->get(),
            'dates' => $dates
        ]);
    }
    function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'data' => 'required|array',
            'data.*.employees' => 'required|array',
            'data.*.shifts' => 'required|array',
        ]);
        $schedule = ShiftSchedule::create([
            'name' => $request->name,
            'user_id' => user()->id,
            'assigned_by' => user()->id,
            'start' => array_keys($request->data[0]['shifts'])[0],
            'end' => array_keys($request->data[0]['shifts'])[count($request->data[0]['shifts']) - 1],
        ]);
        foreach ($request->data as $i => $item) {
            foreach ($item['shifts'] as $date => $shift)
            {
                if($shift)
                {
                    foreach($item['employees'] as $employee)
                    {
                        $schedule->items()->create([
                            'employee_id' => $employee,
                            'shift_id' => $shift['id'],
                            'date' => $date,
                            'group' => $i,
                        ]);
                    }
                }
            }
        }
        return $schedule;
    }
    function delete($id)
    {
        $this->can();
        $f = ShiftSchedule::find($id);
        $f->items()->delete();
        $f->delete();
        Toastr::success('Schedule deleted successfully');
        return redirect()->back()->with('success', 'Schedule deleted successfully');
    }
}
