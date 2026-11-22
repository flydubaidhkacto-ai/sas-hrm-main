<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Roster;
use App\Models\RosterShift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WfhAttendanceService
{
    /**
     * Calculate full working hours from the original roster shift.
     */
    public function calculateShiftDuration(RosterShift $shift): string
    {
        $start = Carbon::parse('2000-01-01 ' . $shift->start);
        $end = Carbon::parse('2000-01-01 ' . $shift->end);

        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $seconds = (int) $start->diffInSeconds($end);

        return gmdate('H:i:s', $seconds);
    }

    /**
     * Create WFH attendance without biometric punches.
     * Never overwrite existing attendance.
     */
    public function createAttendance(
        Roster $roster,
        int $employeeId,
        string $date,
        int $rosterShiftId
    ): Attendance {
        return DB::transaction(function () use (
            $roster,
            $employeeId,
            $date,
            $rosterShiftId
        ) {
            // Serialize WFH creation for the same employee.
            $employee = \App\Models\User::query()
                ->whereKey($employeeId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$roster->is_active) {
                throw ValidationException::withMessages([
                    'wfh_assignments' => 'WFH requires an active roster.',
                ]);
            }

            $assigned = $roster->items()
                ->where('employee_id', $employee->id)
                ->whereDate('date', $date)
                ->where('roster_shift_id', $rosterShiftId)
                ->exists();

            if (!$assigned) {
                throw ValidationException::withMessages([
                    'wfh_assignments' => 'Original roster shift was not found.',
                ]);
            }

            $shift = RosterShift::query()
                ->whereKey($rosterShiftId)
                ->where('display_only', false)
                ->firstOrFail();

            // Never overwrite genuine or manually entered attendance.
            $existingRecords = Attendance::query()
    ->where('employee_id', $employeeId)
    ->whereDate('date', $date)
    ->lockForUpdate()
    ->get();

if ($existingRecords->count() > 1) {
    throw ValidationException::withMessages([
        'wfh_assignments' =>
            "Multiple attendance records exist for employee {$employeeId} on {$date}.",
    ]);
}

$existing = $existingRecords->first();

            if ($existing) {
    // Allow re-saving an unchanged, automatically generated WFH attendance.
    if (
        $existing->roster_id == $roster->id
        && $existing->roster_shift_id == $rosterShiftId
        && $existing->duty_type === 'Work From Home'
        && $existing->system_notes === 'WFH attendance generated from roster.'
        && $existing->added_manually_by === null
        && $existing->check_in === null
        && $existing->check_out === null
        && $existing->worked_from === null
        && $existing->worked_to === null
        && $existing->notes === null
        && $existing->notes_by === null
        && $existing->late_fine_id === null
        && $existing->early_out_fine_id === null
        && $existing->used_in_out_records()->doesntExist()
        && $existing->roster_items()->doesntExist()
    ) {
        return $existing;
    }

    // Never overwrite genuine or manually modified attendance.
    throw ValidationException::withMessages([
        'wfh_assignments' =>
            "Attendance already exists for employee {$employeeId} on {$date}.",
    ]);
}

            return Attendance::create([
    'employee_id' => $employeeId,
    'date' => $date,
    'shift_id' => $employee->shift_id,
    'roster_shift_id' => $rosterShiftId,
    'roster_id' => $roster->id,
                'is_in_roster' => true,
                'duty_type' => 'Work From Home',
                'working_time' => $this->calculateShiftDuration($shift),

                'check_in' => null,
                'check_out' => null,
                'worked_from' => null,
                'worked_to' => null,

                'got_late_check_in' => false,
                'did_early_check_in' => false,
                'did_early_check_out' => false,
                'late_time' => null,
                'late_fine_id' => null,
                'early_out_fine_id' => null,

                'system_notes' => 'WFH attendance generated from roster.',
            ]);
        });
    }
      /**
     * Cancel only attendance generated automatically by WFH.
     * Never delete biometric or manually modified attendance.
     */
    public function cancelAttendance(
        Roster $roster,
        int $employeeId,
        string $date
    ): void {
        DB::transaction(function () use ($roster, $employeeId, $date) {
            // Lock employee to coordinate with WFH creation.
            \App\Models\User::query()
                ->whereKey($employeeId)
                ->lockForUpdate()
                ->firstOrFail();

            $attendance = Attendance::query()
                ->where('roster_id', $roster->id)
                ->where('employee_id', $employeeId)
                ->whereDate('date', $date)
                ->where('duty_type', 'Work From Home')
                ->lockForUpdate()
                ->first();

            // Already cancelled: nothing to delete.
            if (!$attendance) {
                return;
            }

            // Only remove attendance created by our WFH service.
            $safe =
                $attendance->system_notes === 'WFH attendance generated from roster.'
                && $attendance->added_manually_by === null
                && $attendance->check_in === null
                && $attendance->check_out === null
                && $attendance->worked_from === null
                && $attendance->worked_to === null
                && $attendance->notes === null
                && $attendance->notes_by === null
                && $attendance->late_fine_id === null
                && $attendance->early_out_fine_id === null
                && $attendance->used_in_out_records()->doesntExist()
                && $attendance->roster_items()->doesntExist();

            if (!$safe) {
                throw ValidationException::withMessages([
                    'wfh_assignments' =>
                        "WFH attendance for employee {$employeeId} on {$date} " .
                        "has linked or modified records and cannot be cancelled automatically.",
                ]);
            }

            $attendance->delete();
        });
    }
}