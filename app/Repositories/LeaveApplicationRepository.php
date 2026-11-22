<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use App\Interfaces\LeaveApplicationRepositoryInterface;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Services\Approval\CommonService;

class LeaveApplicationRepository implements LeaveApplicationRepositoryInterface
{
    public function apply(Request $request, User $logged_user, User $employee): ?LeaveApplication
    {
        // ---------- 1️⃣ Validation ----------
        $request->validate([
            'leave_type' => 'required|exists:leave_types,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'date' => 'nullable|date',
            'title' => 'required|max:255',
            'description' => 'nullable|max:2000',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'attachment' => 'nullable|file|max:10240',
        ]);

        // ---------- 2️⃣ Attendance Check ----------
        $from = carbon($request->start_date)->startOfDay();
        $to = carbon($request->end_date ?: $request->start_date)->endOfDay();

        if (config('app.customer') === 'asp') {
            Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$from, $to])
                ->delete();
            $att_rpy_date = false;
        } else {
            $att_rpy_date = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$from, $to])
                ->exists();
        }

        $is_hourly = $request->start_time && $request->end_time;

        if ($att_rpy_date && !$is_hourly) {
            throw new \Exception('The employee is present on this date range!');
        }
//        if (!$att_rpy_date && $is_hourly) {
//            throw new \Exception('Hourly leave requires employee to be present on the selected date.');
//        }

        // ---------- 3️⃣ Leave Type & Employee Authorization ----------
        $type = LeaveType::findOrFail($request->leave_type);

        if (!role_can('leave') && !$logged_user->admin_of_super_departments && !$type->is_for_all_user) {
            throw new \Exception('You are not authorized to apply this leave type');
        }

        $canApproveEmps = \App\Models\ApprovalProcess::canApproveEmployees('leave', $logged_user);
        if (
            (!$canApproveEmps || !$canApproveEmps['employees']->first(fn($emp) => $emp->id == $employee->id)) &&
            !role_can('leave') &&
            !$logged_user->admin_of_super_departments &&
            $employee->id != $logged_user->id
        ) {
            throw new \Exception('You are not authorized to apply for this employee!');
        }

        // ---------- 3.5️⃣ Annual Leave 90-Day Cooldown ----------
        // After an employee successfully submits an Annual Leave application (pending or approved),
        // they cannot apply for another Annual Leave until 90 full calendar days have elapsed
        // from the previous application's submission date. Rejected applications do not consume
        // the cooldown since they are not successful applications.
        if (strtolower($type->name) === 'annual leave') {
            $cooldownDays = config('services.leave_annual_cooldown_days', 90);

            $lastApplication = LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->whereIn('status', ['pending', 'approved'])
                ->orderByDesc('created_at')
                ->first();

            if ($lastApplication) {
                $lastDate = carbon($lastApplication->created_at ?? $lastApplication->start_date)->startOfDay();
                $eligibleDate = $lastDate->copy()->addDays($cooldownDays);

                if (today()->lt($eligibleDate)) {
                    throw new \Exception('Annual Leave is not available at this time. You can apply again after ' . $eligibleDate->format('d M, Y') . '.');
                }
            }
        }

        // ---------- 4️⃣ Start & End Date ----------
        if ($is_hourly) {
            $start_date = carbon(($request->hourly_date ?? $request->date) . ' ' . $request->start_time);
            $end_date = carbon(($request->hourly_date ?? $request->date) . ' ' . $request->end_time);
            if (!$start_date->isSameDay($end_date)) {
                throw new \Exception('Hourly leave must be within the same day.');
            }
        } else {
            $start_date = carbon($request->start_date);
            $end_date = $request->end_date ? carbon($request->end_date) : $start_date->copy();
            if ($end_date->year !== $start_date->year) {
                throw new \Exception('Start and end date must be in the same year.');
            }
        }

        // ---------- 5️⃣ Duration & Conflict Check ----------
        $duration = LeaveApplication::getDurationDays($start_date, $end_date, $employee, $is_hourly);

        if (!$is_hourly) {
            foreach ($duration['dates'] as $date) {
                $exist = LeaveApplication::where('start_date', '<=', $date)
                    ->where('is_hourly', false)
                    ->where('end_date', '>=', $date)
                    ->where('employee_id', $employee->id)
                    ->whereIn('status', ['approved', 'pending'])
                    ->exists();
                if ($exist) {
                    throw new \Exception('Leave has already been approved or pending within this timeframe!');
                }
            }

            // ---------- 6️⃣ Leave Balance Check ----------
            // Skip balance check for leave types that do not affect leave balance (e.g., Work From Home)
            if ($type->affects_leave_balance) {
                $balance = LeaveBalance::employee_balance($employee->id, $request->leave_type, $start_date->year);
                if ($balance['left'] < $duration['total']) {
                    throw new \Exception('Your leave balance is not sufficient.');
                }
            }

            if ($duration['total'] <= 0) {
                throw new \Exception('Leave duration cannot be less than or equal to zero.');
            }
        }

        // ---------- 7️⃣ Save Leave ----------
        $leave = new LeaveApplication();
        $leave->employee_id = $employee->id;
        $leave->leave_type_id = $request->leave_type;
        $leave->type = $type->name;
        $leave->start_date = $start_date->format('Y-m-d');
        $leave->end_date = $end_date->format('Y-m-d');
        $leave->title = $request->title;
        $leave->description = $request->description ?? '';
        $leave->is_hourly = $is_hourly;
        $leave->start_time = $request->start_time;
        $leave->end_time = $request->end_time;

        if ($file = $request->file('attachment')) {
            $leave->attachment = $file->store('leaves');
            $leave->attachment_name = $file->getClientOriginalName();
        }

        $leave->published_for_view = !config('services.leave_has_publisher', false);
        $leave->save();

        // ---------- 8️⃣ Station Leave ----------
        if ($request->is_station_leave == 1) {
            $leave->update([
                'is_station_leave' => true,
                'station' => $request->station,
                'station_from_date' => $request->station_from_date ? carbon($request->station_from_date)->format('Y-m-d') : null,
                'station_to_date' => $request->station_to_date ? carbon($request->station_to_date)->format('Y-m-d') : null,
                'station_present_address' => $request->station_present_address,
                'address_during_out_of_station' => $request->address_during_out_of_station,
                'station_contact_number' => $request->station_contact_number,
                'station_alternative_number' => $request->station_alternative_number,
                'reason_for_station_leave' => $request->reason_for_station_leave,
                'station_remarks_by_employee' => $request->station_remarks_by_employee,
            ]);
        }

        // ---------- 9️⃣ Auto-Approve for Super Admin ----------
        if ($logged_user->is_super_admin()) {
            $leave->update(['status' => 'approved']);

            app(CommonService::class)->onApprove($leave, $logged_user, [
                'is_final' => 1,
                'next_approver' => null,
                'module' => 'leave'
            ]);

            $leave->approvedAll();


            return $leave;
        } else {

            app(CommonService::class)->onApply($leave, $employee, [
                'module' => 'leave'
            ]);
        }

        return $leave;
    }
}
