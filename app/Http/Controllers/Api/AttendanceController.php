<?php

namespace App\Http\Controllers\Api;

use App\Actions\EmployeeCurrentPunchStatus;
use App\Classes\ApiResponseClass;
use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\InOutRecord;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(protected AttendanceRepositoryInterface $repository) {}

    public function outSideEntry(Request $request)
    {
        Attendance::validateOutSidePunch($request);

        try {
            DB::beginTransaction();

            $response = $this->repository->outSideEntry($request, $request->auth_user);

            if (!$response) {
                DB::rollBack();
                return ApiResponseClass::sendResponse(null, 'No duty found', 400);
            }

            DB::commit();

            $attendanceData = $response instanceof Attendance ? new AttendanceResource($response) : $response;
            return ApiResponseClass::sendResponse($attendanceData, 'Outside entry recorded successfully.', 201);
        } catch (\Exception $ex) {
            DB::rollBack();
            return ApiResponseClass::rollback($ex, 'An error occurred while processing outside entry.');
        }
    }

    public function outSideOut(Request $request)
    {
        Attendance::validateOutSidePunch($request);

        try {
            DB::beginTransaction();

            $response = $this->repository->outSideOut($request, $request->auth_user);

            if ($response === null) {
                DB::rollBack();
                return ApiResponseClass::sendResponse(null, 'No duty found', 400);
            }

            DB::commit();

            $attendanceData = $response instanceof Attendance ? new AttendanceResource($response) : $response;
            return ApiResponseClass::sendResponse($attendanceData, 'Outside entry recorded successfully.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponseClass::throw($e, 'An error occurred while processing outside entry.');
        }
    }

    function in_outs(Request $request)
    {
        return ApiResponseClass::sendResponse(
            InOutRecord::query()
                ->where('user_id', $request->auth_user->id)
                ->orderBy('date_time', 'desc')
                ->whereDate('date_time', $request->date ?? Carbon::now()->format('Y-m-d'))
                ->get(),
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
        );
    }
}