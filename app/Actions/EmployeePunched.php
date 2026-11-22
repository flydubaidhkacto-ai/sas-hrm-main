<?php
namespace App\Actions;

use App\Models\Attendance;
use App\Models\InOutRecord;
use App\Models\OvertimeScheduleDate;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\TouchedEmployeePunch;
use App\Models\User;
use App\Services\FindEmployeeShiftByPunchDateTime;
use App\Actions\StudentPunched;
use App\Models\Fine;

class EmployeePunched
{
    static function execute(InOutRecord $in_out_record, Attendance|null $attendance = null)
    {
        // ignore if already admin touched attendance of this attendance
        if($in_out_record->employee?->is_student())
        {
            return StudentPunched::execute($in_out_record);
        }
        if(
            TouchedEmployeePunch::query()->where([
                'user_id' => $in_out_record->user_id,
                'date_time' => $in_out_record->date_time->format('Y-m-d H:i:s')
            ])->exists()
            || !$in_out_record->employee?->shift
        )
        {
            return null;
        }

        $employee_punch = new self($in_out_record, $attendance);
        if(!$employee_punch->employee) return null;
        $att = $employee_punch->handle();
        if($att && $att instanceof Attendance && config('app.customer') === 'asp')
        {
            // delete auto absent fines if present later
            // this is done to avoid duplicate fines for same date
            // when attendance is created later by admin
            Fine::asp_delete_auto_absent_fines_if_present_later (
                [$att->date],
                $att->employee_id
            );
        }
        return $att;
    }

    /**
     * TODO: TESTS
     * 1. Done: Check by on-same day shift
     * 2. Done: Check by on-different day shift
     * 3. Check by on-same day shift with different date overtime
     * 4. same day shift with same date overtime
     * 5. different day shift with same date overtime
     * 6. same day shift with different date overtime
     *
     * shift starts: 16:00
     * shift ends: 00:00
     * overtime will end at: 12:00
     */
    protected ?User $employee = null;
    protected ?Attendance $attendance = null;
    protected ?InOutRecord $in_out_record = null;
    protected ?FindEmployeeShiftByPunchDateTime $shift_data = null;
    protected ?Shift $found_shift = null;

    function __construct(InOutRecord $in_out_record, Attendance|null $attendance = null)
    {
        $this->in_out_record = $in_out_record;
        $this->employee = $in_out_record->employee;
        $this->attendance = $attendance;
    }

