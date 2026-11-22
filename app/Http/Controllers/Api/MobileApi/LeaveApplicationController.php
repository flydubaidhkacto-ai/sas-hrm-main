<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Classes\ApiResponseClass;
use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Interfaces\LeaveApplicationRepositoryInterface;
use App\Models\ApprovalProcess;
use App\Models\User;
use App\Repositories\LeaveApplicationRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeaveApplicationController extends Controller
{
    protected $repository;
    function __construct()
    {
        $this->repository = new LeaveApplicationRepository();
    }
    function index(Request $request)
    {
        $user = $request->auth_user;
        $data = LeaveApplication::query();
        if (in_array($request->filter, ['pending', 'approved', 'rejected'])) {
            $data->where('status', $request->filter);
        }
        if ($request->month) {
            $data->where(function ($q) use ($request) {
                $q->where('start_date', '<=', Carbon::parse($request->month . '-01')->endOfMonth())
                    ->where('end_date', '>=', Carbon::parse($request->month . '-01')->startOfMonth());
            });
        }
        // filter limitations according to access
        $canApproveEmps = ApprovalProcess::canApproveEmployees('leave', $user);
        if(
            role_can('leave', $user)
        )
        {
            // if()
        }else
        if($canApproveEmps && $canApproveEmps['employees'])
        {
            $data->whereIn('employee_id', [...$canApproveEmps['employees']->pluck('id'), $user->id]);
        }
        else {
            $data->whereHas('employee', function ($query) use  ($user) {
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
                ->with('leave_type', 'employee')
                ->paginate(10000, ['*'], 'page', $request->page ?? 1),
            'Leave Applications Fetched Successfully'
        );
    }
    function types()
    {
        return ApiResponseClass::sendResponse(
            LeaveType::all(),
            'Leave Types Fetched Successfully'
        );
    }

    function balance(Request $request)
    {
        $balance = LeaveBalance::employee_balance($request->employee_id ?? $request->auth_user->id, $request->type, $request->year ?? date('Y'));
        return ApiResponseClass::sendResponse(
            $balance,
            'Leave Balance Fetched Successfully'
        );
    }
    function store(Request $request)
    {
        try {
            $user = User::findOrFail($request->employee_id);
            $leave = $this->repository->apply($request, $request->auth_user, $user);
            if (!$leave) {
                throw new \Exception('Failed to apply leave');
            }
            return ApiResponseClass::sendResponse(
                $leave,
                'Leave Applied Successfully'
            );
        } catch (\Throwable $th) {
            throw $th;
            // return ApiResponseClass::sendResponse(null, $th->getMessage(), 422);
        }
    }

    function show(Request $request, $id)
    {
        $user = $request->auth_user;
        $leave = LeaveApplication::with(['employee.department.super_department', 'employee.designation', 'leave_type'])->latest();

        $leave = $leave->findOrFail($id);
        $approvalSteps = $leave->getApprovalStepsApi($user->id) ?: [];

        $types = LeaveType::orderBy('name')->get();
        $prevLeaves = [];
        foreach($types as $type) {
            $prevLeaves[] = LeaveApplication::query()
                ->where('id', '<', $leave->id)
                ->where('leave_type_id', $type->id)
                ->where('status', 'approved')
                ->where('is_hourly', false)
                ->where('employee_id', $leave->employee_id)
                ->latest()
                ->with([
                    'employee',
                    'leave_type',
                ])
                ->first();
        }

        return ApiResponseClass::sendResponse([
            'data' => $leave,
            'can_approve' => $leave->can_approve('leave', $user),
            'balance' => LeaveBalance::employee_balance($leave->employee_id, $leave->leave_type_id, Carbon::parse($leave->start_date)->year),
            'approvalSteps' => $approvalSteps,
            'all_types' => $types,
            'prev_leaves' => $prevLeaves,
        ], 'Leave Application Fetched Successfully');
    }
}
