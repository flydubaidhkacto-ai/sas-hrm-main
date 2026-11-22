<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Jobs\UpdateEmployeeInOutRecords;

class ShiftController extends Controller
{
    protected function create_shift(Request $request)
    {
        if($request->is_24_hour_shift)
        {
            $request->validate([
                'max_working' => 'numeric|required|min:1|max:48',
                'max_overtime' => 'numeric|required|min:0|max:48',
            ]);
        }
        $request->validate([
            'name' => 'required|string|max:50',
            'start_time' => 'required|date_format:H:i',
            'end_time' => ['required', 'date_format:H:i'],
            'working_days' => 'required|array',
            'working_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'break_time' => 'nullable|integer|max:1440',
            'break_time_before_ot_minutes' => 'nullable|integer|max:1440',
            'start_date' => 'nullable|date',
        ]);

        $shift = Shift::create([
            'name' => $request->name,
            'start' => $request->start_time,
            'end' => $request->end_time,
            'break_time_minutes' => $request->break_time ?? 0,
            'monday' => in_array('monday', $request->working_days),
            'tuesday' => in_array('tuesday', $request->working_days),
            'wednesday' => in_array('wednesday', $request->working_days),
            'thursday' => in_array('thursday', $request->working_days),
            'friday' => in_array('friday', $request->working_days),
            'saturday' => in_array('saturday', $request->working_days),
            'sunday' => in_array('sunday', $request->working_days),
            'is_for_student' => $request->students ? true : false,
            'break_time_before_ot_minutes' => $request->break_time_before_ot_minutes ?? 0,
            'start_date' => $request->start_date ? \Carbon\Carbon::parse($request->start_date) : null,
            'is_24_hour_shift' => $request->is_24_hour_shift ? true : false,
            'max_working_minutes' => $request->is_24_hour_shift ? ($request->max_working * 60) : 0,
            'max_overtime_minutes' => $request->is_24_hour_shift ? ($request->max_overtime * 60) : 0,
            'track_break_by_checkout' => $request->track_break_by_checkout == 1,
        ]);

        // dd($shift);
        return $shift;
    }
    function shifts(Request $request)
    {
        abort_if(!role_can('shift'), 403, "Need Shift permission");
        $shifts = Shift::query();
        if($request->students)
        {
            $shifts->where('is_for_student', true);
        }else
        {
            $shifts->where('is_for_student', false);
        }
        if($request->ajax())
        {
            return response()->json($shifts->get());
        }
        return view('shifts.index', [
            'shifts' => $shifts->get()
        ]);
    }
    function create()
    {
        abort_if(!role_can('shift'), 403, "Need Shift permission");
        return view('shifts.create', [
            'is_24_hour_shift' => request()->has('24_shift'),
            'students' => request()->has('students'),
        ]);
    }
    function store(Request $request)
    {
        abort_if(!role_can('shift'), 403, "Need Shift permission");
        // dd($request->all());
        $this->create_shift($request);
        Toastr::success('Shift created successfully :)','Success');
        if($request->students)
        {
            return redirect()->route('student_shifts', ['students' => 1]);
        }
        return redirect()->route('shifts.index');
    }
    function destroy(Shift $shift, Request $request)
    {
        abort_if(!role_can('shift'), 403, "Need Shift permission");
        $shift->delete();
        Toastr::success('Shift deleted successfully :)','Success');
        return redirect()->route('shifts.index', ['students' => $request->students]);
    }
    function assign(Shift $shift, Request $request)
    {
        abort_if(!role_can('shift'), 403, "Need Shift permission");
        if($request->students)
        {
            $employees = User::students();
        }else
        {
            $employees = User::active_employees();
        }
        if($request->department)
        {
            $employees->where('department_id', $request->department);
        }elseif($request->super_department)
        {
            $employees->whereHas('department', function ($q) use($request) {
                $q->where('super_department_id', $request->super_department);
            });
        }
        if($request->designation)
        {
            $employees->where('designation_id', $request->designation);
        }
        if($request->employee)
        {
            $employees->where(function ($q) use($request) {
                $q->where('id', $request->employee);
            });
        }
        if($request->shift_id)
        {
            $employees->where('shift_id', $request->shift_id);
        }
        return view('shifts.assign', [
            'shift' => $shift,
            'employees' => $employees->orderBy('id')->paginate(1000),
            'classes' => User::students()
                ->whereNotNull('class')
                ->distinct()->pluck('class'),
        ]);
    }
    function assign_save(Shift $shift, Request $request)
    {
        abort_if(!role_can('shift'), 403, "Need Shift permission");

        if($request->unassign_employee)
        {
            User::findOrFail($request->unassign_employee)->update([
                'shift_id' => null
            ]);
            Toastr::success('Unassigned successfully :)','Success');
        }
        if($request->assign_by_department)
        {
            $employees = User::query();

            $employees->update(
                ['shift_id' => $shift->id]
            );
            Toastr::success('Assigned successfully :)','Success');
        }
        if($request->bulk_add)
        {
            $request->validate([
                'employees' => 'required|array',
                'employees.*' => 'exists:users,id',
            ]);
            User::whereIn('id', $request->employees)->update([
                'shift_id' => $shift->id
            ]);
            Toastr::success('Assigned successfully :)','Success');
        }

        if($shift->start_date)
        {
            UpdateEmployeeInOutRecords::dispatch($request->employees, $shift->start_date);
        }

        return redirect()->back();
    }
    function api_create(Request $request)
    {
        abort_if(!role_can('shift'), 403, "Need Shift permission");
        return $this->create_shift($request);
    }
}