    function handle(): Attendance|null|array
    {
        // check if roster exist for this date/timeframe
        $this->shift_data = new FindEmployeeShiftByPunchDateTime($this->employee, $this->in_out_record->date_time->copy(), $this->attendance);
        $shiftData = $this->shift_data->getData();

        // Step 1: Create attendance and sync related in_out_records
        if($shiftData['is_roster_day'])
        {
            $this->process_by_roster();
        }else
        {
            $this->process_by_shift();
        }
        if(!$this->attendance) return null; // dd($this->in_out_record, $this->shift_data);

        // Step 2: Calculate working time
        return CalculateWorkingTimeOfAttendance::execute($this->attendance, false, $this->shift_data);
        // return $this->send_notification_to_admin();
    }
    function process_by_shift()
    {
        $shiftData = $this->shift_data->getData();
        $this->found_shift = $shiftData['shift'];
        // check if the worker shift have the working day
        if(!$this->found_shift) return null;
        // dump($this->found_shift);
        if(
            !$this->found_shift->{strtolower( $this->in_out_record->date_time->format('l') )}
            && !(
                OvertimeScheduleDate::where('user_id', $this->employee->id)
                    ->whereDate('date', $this->attendance ? $this->attendance->date : $this->in_out_record->date_time->format('Y-m-d'))
                    ->first()
                || Setting::can_everyone_special_overtime()
            )
        )
        {
            return null;
        }
        $this->processAttendanceForShift();
    }
    function processAttendanceForShift()
    {
        $shiftData = $this->shift_data->getData();
        $inOuts = InOutRecord::where('user_id', $this->employee->id)
            ->where('date_time', '>=', $shiftData['check_in_after'])
            ->where('date_time', '<=', $shiftData['check_out_before'])
            ->orderBy('date_time', 'asc')
            ->get();
        // dd($shiftData, $inOuts->toArray());
        if(!$this->attendance)
        {
            $find = Attendance::where('employee_id', $this->employee->id)
                ->whereDate('date', $shiftData['shift_start_at']->format('Y-m-d'))
                ->first();
            if(!$find)
            {
                $find = Attendance::create([
                    'employee_id' => $this->employee->id,
                    'date' => $shiftData['shift_start_at']->format('Y-m-d'),
                    'shift_id' => $shiftData['shift']->id,
                    'check_in' => $this->in_out_record->date_time,
                    'worked_from' => $this->in_out_record->date_time,
                    'working_time' => 0,
                ]);
            }
            $this->attendance = $find;
        }
        if($this->attendance)
        {
            $this->attendance->used_in_out_records()->sync(
                $inOuts->pluck('id')
            );
        }
        // if(!$this->attendance) dd($this->attendance);
    }
    function process_by_roster()
    {
        $shiftData = $this->shift_data->getData();
        $rosterItems = $shiftData['roster_items'];

        $inOuts = InOutRecord::where('user_id', $this->employee->id)
            ->where('date_time', '>=', $shiftData['check_in_after'])
            ->where('date_time', '<=', $shiftData['check_out_before'])
            ->orderBy('date_time', 'asc')
            ->get();
        // dd($inOuts, $rosterItems, $shiftData);
        if(!$this->attendance)
        {
            $find = Attendance::where('employee_id', $this->employee->id)
                ->whereDate('date', $shiftData['shift_start_at']->format('Y-m-d'))
                ->first();
            if(!$find && $rosterItems->count() > 0)
            {
                $find = Attendance::create([
                    'employee_id' => $this->employee->id,
                    'date' => $shiftData['shift_start_at']->format('Y-m-d'),
                    'shift_id' => $rosterItems->first()->roster_shift_id,
                    'check_in' => $this->in_out_record->date_time,
                    'worked_from' => $this->in_out_record->date_time,
                    'working_time' => 0,
                ]);
            }
            $this->attendance = $find;
        }
        if($this->attendance)
        {
            $this->attendance->used_in_out_records()->sync(
                $inOuts->pluck('id')
            );
        }
        // if(!$this->attendance) dd($this->attendance);
    }

    function send_notification_to_admin()
    {
        // send notification to admin
        $attendance = Attendance::query()
            ->whereHas('used_in_out_records', function ($q) {
                $q->where('in_out_record_id', $this->in_out_record->id);
            })
            ->first();
        // dump($attendance, $in_out_record->toArray());dd(1);

        if(
            $attendance
            // && !str_contains($attendance->system_notes ?: '', 'ssms_processed_for_admin: 1')
        ) // if not added to attendance yet
        {
            $message = "";
            $phone = company_data()['company_phone'] ?? null;
            $data = CalculateWorkingTimeOfAttendance::execute($attendance, true, $this->shift_data);
            $isFirstCheckin = true;
            $lastInTime = null;
            $isCheckedOut = false;
            $lastCheckoutTime = null;
            foreach ($data['mappedInOuts'] ?? [] as $key => $value)
            {
                $message = "";
                $in = $value[0];
                $out = $value[1];

                // just entered
                if ($isFirstCheckin) {
                    $message = "Employee {$attendance->employee->name} (ID: {$attendance->employee->employee_id}) has checked in at {$in->date_time->format('h:i A')}";
                    $isFirstCheckin = false;
                    $lastInTime = $in->date_time;
                    $isCheckedOut = false;
                }else
                // came back after out
                if(!$isFirstCheckin && $in) {
                    // if(!$in->date_time) dd($in);
                    $workTime = $lastCheckoutTime
                        ? $in->date_time->diff($lastCheckoutTime)->format('%H:%I')
                        : 'unknown time';
                    $message = "Employee {$attendance->employee->name} (ID: {$attendance->employee->employee_id}) has returned at {$in->date_time->format('h:i A')} after a break of {$workTime}";
                    $lastInTime = null;
                }
                // going out
                if ($out) {
                    $timeOut = $in ? $out->date_time->diff($in->date_time)->format('%H:%I') : 'unknown time';
                    $message = "Employee {$attendance->employee->name} (ID: {$attendance->employee->employee_id}) has checked out at {$out->date_time->format('h:i A')} after working for {$timeOut}";
                    $isCheckedOut = true;
                    $lastCheckoutTime = $out->date_time;
                }
            }
            if($message && $phone)
            {
                SMSApi::send($phone, $message);
            }
        }
        return $attendance;
    }
}
