<!DOCTYPE html>
<html>
<body style="margin:0; padding:20px; background:#f5f6fa; font-family:Arial, Helvetica, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="left">
                <table width="100%" cellpadding="0" cellspacing="0"
                       style="background:#ffffff; border-radius:8px; border:1px solid #e0e0e0;">
                    <tr>
                        <td style="padding:30px;">
                            <h2 style="margin:0 0 20px 0; font-size:22px; color:#e74c3c;">
                                Late Attendance Notification
                            </h2>

                            <p style="margin:0 0 15px 0; font-size:15px; color:#333; line-height:1.7;">
                               The HRM Portal has automatically generated a Late Reporting Alert, and your
                               attendance for today has accordingly been recorded as <strong>Late.</strong>
                            </p>

                            <p style="margin:0 0 15px 0; font-size:15px; color:#333; line-height:1.7;">
                              You are required to provide a valid explanation for your late reporting by
                              submitting the necessary application through the HRM Portal within six (6)
                              hours from your actual reporting time.
                            </p>

                            <p style="margin:0 0 15px 0; font-size:15px; color:#333; line-height:1.7;">
                              Please note that failure to submit the application within the stipulated
                              timeframe may result in the applicable penalty in accordance with the
                              Company’s attendance policy.
                            </p>

                            <p style="margin:0; font-size:15px; color:#333; line-height:1.7;">
                              Your timely compliance is expected.<br>
                              HR Team<br>
                              SAS - DAC.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="background:#f9f9f9; border-radius:6px; border:1px solid #eee;">
                                <tr>
                                    <td style="padding:15px;">
                                        <p style="margin:0 0 8px 0; font-size:14px; color:#333;">
                                            <strong>Employee:</strong> {{ $employee->name ?? 'N/A' }}
                                        </p>
                                        <p style="margin:0 0 8px 0; font-size:14px; color:#333;">
                                            <strong>Employee ID:</strong> {{ $employee->employee_id ?? '-' }}
                                        </p>
                                        <p style="margin:0 0 8px 0; font-size:14px; color:#333;">
                                            <strong>Department:</strong> {{ $employee->department->name ?? 'N/A' }}
                                        </p>
                                        <p style="margin:0 0 8px 0; font-size:14px; color:#333;">
                                            <strong>Designation:</strong> {{ $employee->designation->name ?? 'N/A' }}
                                        </p>
                                        <p style="margin:0 0 8px 0; font-size:14px; color:#333;">
                                            <strong>Attendance Date:</strong> {{ $attendance->date ? carbon($attendance->date)->format('d M, Y') : 'N/A' }}
                                        </p>
                                        <p style="margin:0 0 8px 0; font-size:14px; color:#333;">
                                            <strong>Regular Shift Start Time:</strong> {{ $shiftStart ? $shiftStart->format('h:i A') : 'N/A' }}
                                        </p>
                                        <p style="margin:0 0 8px 0; font-size:14px; color:#333;">
                                            <strong>Actual Check-in Time:</strong> {{ $attendance->check_in ? carbon($attendance->check_in)->format('h:i A') : ($attendance->worked_from ? carbon($attendance->worked_from)->format('h:i A') : 'N/A') }}
                                        </p>
                                        <p style="margin:0 0 8px 0; font-size:14px; color:#e74c3c;">
                                            <strong>Status:</strong> Late Attendance
                                        </p>
                                        @if($attendance->late_time)
                                        @php
                                            $lateParts = explode(':', $attendance->late_time);
                                            $lateHours = ltrim($lateParts[0] ?? '0', '0') ?: '0';
                                            $lateMinutes = $lateParts[1] ?? '00';
                                            $lateSeconds = $lateParts[2] ?? '00';
                                            $formattedLateTime = "{$lateHours}H :{$lateMinutes}M: {$lateSeconds}S";
                                        @endphp
                                        <p style="margin:0 0 8px 0; font-size:14px; color:#333;">
                                            <strong>Late By:</strong> {{ $formattedLateTime }}
                                        </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <div style="margin-top:30px; border-top:1px solid #eee; padding-top:15px;
                                        font-size:12px; color:#888;">
                                This is an automated message from
                                <strong>{{ config('app.name') }}</strong>.<br>
                                Please do not reply to this email.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
