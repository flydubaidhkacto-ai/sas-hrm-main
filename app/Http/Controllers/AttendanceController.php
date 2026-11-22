<?php

namespace App\Http\Controllers;

use App\Actions\EmployeeCurrentPunchStatus;
use App\Actions\EmployeePunched;
use App\Models\Attendance;
use App\Models\InOutRecord;
use App\Models\OvertimeSchedule;
use App\Models\OvertimeScheduleDate;
use App\Models\Setting;
use App\Models\TouchedEmployeePunch;
use App\Models\User;
use App\Interfaces\AttendanceRepositoryInterface;
use App\Models\LeaveApplication;
use App\Models\Shift;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    function entry()
    {
        return view('attendance.entry');
    }
    function entry_save(Request $request)
    {
        $data = $request->validate([
            'employee' => 'required|exists:users,id',
            'notes' => 'nullable|max:2000|string',
            'duty_type' => 'nullable|max:200|string',
            'punches' => 'required|array',
            'punches.*' => 'nullable|date',
            'shift_id' => 'required|exists:shifts,id',
        ]);
        $emp = User::findOrFail($data['employee']);
        $prev_shift_id = $emp->shift_id;

        abort_if(
            !(
                role_can('attendance')
                || role_can('attendance.self.super_department')
                || user()->admin_of_super_departments
            ),
            403
        );
        $last = null;
        $emp->shift_id = $data['shift_id'];
        $emp->save();
        foreach ($data['punches'] as $punch) {
            if ($punch) {
                $last = $punch;
                $att = \App\Actions\EmployeePunched\Execute::run(InOutRecord::create([
                    'date_time' => $punch,
                    'user_id' => $emp->id,
                    'status' => 0,
                    'device_uid' => 'Manually added by #' . user()->id . ': ' . user()->name,
                ]));
                if ($att) {
                    if (isset($data['notes']) && $data['notes']) {
                        $att->notes = $data['notes'];
                        $att->notes_by = user()->id;
                    }
                    if (isset($data['duty_type']) && $data['duty_type']) {
                        $att->duty_type = $data['duty_type'];
                    }
                    $att->added_manually_by = user()->id;
                    $att->save();
                }
            }
        }
        $emp->shift_id = $prev_shift_id;
        $emp->save();
        // dd($att ?? null, $data);
        Toastr::success('Attendance added successfully :)', 'Success');
        return redirect()->route('reports.attendance', [
            'employee' => $data['employee'],
            'start_date' => carbon($data['punches'][0] ?? null)->format('d-m-Y'),
            'end_date' => carbon($last)->format('d-m-Y'),
            'editable' => 1,
        ]);
    }
    function edit($id)
    {
        $att = Attendance::findOrFail($id);
        return view('attendance.edit', [
            'att' => $att
        ]);
    }
    function update($id, Request $request)
    {
        $att = Attendance::findOrFail($id);
        $data = $request->validate([
            'punches' => 'required|array',
            'notes' => 'nullable|max:2000|string',
            'duty_type' => 'nullable|max:200|string',
        ]);
        $emp = User::findOrFail($att->employee_id);
        $prev_shift_id = $att->shift_id;
        if ($request->shift_id) {
            $emp->shift_id = $request->shift_id;
            $emp->save();
            $att->shift_id = $request->shift_id;
            $att->save();
        }
        abort_if(
            (
                !(
                    role_can('attendance')
                    || role_can('attendance.self.super_department')
                )
                || (
                    !role_can('attendance')
                    && role_can('attendance.self.super_department')
                    && $emp->department?->super_department_id != user()->department?->super_department_id
                )
                || (
                    !role_can('attendance')
                    && user()->admin_of_super_departments
                    && !in_array($emp->department?->super_department_id, user()->admin_of_super_departments ?? [])
                )
            ),
            403
        );

        $in_id = $request->in_id;
        $out_id = $request->out_id;
        if ($in_id) InOutRecord::query()->where('id', $in_id)->delete();
        if ($out_id) InOutRecord::query()->where('id', $out_id)->delete();

        $atts = [];
        foreach ($data['punches'] as $index => $date) {
            $atts[] = \App\Actions\EmployeePunched\Execute::run(InOutRecord::create([
                'user_id' => $att->employee_id,
                'status' => 0,
                'date_time' => str_replace('T', ' ', $date['in']),
                'device_uid' => 'Manually edited by #' . user()->id . ': ' . user()->name,
                'added' => 1,
            ]));
            $atts[] = \App\Actions\EmployeePunched\Execute::run(InOutRecord::create([
                'user_id' => $att->employee_id,
                'status' => 1,
                'date_time' => str_replace('T', ' ', $date['out']),
                'device_uid' => 'Manually edited by #' . user()->id . ': ' . user()->name,
                'added' => 1,
            ]));
        }
        foreach ($atts as $att) {
            if (!$att) continue;
            if (isset($data['notes']) && $data['notes']) {
                $att->notes = $data['notes'];
                $att->notes_by = user()->id;
            }
            if (isset($data['duty_type']) && $data['duty_type']) {
                $att->duty_type = $data['duty_type'];
            }
            $att->added_manually_by = user()->id;
            $att->save();
            foreach ($att->used_in_out_records as $in_out_record) {
                TouchedEmployeePunch::firstOrCreate([
                    'user_id' => $in_out_record->user_id,
                    'date_time' => $in_out_record->date_time->format('Y-m-d H:i:s')
                ]);
            }
        }

        if ($request->shift_id) {
            $emp->shift_id = $prev_shift_id;
            $emp->save();
        }
        Toastr::success('Attendance edited successfully :)', 'Success');
        return redirect()->back();
    }

    function overtime_schedule_date_all_overtime(Request $request)
    {
        abort_if(!role_can('overtime_schedule') && !user()->admin_of_super_departments, 403);
        Setting::can_everyone_special_overtime($request->val == 1);
        Toastr::success('Overtime limit updated successfully :)', 'Success');
        return redirect()->back();
    }
    function overtime_schedule_date(Request $request)
    {
        abort_if(!role_can('overtime_schedule') && !user()->admin_of_super_departments, 403);

        $date = carbon($request->date);
        $employees = User::active_employees();
        if (user()->admin_of_super_departments) {
            $employees->whereHas('department', function ($query) use ($request) {
                return $query->whereIn('super_department_id', user()->admin_of_super_departments);
            });
        }
        if ($request->employee) {
            $employees->where(function ($q) use ($request) {
                return $q->orWhere('employee_id', $request->employee)
                    ->orWhere('id', $request->employee);
            });
        }
        if ($request->department) {
            $employees->whereHas('department', function ($query) use ($request) {
                return $query->where('id', $request->department);
            });
        } elseif ($request->super_department) {
            $employees->whereHas('department', function ($query) use ($request) {
                return $query->where('super_department_id', $request->super_department);
            })
                ->orWhereIn('id', user()->managed_employees->pluck('id'));
        }

        if ($request->designation) {
            $employees->whereHas('designation', function ($query) use ($request) {
                return $query->where('id', $request->designation);
            });
        }
        $employees = $employees->paginate(1000);
        $scheduled_users = OvertimeScheduleDate::whereDate('date', $date)
            ->pluck('user_id')
            ->toArray();

        return view('attendance.overtime_schedule_date', [
            'date' => $date,
            'employees' => $employees,
            'scheduled_users' => $scheduled_users,
            'limit' => Setting::query()
                ->where('key', 'overtime_date_default_limit')
                ->first()?->value ?? 0,
            'all_overtime' => Setting::can_everyone_special_overtime(),
        ]);
    }
    function overtime_schedule_date_save($id, Request $request)
    {
        abort_if(!role_can('overtime_schedule') && !user()->admin_of_super_departments, 403);
        $check = OvertimeScheduleDate::where('user_id', $id)
            ->whereDate('date', $request->date)
            ->first();
        if ($check) {
            $check->delete();
            Toastr::warning('Schedule removed for this date!', 'Warning');
            return redirect()->back();
        }
        OvertimeScheduleDate::create([
            'user_id' => $id,
            'date' => $request->date
        ]);
        Toastr::success('Overtime schedule added successfully :)', 'Success');
        return redirect()->back();
    }
    function overtime_schedule_date_update_default_limit(Request $request)
    {
        abort_if(!role_can('overtime_schedule') && !user()->admin_of_super_departments, 403);
        $request->validate([
            'limit' => 'nullable|numeric|max:24'
        ]);
        $check = Setting::query()
            ->where('key', 'overtime_date_default_limit')
            ->first();
        if (!$check) {
            Setting::create([
                'key' => 'overtime_date_default_limit',
                'value' => $request->limit
            ]);
        } else {
            if ($request->limit) {
                $check->value = $request->limit ?? 0;
                $check->save();
            } else {
                $check->delete();
            }
        }
        Toastr::success('Overtime default limit updated successfully :)', 'Success');
        return redirect()->back();
    }
    function overtime_schedule_date_update_limit($user, $date, Request $request)
    {
        abort_if(!role_can('overtime_schedule') && !user()->admin_of_super_departments, 403);
        $request->validate([
            'limit' => 'nullable|numeric|max:24'
        ]);
        $check = \App\Models\OvertimeScheduleDate::whereDate('date', $date)
            ->where('user_id', $user)
            ->first(); //?->max_overtime_hours ?? 0;
        // dd(1, $user, $date, $check);
        if (!$check) {
            $check = OvertimeScheduleDate::create([
                'user_id' => $user,
                'date' => $date,
                'max_overtime_hours' => $request->limit ?? 0
            ]);
        } else {
            $check->max_overtime_hours = $request->limit ?? 0;
            $check->save();
        }
        // dd(2, $user, $date, $check);
        Toastr::success('Overtime limit updated successfully :)', 'Success');
        return redirect()->back();
    }

    function overtime_schedule_all_overtime(Request $request)
    {
        abort_if(!role_can('overtime_schedule') && !user()->admin_of_super_departments, 403);
        Setting::update_can_everyone_overtime($request->val == 1);
        Toastr::success('Overtime limit updated successfully :)', 'Success');
        return redirect()->back();
    }
    function overtime_schedule(Request $request)
    {
        abort_if(!role_can('overtime_schedule') && !user()->admin_of_super_departments, 403);
        $date = carbon($request->date);
        $employees = User::active_employees();
        if (user()->admin_of_super_departments) {
            $employees->whereHas('department', function ($query) use ($request) {
                return $query->whereIn('super_department_id', user()->admin_of_super_departments);
            });
        }

        if ($request->employee) {
            $employees->where(function ($q) use ($request) {
                return $q->orWhere('employee_id', $request->employee)
                    ->orWhere('id', $request->employee);
            });
        }
        if ($request->department) {
            $employees->whereHas('department', function ($query) use ($request) {
                return $query->where('id', $request->department);
            });
        } elseif ($request->super_department) {
            $employees->whereHas('department', function ($query) use ($request) {
                return $query->where('super_department_id', $request->super_department);
            })
                ->orWhereIn('id', user()->managed_employees->pluck('id'));
        }

        if ($request->designation) {
            $employees->whereHas('designation', function ($query) use ($request) {
                return $query->where('id', $request->designation);
            });
        }
        $employees = $employees->paginate(1000);
        $scheduled_users = OvertimeSchedule::whereDate('date', $date)
            ->pluck('user_id')
            ->toArray();

        return view('attendance.overtime_schedule', [
            'date' => $date,
            'employees' => $employees,
            'scheduled_users' => $scheduled_users,
            'limit' => Setting::query()
                ->where('key', 'overtime_default_limit')
                ->first()?->value ?? 0,
            'all_overtime' => Setting::can_everyone_overtime(),
        ]);
    }
    function overtime_schedule_save($id, Request $request)
    {
        abort_if(!role_can('overtime_schedule') && !user()->admin_of_super_departments, 403);
        $check = OvertimeSchedule::where('user_id', $id)
            ->whereDate('date', $request->date)
            ->first();
        if ($check) {
            $check->delete();
            Toastr::warning('Schedule removed for this date!', 'Warning');
            return redirect()->back();
        }
        OvertimeSchedule::create([
            'user_id' => $id,
            'date' => $request->date
        ]);
        Toastr::success('Overtime schedule added successfully :)', 'Success');
        return redirect()->back();
    }
    function overtime_schedule_update_default_limit(Request $request)
    {
        abort_if(!role_can('overtime_schedule') && !user()->admin_of_super_departments, 403);
        $request->validate([
            'limit' => 'nullable|numeric|max:24'
        ]);
        $check = Setting::query()
            ->where('key', 'overtime_default_limit')
            ->first();
        if (!$check) {
            Setting::create([
                'key' => 'overtime_default_limit',
                'value' => $request->limit
            ]);
        } else {
            if ($request->limit) {
                $check->value = $request->limit ?? 0;
                $check->save();
            } else {
                $check->delete();
            }
        }
        Toastr::success('Overtime default limit updated successfully :)', 'Success');
        return redirect()->back();
    }
    function overtime_schedule_update_limit($user, $date, Request $request)
    {
        abort_if(!role_can('overtime_schedule') && !user()->admin_of_super_departments, 403);
        $request->validate([
            'limit' => 'nullable|numeric|max:24'
        ]);
        $check = \App\Models\OvertimeSchedule::whereDate('date', $date)
            ->where('user_id', $user)
            ->first(); //?->max_overtime_hours ?? 0;
        // dd(1, $user, $date, $check);
        if (!$check) {
            $check = OvertimeSchedule::create([
                'user_id' => $user,
                'date' => $date,
                'max_overtime_hours' => $request->limit ?? 0
            ]);
        } else {
            $check->max_overtime_hours = $request->limit ?? 0;
            $check->save();
        }
        // dd(2, $user, $date, $check);
        Toastr::success('Overtime limit updated successfully :)', 'Success');
        return redirect()->back();
    }
    function note(Request $request)
    {
        abort_if(!(
            role_can('reports')
            || user()->admin_of_super_departments
            || role_can('reports.self.super_department')
            // || role_can('reports.self')
        ), 403);
        $date = carbon($request->date);
        $data = Attendance::with('employee.designation')
            ->whereDate('date', $date->format('Y-m-d'));
        if(
            !role_can('reports') &&
            user()->admin_of_super_departments
        ) {
            $data->whereHas('employee', function ($query) {
                return $query->whereHas('department', function ($query) {
                    return $query->whereIn('super_department_id', user()->admin_of_super_departments);
                });
            });
        }else
        if (
            !role_can('reports')
            && !role_can('reports.self.super_department')
            && role_can('reports.self')
            && user()->managed_employees->count() == 0
        ) {
            // dd(2);
            $data->where('employee_id', user()->id);
        } else
        if (
            !role_can('reports')
            && (
                role_can('reports.self.super_department')
                || user()->managed_employees->count() > 0
            )
        ) {
            $data = $data->whereHas('employee', function ($query) {
                return $query->whereHas('department', function ($query) {
                    return $query->where('super_department_id', user()->department?->super_department_id);
                })
                    ->orWhereIn('id', user()->managed_employees->pluck('id'));
            });
        }
        if ($request->employee) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('id', $request->employee);
            });
        }
        if ($request->department) {
            $data = $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('department_id', $request->department);
            });
        } elseif ($request->super_department) {
            $data = $data->whereHas('employee', function ($query) use ($request) {
                return $query->whereHas('department', function ($query) use ($request) {
                    return $query->where('super_department_id', $request->super_department);
                })
                    ->orWhereIn('id', user()->managed_employees->pluck('id'));
            });
        }
        if ($request->designation) {
            $data = $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('designation_id', $request->designation);
            });
        }

        $data = $data->paginate(1000);
        return view('attendance.note', [
            'data' => $data,
            'date' => $date
        ]);
    }
    function note_save($id, Request $request)
    {
        $request->validate([
            'notes' => 'max:2000'
        ]);
        // dd($request->all());
        $att = Attendance::findOrFail($id);
        $emp = User::findOrFail($att->employee_id);
        abort_if(
            (
                !(
                    role_can('attendance')
                    || role_can('attendance.self.super_department')
                )
                && (
                    !role_can('attendance')
                    && role_can('attendance.self.super_department')
                    && $emp->department?->super_department_id != user()->department?->super_department_id
                )
                && (
                    !role_can('attendance')
                    && user()->admin_of_super_departments
                    && !in_array($emp->department?->super_department_id, user()->admin_of_super_departments ?? [])
                )
            ),
            403
        );

        $att->notes = $request->notes ?? '';
        $att->notes_by = user()->id;
        $att->save();
        Toastr::success('Note added successfully :)', 'Success');
        return redirect()->back();
    }
    function outside_duty()
    {
        $user = user();
        abort_if($user->can_outside_attendence == 0 && !role_can('superadmin'), 403);
        if (!$user->shift) {
            $user->update(['shift_id' => Shift::initFirst()->id]);
        }
        return view('attendance.outside_duty', EmployeeCurrentPunchStatus::execute($user));
    }

    function outSideEntry(Request $request)
    {
        Attendance::validateOutSidePunch($request);
        $today = Carbon::today();
        $user = user();
        $check_approved = LeaveApplication::final_approved()
            ->where('employee_id', $user)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->first();

        if ($check_approved) {
            return ['leave' => true];
        } else {
            $data = $request->latitude . ',' . $request->longitude;
            if ($request->latitude == null) {
                $data = null;
            }
            abort_if(
                !$data
                    || !$user
                    || !$request->latitude
                    || !$request->longitude
                    || ($data === ",")
                    || ($user->can_outside_attendence == 0 && !$user->is_super_admin()),
                403
            );
            $in = InOutRecord::create([
                'user_id' => $user->id,
                'status' => 0,
                'date_time' => now(),
                'device_uid' => 'Punch from outside tab #',
                'added' => 1,
                'location' => $data,
                'unique_data' => now() . '-' . $user->id,
            ]);
            $emp_punch = \App\Actions\EmployeePunched\Execute::run($in);
            if($emp_punch instanceof Attendance) {
                // dispatch the EmployeePunchedJob again to check for half day fine
                $emp_punch->delete_attendnace_upon_late();
            }
            // EmployeePunchedJob::dispatch($in);
            return $emp_punch;
        }
        // $this->repository->outSideEntry($request, user());
    }
    function outSideOut(Request $request)
    {
        Attendance::validateOutSidePunch($request);
        $data = $request->latitude . ',' . $request->longitude;
        if ($request->latitude == null) {
            $data = null;
        }
        $user = user();
        abort_if(
            !$data
                || !$user
                || !$request->latitude
                || !$request->longitude
                || ($data === ",")
                || ($user->can_outside_attendence == 0 && !$user->is_super_admin()),
            403
        );
        $out = InOutRecord::create([
            'user_id' => $user->id,
            'status' => 1,
            'date_time' => now(),
            'device_uid' => 'Punch from outside tab #',
            'added' => 1,
            'location' => $data,
            'unique_data' => now() . '-' . $user->id,
        ]);
        $emp_punch = \App\Actions\EmployeePunched\Execute::run($out);
        if($emp_punch instanceof Attendance) {
            // dispatch the EmployeePunchedJob again to check for half day fine
            $emp_punch->delete_attendnace_upon_late();
        }
        return $emp_punch;
        // $this->repository->outSideOut($request, user());
    }
    function destroy(Attendance $attendance)
    {
        abort_if(!role_can('attendance'), 403);

        foreach ($attendance->used_in_out_records as $inout) {
            TouchedEmployeePunch::create([
                'user_id' => $attendance->employee_id,
                'date_time' => $inout->date_time
            ]);
        }

        $attendance->used_in_out_records()->delete();
        $attendance->delete();
        Toastr::success('Attendance deleted successfully :)', 'Success');
        return redirect()->back();
    }
    function gracetime(Request $request)
    {
        abort_if(!role_can('attendance.gracetime'), 403);
        if ($request->user && is_numeric($request->gracetime) && $request->gracetime >= 0) {
            $user = User::find($request->user);
            $user->grace_minutes = intval($request->gracetime);
            $user->save();
            return intval($request->gracetime);
        }
        return view('attendance.gracetime', [
            'users' => User::active_employees()->get()
        ]);
    }
    function attendance_shift_update(Attendance $attendance, Request $request)
    {
        // dd($request->all());
        $attendance->shift_id = $request->shift_id;
        $attendance->save();
        $inouts = $attendance->used_in_out_records;
        if ($inouts->count() > 0) {
            \App\Actions\EmployeePunched\Execute::run($inouts->first(), $attendance);
        }
        Toastr::success('Shift updated successfully :)', 'Success');
        return redirect()->back();
    }

    public function check_employee_schedule()
    {
        return EmployeeCurrentPunchStatus::execute(user());
    }

    public function manual_store(Request $request)
    {
        $check = Attendance::where('employee_id', $request->employee)
            ->whereDate('date', carbon($request->date)->format('Y-m-d'))
            ->first();
        if ($check) {
            Toastr::warning('Already punched!', 'Warning');
            return redirect()->back();
        }
        $in = InOutRecord::create([
            'user_id' => $request->employee,
            'status' => 0,
            'date_time' => carbon(
                carbon($request->date)->format('Y-m-d') . ' ' . $request->check_in
            ),
            'device_uid' => 'Manually added by #' . user()->id . ': ' . user()->name,
        ]);
        $out = InOutRecord::create([
            'user_id' => $request->employee,
            'status' => 1,
            'date_time' => carbon(
                carbon($request->date)->format('Y-m-d') . ' ' . $request->check_out
            ),
            'device_uid' => 'Manually added by #' . user()->id . ': ' . user()->name,
        ]);

        $check = \App\Actions\EmployeePunched\Execute::run($in);

        return response()->json([
            'success' => true,
            'message' => 'Attendance saved successfully',
            'data' => $check
        ]);
    }
    public function update_out_time(Request $request)
    {
        abort_if(!role_can('attendance.update.out-time'), 403);
        $request->validate([
            'emp_id' => 'required|exists:users,id',
            'out_time' => 'required|date'
        ]);
        // $emp = User::find($request->emp_id);
        $out = Carbon::parse($request->out_time)->format('Y-m-d H:i:s');
        $out = InOutRecord::create([
            'user_id' => $request->emp_id,
            'status' => 1,
            'date_time' => $out,
            'device_uid' => 'Manually added by #' . user()->id . ': ' . user()->name,
        ]);
        // return $out;
        $emp_punch = \App\Actions\EmployeePunched\Execute::run($out);
        return response()->json([
            'success' => true,
            'message' => 'Out time updated successfully',
            'data' => $emp_punch
        ]);
    }
}
