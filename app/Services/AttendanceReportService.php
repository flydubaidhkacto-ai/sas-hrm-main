<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\RosterItem;
use App\Models\User;
use Carbon\Carbon;
use App\Helpers\DurationHelper;

class AttendanceReportService
{
    /**
     * Generate attendance report
     */
    public function attendanceData(array $payload): \Generator
    {

        $from = Carbon::parse($payload['from']);
        $to = Carbon::parse($payload['to']);
        $employee = $payload['employee'] ?? 'all';
        $attendanceType = $payload['attendanceType'] ?? 'all';
        $shiftType = $payload['shiftType'] ?? 'all';
        $superDepartment = $payload['superDepartmentId'] ?? 'all';

        /*
        =========================================
        1️⃣ Employees
        =========================================
        */
        $employees = User::active_employees()
            ->when($employee !== 'all', fn($q) => $q->where('users.id', $employee))

            // SUPER DEPARTMENT FILTER
            ->when($superDepartment !== 'all', function ($q) use ($superDepartment) {
                $q->whereHas('department', function ($subQuery) use ($superDepartment) {
                    $subQuery->where('super_department_id', $superDepartment);
                });
            })

            //  JOINS FOR ORDERING
            ->leftJoin('departments', 'departments.id', '=', 'users.department_id')
            ->leftJoin('super_departments', 'super_departments.id', '=', 'departments.super_department_id')
            ->leftJoin('designations', 'designations.id', '=', 'users.designation_id')

            // ORDERING BY DATABASE ORDER
            ->orderBy('super_departments.order', 'asc')
            ->orderBy('departments.order', 'asc')
            ->orderBy('designations.order', 'asc')
            ->orderBy('users.name', 'asc')

            // ⚠️ VERY IMPORTANT
            ->select('users.*')
            ->with(['designation', 'department.super_department', 'shift'])
            ->cursor();

        /*
        =========================================
        2️⃣ Date range
        =========================================
        */
        $dates = generateDateRange($from, $to);

        /*
        =========================================
        3️⃣ Attendance preload (🔥 FIXED)
        =========================================
        */
        $attendances = [];

        Attendance::with(['roster'])
            ->whereBetween('date', [$from, $to])
            ->orderByDesc('is_in_roster')   // ⭐ roster first
            ->orderByDesc('got_late_check_in') // ⭐ late priority
            ->orderByDesc('id') // ⭐ latest fallback
            ->chunk(5000, function ($rows) use (&$attendances) {

                foreach ($rows as $a) {

                    $date = Carbon::parse($a->date)->format('Y-m-d');
                    $key = $a->employee_id . '_' . $date;

                    // keep first best record
                    if (!isset($attendances[$key])) {
                        $attendances[$key] = $a;
                    }
                }
            });


        /*
        =========================================
        4️⃣ Roster preload
        =========================================
        */
        $rosterMap = [];

        
     RosterItem::with(['roster', 'shift'])
        ->where(function ($query) {
           $query->whereDoesntHave('shift')
            ->orWhereHas('shift', function ($shiftQuery) {
                $shiftQuery->where('display_only', false);
            });
    })
    ->whereBetween('date', [$from, $to])

            ->chunk(5000, function ($rows) use (&$rosterMap) {
                foreach ($rows as $r) {
                    $date = Carbon::parse($r->date)->format('Y-m-d');
                    $key = $r->employee_id . '_' . $date;
                    $rosterMap[$key] = $r;
                }
            });

        /*
        =========================================
        5️⃣ Requests preload
        =========================================
        */
        $requests = [];

        AttendanceRequest::whereBetween('date', [$from, $to])
            ->chunk(5000, function ($rows) use (&$requests) {
                foreach ($rows as $r) {
                    $date = Carbon::parse($r->date)->format('Y-m-d');
                    $key = $r->user_id . '_' . $date;
                    $requests[$key] = $r;
                }
            });

        /*
        =========================================
        6️⃣ Color map
        =========================================
        */
        $colorMap = [
            'late' => ['status' => 'color:red;font-weight:bold;', 'in' => 'color:red;'],
            'absent' => ['status' => 'color:red;font-weight:bold;', 'in' => 'color:red;'],
            'ontime' => ['status' => 'color:green;font-weight:bold;', 'in' => 'color:green;'],
        ];

        /*
        =========================================
        7️⃣ MAIN LOOP
        =========================================
        */
        foreach ($employees as $employee) {

            $joinDate = $employee->join_date
                ? Carbon::parse($employee->join_date)->startOfDay()
                : null;

            foreach ($dates as $date) {

                $dateCarbon = Carbon::parse($date)->startOfDay();

                if ($joinDate && $dateCarbon->lt($joinDate)) {
                    continue;
                }

                $key = $employee->id . '_' . $date;

                $attendance = $attendances[$key] ?? null;
                $rosterItem = $rosterMap[$key] ?? null;
                $request = $requests[$key] ?? null;

                /*
                =========================================
                ⭐ SHIFT TYPE DETECT
                =========================================
                */
                $isRoster = ($attendance ?->is_in_roster == 1) || $rosterItem;
                $currentShiftType = $isRoster ? 'roster' : 'regular';

                if ($shiftType !== 'all' && $shiftType !== $currentShiftType) {
                    continue;
                }

                /*
                =========================================
                ⭐ RESOLVE SHIFT
                =========================================
                */
                [$shiftName, $shiftStartRaw, $shiftEndRaw] =
                    $this->resolveShift($employee, $attendance, $rosterItem);

                $shiftStartCarbon = $shiftStartRaw ? Carbon::parse($shiftStartRaw) : null;
                $shiftEndCarbon = $shiftEndRaw ? Carbon::parse($shiftEndRaw) : null;

                /*
                =========================================
                ⭐ STATUS RESOLVE (🔥 UPDATED)
                =========================================
                */
                $statusData = AttendanceStatusResolver::resolve(
                    $employee,
                    $dateCarbon,
                    $attendance,
                    $rosterItem,
                    $shiftStartCarbon,
                    $shiftEndCarbon
                );

                if ($attendanceType !== 'all'
                    && strtolower($statusData['status']) !== strtolower($attendanceType)) {
                    continue;
                }

                /*
                =========================================
                ⭐ TIME CALCULATION
                =========================================
                */
                $timeCalc = DurationHelper::calculateAttendanceTime(
                    $shiftStartCarbon,
                    $shiftEndCarbon,
                    $attendance
                );

                $workedMinutes = $timeCalc['worked_minutes'];
                $expectedMinutes = $timeCalc['expected_minutes'];
                $missedMinutes = $timeCalc['missed_minutes'];
                $missedTime = $timeCalc['missed_time_text'];

                /*
                =========================================
                ⭐ FORMAT TIMES
                =========================================
                */
                $checkInCarbon = $attendance ?->check_in
                    ? Carbon::parse($attendance->check_in)
                    : null;

                $checkOutCarbon = $attendance ?->check_out
                    ? Carbon::parse($attendance->check_out)
                    : null;

                $shiftStart = $shiftStartCarbon ?->format('h:i A');
                $shiftEnd = $shiftEndCarbon ?->format('h:i A');
                $checkIn = $checkInCarbon ?->format('h:i A');
                $checkOut = $checkOutCarbon ?->format('h:i A');

                /*
                =========================================
                ⭐ COLOR
                =========================================
                */
                $statusKey = strtolower($statusData['status'] ?? '');
                $statusColor = $colorMap[$statusKey]['status'] ?? '';
                $inColor = $colorMap[$statusKey]['in'] ?? '';

                $outColor = ($checkOutCarbon && $shiftEndCarbon && $checkOutCarbon->lt($shiftEndCarbon))
                    ? 'color:red;'
                    : '';

                /*
                =========================================
                ⭐ YIELD
                =========================================
                */
                yield [
                    'employee' => $employee,
                    'date' => $dateCarbon->format('d M Y'),
                    'attendance' => $attendance,
                    'status' => $statusData,
                    'shift_type' => $currentShiftType,
                    'shift_name' => $shiftName,
                    'shift_start' => $shiftStart,
                    'shift_end' => $shiftEnd,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'worked_minutes' => $workedMinutes,
                    'expected_minutes' => $expectedMinutes,
                    'time_missed_minutes' => $missedMinutes,
                    'time_missed_readable' => $missedTime,
                    'status_color' => $statusColor,
                    'in_color' => $inColor,
                    'out_color' => $outColor,
                    'request_status' => $request ?->status,
                    'request_type' => $request ?->request_type,
                    'request_note' => $request ?->notes,
                ];
            }
        }
    }

    /**
     * Resolve shift info
     */
    private function resolveShift($employee, $attendance, $rosterItem): array
    {
        if ($rosterItem && $rosterItem->shift) {
            return [
                $rosterItem->shift->name,
                $rosterItem->shift->start,
                $rosterItem->shift->end,
            ];
        }

        if ($attendance ?->is_in_roster && $attendance->roster) {
        return [
            $attendance->roster->name,
            $attendance->roster->start,
            $attendance->roster->end,
        ];
    }

        return [
            $employee->shift->name ?? null,
            $employee->shift->start ?? null,
            $employee->shift->end ?? null,
        ];
    }
}
