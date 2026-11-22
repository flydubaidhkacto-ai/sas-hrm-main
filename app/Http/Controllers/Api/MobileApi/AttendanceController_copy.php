<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Actions\EmployeeCurrentPunchStatus;
use App\Actions\GetCalendarByUSer;
use App\Classes\ApiResponseClass;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Repositories\AttendanceRepository;
use App\Actions\EmployeePunched\FindEmployeeShiftByPunchDateTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    public function outSidePunch(Request $request)
    {
        $message = Attendance::validateOutSidePunch($request, true);
        if ($message) {
            return $message;
        }
        try {
            DB::beginTransaction();
            $response = (new AttendanceRepository)->outSideEntry($request, $request->auth_user);
            if (!$response) {
                DB::rollBack();
                return ApiResponseClass::sendResponse(null, 'No duty found', 400);
            }

            DB::commit();
            return ApiResponseClass::sendResponse(([
                'attendance' => $response,
                'status_data' => EmployeeCurrentPunchStatus::execute($request->auth_user)
            ]), 'Outside entry recorded successfully.', 201);
        } catch (\Exception $ex) {
            DB::rollBack();
            if (config('app.debug')) return throw $ex;
            return ApiResponseClass::rollback($ex, 'An error occurred while processing outside entry.');
        }
    }


    // public function outSideOut(Request $request)
    // {
    //     Attendance::validateOutSidePunch($request);

    //     try {
    //         DB::beginTransaction();

    //         $response = $this->repository->outSideEntry($request, $request->auth_user);

    //         if ($response === null) {
    //             DB::rollBack();
    //             return ApiResponseClass::sendResponse(null, 'No duty found', 400);
    //         }

    //         DB::commit();

    //         return ApiResponseClass::sendResponse(new AttendanceResource($response), 'Outside entry recorded successfully.', 201);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return ApiResponseClass::throw($e, 'An error occurred while processing outside entry.');
    //     }
    // }

    function in_outs(Request $request)
    {
        $currendDate = FindEmployeeShiftByPunchDateTime::getData($request->auth_user, now());
        $inouts = [];
        $attendance = null;
        if ($currendDate && ($currendDate['shift_start_at'])) {
            $attendance = Attendance::where('employee_id', $request->auth_user->id)
                ->where('date', $currendDate['date']->format('Y-m-d'))
                ->first();
            if ($attendance) {
                $inouts = $attendance->used_in_out_records()
                    ->latest()->get();
            }
        }
        return ApiResponseClass::sendResponse(
            [
                'shift_data' => $currendDate,
                'attendance' => $attendance,
                'inouts' => $inouts,
            ],
            'Attendance Retrive Successfully',
            200
        );
    }
    public function inOutRecords(Request $request)
    {
        $formData = $request->validate([
            'to'   => 'sometimes|nullable|date',
            'from' => 'sometimes|nullable|date'
        ]);

        $current_date = Carbon::now();
        $before_7_days = Carbon::now()->subDays(7);

        $to   = $formData['to'] ?? $current_date;
        $from = $formData['from'] ?? $before_7_days;

        $attendance_records = Attendance::where('worked_from', '<=', $to)
            ->where('worked_to', '>=', $from)
            ->get();

        return ApiResponseClass::sendResponse(AttendanceResource::collection($attendance_records), 'Attendance Retrive Successfully', 200);
    }
    function attendance_status(Request $request)
    {
        return ApiResponseClass::sendResponse(
            EmployeeCurrentPunchStatus::execute($request->auth_user),
            'Attendance Retrive Successfully',
            200
        ); //EmployeeCurrentPunchStatus::execute($request->auth_user);
    }
    function calendar(Request $request)
    {
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));
        $user = $request->auth_user;

        // return Cache::remember('calendar_' . $user->id . '_' . $year, 60 * 30, function () use ($request, $user, $year) {
        $events = GetCalendarByUSer::execute($user, $month, $year);
        return ApiResponseClass::sendResponse($events, 'Attendance Retrive Successfully', 200);
        // });
    }
}
