<?php

namespace App\Jobs;

use App\Actions\SMSApi;
use App\Models\Attendance;
use App\Models\InOutRecord;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendAttendanceRelatedSMS implements ShouldQueue
{
    use Queueable;
    public $company_phone = null;
    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $for,
        public bool $is_to_company,
        public ?InOutRecord $punch = null,
        public ?Attendance $attendance = null
    ) {
        /**
         * $for: late, in, out, duty_summary
         */
        $this->company_phone = company_data()['phone_number'] ?? null;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $permission = $this->attendance->employee->rel_to_sms_notifications()
            ->pluck('types')->toArray();

        if (!in_array($this->for, $permission, true)) {
            return;
        }

        if (!$this->company_phone && $this->is_to_company) {
            return; // exit if no company phone number added
        }
        if ($this->for == 'late' && $this->attendance?->got_late_check_in) {
            $this->late_sms();
        }
        if ($this->for == 'duty_summary') {
            $this->duty_summary_sms();
        }
        if ($this->for == 'in' || $this->for == 'first_in') {
            $this->in_sms();
        }
        if ($this->for == 'out') {
            $this->out_sms();
        }
    }
    function duty_summary_sms()
    {
        $day = Carbon::parse($this->attendance->date)->format('d M, Y');
        $workingTime = $this->attendance->working_time;
        $overTime = $this->attendance->overtime_time;
        $breakTime = $this->attendance->breaktime;
        // dd($this->attendance, $this->attendance->working_time, $workingTime, $overTime, $breakTime);
        if ($this->is_to_company) {
            $message = "{$this->attendance->employee->name} today's duty summary\n Working Time: $workingTime minutes\n Overtime: $overTime minutes\n Break Time: $breakTime minutes\n Check In: {$this->attendance->check_in}\n Check Out: {$this->attendance->check_out}";
            SMSApi::send($this->company_phone, $message, true);
        } else {
            $message = "Dear " . (company_data()['name'] ?? config('app.name')) . " employee {$this->attendance->employee->name},\n your today's duty summary\n Working Time: $workingTime minutes\n Overtime: $overTime minutes\n Break Time: $breakTime minutes\n Check In: {$this->attendance->check_in}\n Check Out: {$this->attendance->check_out}";
            if ($this->attendance->employee->phone_number) SMSApi::send($this->attendance->employee->phone_number, $message, true);
        }
    }
    function in_sms()
    {
        $day = $this->punch->date_time->format('d M, Y');
        if ($this->is_to_company) {
            $message = "{$this->attendance->employee->name} has checked in at {$this->punch->date_time->format('h:i A')} for the duty of {$day}.";
            SMSApi::send($this->company_phone, $message, true);
        } else {
            $message = "Dear " . (company_data()['name'] ?? config('app.name')) . " employee {$this->attendance->employee->name},\n you have checked in at {$this->punch->date_time->format('h:i A')} for the duty of {$day}.";
            if ($this->attendance->employee->phone_number) SMSApi::send($this->attendance->employee->phone_number, $message, true);
        }
    }
    function out_sms()
    {
        $day = $this->punch->date_time->format('d M, Y');
        if ($this->is_to_company) {
            $message = "{$this->attendance->employee->name} has checked out at {$this->punch->date_time->format('h:i A')} for the duty of {$day}.";
            SMSApi::send($this->company_phone, $message, true);
        } else {
            $message = "Dear " . (company_data()['name'] ?? config('app.name')) . " employee {$this->attendance->employee->name},\n you have checked out at {$this->punch->date_time->format('h:i A')} for the duty of {$day}.";
            if ($this->attendance->employee->phone_number) SMSApi::send($this->attendance->employee->phone_number, $message, true);
        }
    }
    function late_sms()
    {
        $day = Carbon::parse($this->attendance->date)->format('d M, Y');
        // late to company
        $total_late = Carbon::parse($this->attendance->late_time ?? '00:00:00')->format('H:i');
        if ($this->is_to_company) {
            $message = "{$this->attendance->employee->name} is late by $total_late minutes for the duty of {$day}.";
            SMSApi::send($this->company_phone, $message, true);
        } else {
            $message = "Dear " . (company_data()['name'] ?? config('app.name')) . " employee {$this->attendance->employee->name},\n you are late by $total_late minutes for the duty of {$day}.";
            if ($this->attendance->employee->phone_number) SMSApi::send($this->attendance->employee->phone_number, $message, true);
        }
    }
}
