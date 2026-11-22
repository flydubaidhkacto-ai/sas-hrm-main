<?php

namespace App\Mail;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LateAttendanceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $attendance;
    public $employee;
    public $shiftStart;
    public $lateCutoff;
    public $minLateMinutes;

    public function __construct(Attendance $attendance, $shiftStart = null, $minLateMinutes = null)
    {
        $this->attendance = $attendance;
        $this->employee = $attendance->employee;
        $this->shiftStart = $shiftStart;
        $this->minLateMinutes = $minLateMinutes;
    }

    public function build()
    {
        $employeeName = $this->employee->name ?? 'N/A';
        $date = $this->attendance->date ? carbon($this->attendance->date)->format('d M, Y') : 'N/A';
        $subject = "Late Attendance Alert - {$employeeName} - {$date}";

        return $this->subject($subject)
            ->view('email.late_attendance')
            ->with([
                'attendance' => $this->attendance,
                'employee' => $this->employee,
                'shiftStart' => $this->shiftStart,
                'minLateMinutes' => $this->minLateMinutes,
            ]);
    }
}
