<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Classes\ApiResponseClass;
use App\Models\ApprovalProcess;
use App\Models\User;
use Carbon\Carbon;
use App\Services\Approval\CommonService;

class AttendanceRequestController extends Controller
{
    function index(Request $request)
    {

        $user = $request->auth_user;

        $data = AttendanceRequest::query()
            ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59'
                ]);
            });

        if (in_array($request->filter, ['pending', 'approved', 'rejected'])) {
            $data->where('status', $request->filter);
        }

        $canApproveEmps = ApprovalProcess::canApproveEmployees('attendance_request', $user);
        if(
            role_can('attendance-requests', $user)
        )
        {
            // if()
        }else
        if($canApproveEmps && $canApproveEmps['employees'])
        {
            $data->whereIn('user_id', [...$canApproveEmps['employees']->pluck('id'), $user->id]);
        }
        else {
            $data->whereHas('employee', function ($query) use ($user) {
                return $query->where('id', $user->id);
            });
        }

        if ($request->employee) {
            $data->whereHas('employee', function ($query) use ($request) {
                return $query->where('id', $request->employee);
            });
        }

        return ApiResponseClass::sendResponse(
            $data->orderBy('id', 'desc')
                ->with('employee')
                ->paginate(10000, ['*'], 'page', $request->page ?? 1),
            'Attendance Requests Fetched Successfully'
        );
    }


    function pre_verify_request(array $data, string $type, array $timestamps, $user)
    {
        if (!$user->shift) {
            return abort(422, 'Please set your shift first!');
        }
        return null;
    }


    function store(Request $request)
    {
        $user = $request->auth_user;
        $req_user = $request->employee_id ? User::findOrFail($request->employee_id) : $user;
        $request->validate([
            'type' => 'required|in:IN,OUT,IN_OUT',
            'notes' => 'nullable|max:2000|string',
        ]);
        $type = $request->type;
        $timestamps = [];
        if ($request->type === 'IN_OUT') {
            $data = $request->validate([
                'check_in' => 'required|date',
                'check_out' => 'required|date|after:check_in',
            ]);
            $timestamps['check_in'] = carbon($data['check_in']);
            $timestamps['check_out'] = carbon($data['check_out']);
        } else if ($request->type === 'IN') {
            $data = $request->validate([
                'check_in' => 'required|date',
            ]);
            $timestamps['check_in'] = carbon($data['check_in']);
        } else {
            $data = $request->validate([
                'check_out' => 'required|date',
            ]);
            $timestamps['check_out'] = carbon($data['check_out']);
        }
        $data['notes'] = $request->notes;
        $check = $this->pre_verify_request($data, $type, $timestamps, $req_user);
        if ($check) return $check;
        $data['shift_id'] = $req_user->shift_id;
        $attendanceRequest = AttendanceRequest::create([
            'user_id' => $req_user->id,
            'date' => ($timestamps['check_in'] ?? $timestamps['check_out'])->format('Y-m-d'),
            'punch_in' => ($timestamps['check_in'] ?? null),
            'punch_out' => ($timestamps['check_out'] ?? null),
            'shift_id' => $data['shift_id'],
            'notes' => $data['notes'],
        ]);

        app(CommonService::class)->onApply($attendanceRequest, $user, [
            'module' => 'attendance_request'
        ]);

        return ApiResponseClass::sendResponse(
            $attendanceRequest,
            'Attendance Request Added Successfully'
        );
    }

    function show(Request $request, $id)
    {
        $user = $request->auth_user;
        $get_data = AttendanceRequest::with(['employee.department.super_department', 'employee.designation']);

        if (
            !role_can('attendance-requests', $user)
            && role_can('attendance-requests.apply', $user)
        ) { //individual view
            $get_data->whereHas('employee', function ($query) use ($user) {
                return $query->where('id', $user->id);
            });
        }

        $data = $get_data->findOrFail($id);

        $approvalSteps = $data->getApprovalStepsApi($user->id) ?: [];

        return ApiResponseClass::sendResponse([
            'data' => $data,
            'approvalSteps' => $approvalSteps
        ], 'Attendance Request Fetched Successfully');
    }
}
