<?php

namespace App\Http\Controllers;

use App\Actions\EmployeePunched;
use App\Models\ApprovalProcess;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\InOutRecord;
use App\Models\User;
use App\Services\Approval\CommonService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class AttendanceRequestController extends Controller
{
    function index(Request $request)
    {
        $data = AttendanceRequest::query();
        $from = carbon($request->from_date ?: now()->subDays(30));
        $to = carbon($request->to_date ?: now()->endOfDay());
        $type = strtolower($request->type ?: 'pending');
        abort_if(!(
            role_can('attendance-requests')
            || role_can('attendance-requests.apply')
            || \App\Models\ApprovalProcess::canApprove('attendance_request', user())
        ), 403);

        $data->where(function ($q) use ($from, $to) {
            $q->where(function ($q2) use ($from, $to) {
                $q2->whereBetween('created_at', [
                    $from->format('Y-m-d') . ' 00:00:00', $to->format('Y-m-d') . ' 23:59:59'
                ]);
            });
        });

        $canApproveEmps = ApprovalProcess::canApproveEmployees('attendance_request', user());
        if (
        role_can('attendance-requests')
        ) {
            // if()
        } else
            if ($canApproveEmps && $canApproveEmps['employees']) {
                $data->whereIn('user_id', [...$canApproveEmps['employees']->pluck('id'), user()->id]);
            } else {
                $data->whereHas('employee', function ($query) use ($request) {
                    return $query->where('id', user()->id);
                });
            }

        if ($request->employee) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('id', $request->employee);
            });
        }
        if ($request->department) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('department_id', $request->department);
            });
        } elseif ($request->super_department) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->whereHas('department', function ($query) use ($request) {
                    return $query->where('super_department_id', $request->super_department);
                })
                    ->orWhereIn('id', user()->managed_employees->pluck('id'));
            });
        }
        if ($request->designation) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('designation_id', $request->designation);
            });
        }

        $data = $data->where('status', $type)
            ->orderBy('updated_at', 'desc')
            ->get();
        if ($type === 'pending') {
            $data = AttendanceRequest::pending_for_approval($data);
        }

        return view('attendance_request.index', [
            'requests' => $data,
            'type' => $type,
            'from' => $from,
            'to' => $to,
        ]);
    }

    function create()
    {
        return view('attendance_request.create');
    }

    function pre_verify_request(array $data, User $user)
    {
        if (!$user->shift) {
            Toastr::warning('Please set your shift first!', 'Warning');
            return redirect()->back();
        }

        $att = Attendance::where('employee_id', $user->id)
            ->whereDate('date', carbon($data['check_in_date'] ?? $data['check_out_date'])->format('Y-m-d'))
            ->first();
        $inouts = collect();
        if ($att) {
            $inouts = $att->used_in_out_records;
        }
        // check odd or even number
        $count = $inouts->count();
        // if ($count % 2 == 0) {
        //     //dd('currently out');
        //     if ($type === 'OUT') {
        //         Toastr::warning('Already checked out. You cannot request to check out!', 'Warning');
        //         return redirect()->back();
        //     }
        // } else {
        //     // dd('currently in');
        //     if ($type === 'IN') {
        //         Toastr::warning('Already checked in. You cannot request to check in!', 'Warning');
        //         return redirect()->back();
        //     }
        // }
        // check if the time already exists or not in the in-out records
        // foreach ($inouts as $inout) {
        //     foreach ($timestamps as $key => $timestamp) {
        //         if ($inout->date_time->format('Y-m-d H:i:s') == $timestamp->format('Y-m-d H:i:s')) {
        //             Toastr::warning($inout->date_time->format('Y-m-d H:i:s') . " already exists. You cannot request for this time!", 'Warning');
        //             return redirect()->back();
        //         }
        //     }
        // }
        return null;
    }

    function preview(Request $request)
    {
        abort_if(
            !role_can('attendance-requests.apply') && !role_can('attendance-requests'), 403
        );
        $request->validate([
            'type' => 'required|in:IN,OUT,IN_OUT',
            'notes' => 'nullable|max:2000|string',
            'employee' => 'required|exists:users,id',
        ]);
        $user = User::findOrFail($request->employee);
        if (!role_can('attendance-requests')) $user = user();
        if (!$user->shift) {
            Toastr::error('Employee does not have any shift!', 'Error');
            return redirect()->back();
        }

        $type = $request->type;
        $timestamps = [];
        if ($request->type === 'IN_OUT') {
            $data = $request->validate([
                'check_in_date' => 'required|date',
                'check_in' => 'required|date_format:H:i',
                'check_out_date' => 'required|date|after_or_equal:check_in_date',
                'check_out' => 'required|date_format:H:i|different:check_in',
            ]);
            $timestamps['check_in'] = carbon($data['check_in_date'] . ' ' . $data['check_in']);
            $timestamps['check_out'] = carbon($data['check_out_date'] . ' ' . $data['check_out']);
        } else if ($request->type === 'IN') {
            $data = $request->validate([
                'check_in_date' => 'required|date',
                'check_in' => 'required|date_format:H:i',
            ]);
            $timestamps['check_in'] = carbon($data['check_in_date'] . ' ' . $data['check_in']);
        } else {
            $data = $request->validate([
                'check_out_date' => 'required|date',
                'check_out' => 'required|date_format:H:i',
            ]);
            $timestamps['check_out'] = carbon($data['check_out_date'] . ' ' . $data['check_out']);
        }
        $data['notes'] = $request->notes;
        $check = $this->pre_verify_request($data, $user);
        if ($check) return $check;
        $data['shift_id'] = $user->shift_id;
        $attendanceReq = AttendanceRequest::create([
            'user_id' => $user->id,
            'date' => ($timestamps['check_in'] ?? $timestamps['check_out'])->format('Y-m-d'),
            'punch_in' => ($timestamps['check_in'] ?? null),
            'punch_out' => ($timestamps['check_out'] ?? null),
            'shift_id' => $data['shift_id'],
            'notes' => $data['notes'],
            'duty_type' => $request->duty_type,
        ]);

        if (user()->is_super_admin()) {

            $attendanceReq->update(['status' => 'approved']);

            app(CommonService::class)->onApprove($attendanceReq, user(), [
                'is_final' => 1,
                'next_approver' => null,
                'module' => 'attendance_request'
            ]);

            $attendanceReq->approvedAll();

        } else {
            app(CommonService::class)->onApply($attendanceReq, user(), [
                'module' => 'attendance_request'
            ]);
        }


        Toastr::success('Attendance request added successfully!', 'Success');
        if ($request->duty_type) {
            // duty_type
            return redirect()->back();
        }
        return redirect()->route('attendance-requests');
    }

    function work_from_home_req()
    {
        return view('attendance_request.work-from-home');
    }

    function show($id)
    {
        $attendance_request = AttendanceRequest::findOrFail($id);

        if (user()->role === 'employee' && !role_can('attendance_request')) {
            $canApproveEmps = ApprovalProcess::canApproveEmployees('attendance_request', user());
            $emp = $attendance_request->employee;

            $approvableIds = [];

            if ($canApproveEmps && isset($canApproveEmps['employees'])) {
                $approvableIds = $canApproveEmps['employees']->pluck('id')->toArray();
            }

            $approvableIds[] = user()->id; // Add self

            abort_if(
                !in_array($emp ?->id, $approvableIds)
            && !in_array($emp ?->department ?->super_department_id, user()->admin_of_super_departments ?: [])
            && $leave->employee_id != user()->id,
            403
        );
    }

        return view('attendance_request.view', [
            'attendance_request' => $attendance_request,
        ]);
    }

    function destroy($id)
    {
        $req = AttendanceRequest::findOrFail($id);
        abort_if(!role_can('attendance-requests'), 403);

        $req->delete();
        app(CommonService::class)->onDelete($req, user(), [
            'module' => 'attendance_request'
        ]);

        Toastr::success('Attendance request deleted successfully!', 'Success');
        return redirect()->back();
    }
}
