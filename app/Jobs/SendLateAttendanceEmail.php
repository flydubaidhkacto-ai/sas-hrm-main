<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\MailLog;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\LateAttendanceMail;
use App\Models\Setting;
use App\Services\FindEmployeeShiftByPunchDateTime;
use Carbon\Carbon;

class SendLateAttendanceEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Attendance $attendance) {}

    public function handle(): void
    {
        if ($this->attendance->late_notified_via_email_at) {
            return;
        }

        $employee = $this->attendance->employee;
        if (!$employee || !$employee->email) {
            return;
        }

        $minLateMinutes = Setting::where('key', 'reports.late.min_late_minutes')->first()?->value ?: 5;
        if (is_numeric($minLateMinutes)) {
            $minLateMinutes = intval($minLateMinutes);
        } else {
            $minLateMinutes = 5;
        }

        // Roster attendance must be checked against a real, non-display-only roster shift.
        // Do not send an alert based on a stale late flag or a fallback midnight shift.
        if ($this->attendance->is_in_roster) {
            $checkIn = $this->attendance->check_in ?: $this->attendance->worked_from;
            if (!$checkIn) {
                return;
            }

            try {
                $rosterShiftData = (new FindEmployeeShiftByPunchDateTime(
                    $employee,
                    carbon($checkIn),
                    $this->attendance
                ))->getData();
            } catch (\Throwable $e) {
                Log::warning('Skipping late email: roster shift could not be resolved', [
                    'attendance_id' => $this->attendance->id,
                    'error' => $e->getMessage(),
                ]);
                return;
            }

            if (empty($rosterShiftData['is_roster_day']) || empty($rosterShiftData['shift_start_at'])) {
                Log::warning('Skipping late email: no active working roster shift', [
                    'attendance_id' => $this->attendance->id,
                ]);
                return;
            }

            $actualLateSeconds = carbon($rosterShiftData['shift_start_at'])->diffInSeconds(carbon($checkIn), false);
            if ($actualLateSeconds <= ($minLateMinutes * 60)) {
                return;
            }

            // Ensure the email reports the verified roster lateness, not stale saved values.
            $this->attendance->late_time = gmdate('H:i:s', $actualLateSeconds);
            $shiftStart = $rosterShiftData['shift_start_at']->copy();
        } else {
            $shiftStart = $this->resolveShiftStart($employee);
        }

        $toEmails = [$employee->email];

        $ccEmails = collect();

        $hodEmails = $employee->managed_by
            ->where('is_active', true)
            ->where('email', '!=', null)
            ->pluck('email')
            ->unique()
            ->toArray();

        if (empty($hodEmails)) {
            $hodEmails = $this->resolveHodFromApprovalProcess($employee->id);
        }

        $ccEmails->push($hodEmails);

        $hrEmails = (array) config('hr.emails');
        $ccEmails->push($hrEmails);

        $mdEmails = $this->resolveMdFromApprovalProcess($employee->id);

        if (empty($mdEmails)) {
            $mdEmails = User::query()
                ->where('is_active', true)
                ->where('email', '!=', null)
                ->whereIn('role', ['M.D.', 'm.d.', 'MD', 'Md', 'md', 'Managing Director', 'managing director'])
                ->pluck('email')
                ->unique()
                ->toArray();
        }
        $ccEmails->push($mdEmails);

        $ccEmails = $ccEmails
            ->flatten()
            ->filter()
            ->unique()
            ->diff($toEmails)
            ->values()
            ->toArray();

        try {
            $subject = "Late Attendance Alert - {$employee->name} - " . carbon($this->attendance->date)->format('d M, Y');

            $mailLog = MailLog::create([
                'from_email' => config('mail.from.address'),
                'to_email'   => $toEmails,
                'cc_email'   => $ccEmails,
                'bcc_email'  => [],
                'subject'    => $subject,
                'module'     => 'attendance',
                'body'       => 'Late Attendance Alert email',
                'meta'       => [
                    'attendance_id' => $this->attendance->id,
                    'employee_id'   => $employee->id,
                    'type'          => 'late_attendance',
                    'late_time'     => $this->attendance->late_time,
                    'check_in'      => $this->attendance->check_in,
                ],
                'attachments' => [],
                'status'      => 'pending',
                'attempt'     => 1,
            ]);

            Mail::to($toEmails)
                ->cc($ccEmails)
                ->send(new LateAttendanceMail($this->attendance, $shiftStart, $minLateMinutes));

            $mailLog->update([
                'status'  => 'sent',
                'sent_at' => now(),
                'attempt' => 1,
            ]);

            $this->attendance->update([
                'late_notified_via_email_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send late attendance email', [
                'attendance_id' => $this->attendance->id,
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
            ]);

            if (isset($mailLog)) {
                $mailLog->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                    'attempt'       => 1,
                ]);
            }
        }
    }

    private function resolveHodFromApprovalProcess(int $employeeId): array
    {
        $approvalProcess = \DB::table('approval_processes')
            ->where('type', 'attendance_request')
            ->where('for_users', 'LIKE', '%' . $employeeId . '%')
            ->first();

        if (!$approvalProcess) {
            return [];
        }

        $firstApprover = \DB::table('approval_process_steps')
            ->where('approval_process_id', $approvalProcess->id)
            ->orderBy('step_order')
            ->first();

        if (!$firstApprover) {
            return [];
        }

        $hod = \DB::table('users')->where('id', $firstApprover->approver_id)->first();
        if ($hod && $hod->email && $hod->is_active) {
            return [$hod->email];
        }

        return [];
    }

    private function resolveMdFromApprovalProcess(int $employeeId): array
    {
        $approvalProcess = \DB::table('approval_processes')
            ->where('type', 'attendance_request')
            ->where('for_users', 'LIKE', '%' . $employeeId . '%')
            ->first();

        if (!$approvalProcess) {
            return [];
        }

        $lastApprover = \DB::table('approval_process_steps')
            ->where('approval_process_id', $approvalProcess->id)
            ->orderByDesc('step_order')
            ->first();

        if (!$lastApprover) {
            return [];
        }

        $md = \DB::table('users')->where('id', $lastApprover->approver_id)->first();
        if ($md && $md->email && $md->is_active) {
            return [$md->email];
        }

        return [];
    }

    /**
     * Resolve the shift start time dynamically from the employee's roster/schedule,
     * falling back to the employee's default shift if no roster/schedule is found.
     * This ensures lateness is calculated against the correct shift start time.
     */
    private function resolveShiftStart(User $employee): ?Carbon
    {
        $punchDateTime = carbon($this->attendance->check_in ?: $this->attendance->worked_from ?: now());

        try {
            $shiftFinder = new FindEmployeeShiftByPunchDateTime(
                $employee,
                $punchDateTime,
                $this->attendance
            );
            $shiftData = $shiftFinder->getData();

            if (isset($shiftData['shift_start_at']) && $shiftData['shift_start_at']) {
                $shiftStart = $shiftData['shift_start_at'];
                $shift = $shiftData['shift'] ?? null;

                if ($shift && $shift->is_night_shift) {
                    $shiftStart->subDay();
                }

                return $shiftStart;
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to resolve shift start via FindEmployeeShiftByPunchDateTime', [
                'attendance_id' => $this->attendance->id,
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Fallback: use the attendance's shift or employee's default shift
        if ($this->attendance->shift) {
            $shiftStart = carbon($this->attendance->date . ' ' . $this->attendance->shift->start);
            if ($this->attendance->shift->is_night_shift) {
                $shiftStart->subDay();
            }
            return $shiftStart;
        }

        if ($employee->shift) {
            $shiftStart = carbon($this->attendance->date . ' ' . $employee->shift->start);
            if ($employee->shift->is_night_shift) {
                $shiftStart->subDay();
            }
            return $shiftStart;
        }

        return null;
    }
}
