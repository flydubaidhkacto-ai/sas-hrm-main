<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Models\StationLeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use App\Interfaces\StationLeaveApplicationRepositoryInterface;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Services\Approval\CommonService;

class StationLeaveApplicationRepository implements StationLeaveApplicationRepositoryInterface
{
    public function apply(array $data, User $logged_user, User $employee): ?StationLeaveApplication
    {
        $from = carbon($data['start_date'])->startOfDay();
        $to = carbon($data['end_date'] ?? $data['start_date'])->endOfDay();

        // Attendance check
        if (config('app.customer') === 'asp') {
            Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$from, $to])
                ->delete();
        } else {
            $exists = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$from, $to])
                ->exists();

            if ($exists) {
                throw new \Exception('The employee is present on this date range!');
            }
        }

        // Authorization check
        $canApproveEmps = \App\Models\ApprovalProcess::canApproveEmployees('leave', $logged_user);


        if (
            (!$canApproveEmps ||
                !$canApproveEmps['employees']->contains('id', $employee->id))
            && !role_can('leave')
            && !$logged_user->admin_of_super_departments
            && $employee->id !== $logged_user->id
        ) {
            throw new \Exception('You are not authorized to apply for this employee!');
        }

        $start_date = \Carbon\Carbon::parse($data['start_date'])->format('Y-m-d');
        $end_date   = \Carbon\Carbon::parse($data['end_date'])->format('Y-m-d');

        $exist = StationLeaveApplication::where('employee_id', $employee->id)
            ->whereIn('status', ['approved', 'pending'])
            ->where(function ($q) use ($start_date, $end_date) {
                $q->whereBetween('start_date', [$start_date, $end_date])
                    ->orWhereBetween('end_date', [$start_date, $end_date])
                    ->orWhere(function ($q) use ($start_date, $end_date) {
                        $q->where('start_date', '<=', $start_date)
                            ->where('end_date', '>=', $end_date);
                    });
            })
            ->exists();

        if ($exist) {
            throw new \Exception(
                'Station leave has already been approved or pending within this timeframe!'
            );
        }

        $stationLeave = new StationLeaveApplication();

        $stationLeave->start_date = carbon($data['start_date'])->format('Y-m-d');
        $stationLeave->end_date = carbon($data['end_date'])->format('Y-m-d');

        $stationLeave->employee_id = $employee->id;
        $stationLeave->leave_id = null;

        // File upload (controller passes file separately)
        if (!empty($data['attachment_path'])) {
            $stationLeave->attachment = $data['attachment_path'];
            $stationLeave->attachment_name = $data['attachment_name'];
        }

        $stationLeave->duration_count = 0 ?? null;
        $stationLeave->published_for_view = !config('services.leave_has_publisher', false);

        $stationLeave->station = $data['station'];
        $stationLeave->present_address = $data['present_address'];
        $stationLeave->address_during_out_of_station = $data['address_during_out_of_station'];
        $stationLeave->contact_number = $data['contact_number'];
        $stationLeave->alternative_number = $data['alternative_number'] ?? null;
        $stationLeave->reason = $data['reason'];
        $stationLeave->remarks_by_employee = $data['remarks_by_employee'] ?? null;

        $stationLeave->created_by = $logged_user->id;

        $stationLeave->save(); // ✅ MUST

        // Approval flow
        if ($logged_user->is_super_admin()) {

            $stationLeave->update(['status' => 'approved']);

            app(CommonService::class)->onApprove($stationLeave, $logged_user, [
                'is_final' => 1,
                'next_approver' => null,
                'module' => 'station_leave'
            ]);

            $stationLeave->approvedAll();

        } else {
            app(CommonService::class)->onApply($stationLeave, $employee, [
                'module' => 'station_leave'
            ]);
        }


        return $stationLeave;
    }
}

