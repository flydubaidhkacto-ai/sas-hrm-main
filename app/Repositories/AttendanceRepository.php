<?php

namespace App\Repositories;

use App\Actions\EmployeePunched;
use App\Models\InOutRecord;
use App\Models\LeaveApplication;
use App\Interfaces\AttendanceRepositoryInterface;
use App\Models\Attendance;
use App\Models\LiveLocation;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    // Define your methods here
    public function all()
    {
        return InOutRecord::limit(10)->get();
    }

    public function outSideEntry($request, $user): array|object|null
    {

        $today = Carbon::today();

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
            $min = Setting::duplicate_punch_minutes() ?: 0;
            if (
                InOutRecord::where('user_id', $user->id)
                    ->where('date_time', '<=', now()->addMinutes($min))
                    ->where('date_time', '>=', now()->subMinutes($min))
                    ->exists()
            ) {
                abort(403, "Cannot punch before {$min} minute delay!");
            }

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
            // create a liveLocation record if eligible. will be treated as starting point
            if(
                has_module('live-location-tracking')
                && $request->auth_user?->live_location_track
            )
            {
                $isIn = true;
                if(
                    $emp_punch instanceof Attendance
                ) {
                    $isIn = !($emp_punch->used_in_out_records()->count() % 2 === 0);
                }
                LiveLocation::create([
                    'user_id' => $user->id,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'ui_id' => 1,
                    'device_name' => $request->header('user_agent') ?:  $request->userAgent(),
                    'platform' => 'mobile',
                    'date_time' => now(),
                    'status' => $isIn ? 'start' : 'stop',
                ]);
            }
            return $emp_punch;
        }
    }

    public function outSideOut($request, $user): array|object|null
    {
        // dd($request->auth_user->id);

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
    }
}
