<?php

namespace App\Repositories\Services;

use App\Actions\EmployeePunched;
use App\Models\InOutRecord;
use App\Models\LeaveApplication;
use App\Repositories\Interfaces\AttendanceRepositoryInterface;
use Carbon\Carbon;

class AttendanceRepository extends BaseRepository implements AttendanceRepositoryInterface
{
    // Define your methods here

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
            $in = InOutRecord::create([
                'user_id' => $user->id,
                'status' => 1,
                'date_time' => now(),
                'device_uid' => 'Punch from outside tab #',
                'added' => 1,
                'location' => $data,
                'unique_data' => now() . '-' . $user->id,
            ]);
            $emp_punch = EmployeePunched::execute($in);
            // EmployeePunchedJob::dispatch($in);
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
            'status' => 0,
            'date_time' => now(),
            'device_uid' => 'Punch from outside tab #',
            'added' => 1,
            'location' => $data,
            'unique_data' => now() . '-' . $user->id,
        ]);
        $emp_punch = EmployeePunched::execute($out);

        return $emp_punch;
    }
}
