<?php

namespace App\Jobs;

use App\Helpers\StatusHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

use App\Services\AttendanceReportService;
use App\Services\AttendanceReportGenerateService;
use App\Services\Notification\NotificationService;
use App\Helpers\AttendanceReportMailHelper;
use App\Models\SuperDepartment;

class GenerateAttendanceReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Job timeout & retry
     */
    public $timeout = 0;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $payload) {}

/**
 * Execute the job.
 */
public
function handle(): void
{
    ini_set('memory_limit', '1024M');
    set_time_limit(0);

    //  config values
    // =====================================
    $path = rtrim(config('reports.attendance.path'), '/');
    $keepDays = config('reports.attendance.keep_days', 2);
    $mailTo = config('reports.attendance.mail_to');

    //  date range from constructor
    // =====================================
    // $from = $this->from->copy()->startOfDay();
    //$to = $this->to->copy()->endOfDay();

    $data = $this->payload;

    $from = Carbon::parse($data['from'])->startOfDay();
    $to = Carbon::parse($data['to'])->endOfDay();

    $reportType = $data['reportType'];
    $attendanceTypes = $data['attendanceTypes'];
    $employee = $data['employee'];
    $shiftType = $data['shiftType'];
    $superDepartmentId = $data['superDepartmentId'];
    $reportBladeName = $data['reportBladeName'];


    // =====================================
// LOAD SUPER DEPARTMENTS DYNAMICALLY
// =====================================

    if ($superDepartmentId === 'all') {

        $superDepartments = SuperDepartment::select('id', 'name')->ordered()->get();

    } else {

        $superDepartments = SuperDepartment::select('id', 'name')
            ->where('id', $superDepartmentId)
            ->get();
    }

    $shiftTypeLabel = in_array($shiftType, ['roster', 'regular'])
        ? $shiftType
        : "";

    $reportService = app(AttendanceReportService::class);
    $generateService = app(AttendanceReportGenerateService::class);


    // attachments array
    $attachments = [];

    foreach ($superDepartments as $superDepartment) {

        $superDepartmentId = $superDepartment->id;
        $superDepartmentName = $superDepartment->name;

        // LOOP PER Attendance Types
        // =====================================
        foreach ($attendanceTypes as $type) {

            // ---------- title ----------
            $label = $type ? $type : 'all';


            $cleanSuperDept = str_replace(' ', '_', strtolower($superDepartmentName));
            $reportTitle = "{$superDepartmentName} {$reportType} {$shiftTypeLabel} {$label} Attendance Report";
            $reportName = "{$cleanSuperDept}_{$reportType}_{$shiftTypeLabel}_{$label}_attendance_report";

            $dateRange = $from->format('F d, Y') === $to->format('F d, Y')
                ? $from->format('F d, Y')
                : $from->format('F d, Y') . ' To ' . $to->format('F d, Y');


            // ---------- Attendance Service Data ----------
            $payloadAttendanceData = [
                'from' => $from,
                'to' => $to,
                'employee' => $employee,
                'attendanceType' => $type,
                'shiftType' => $shiftType,
                'superDepartmentId' => $superDepartmentId,
            ];

            $data = $reportService->attendanceData(
                $payloadAttendanceData
            );

            // ---------- PDF ----------
            $payloadPdfData = [
                'data' => $data,
                'reportTitle' => $reportTitle,
                'reportName' => $reportName,
                'dateRange' => $dateRange,
                'employee' => $employee,
                'attendanceType' => $type,
                'shiftType' => $shiftType,
                'reportBladeName' => $reportBladeName,
            ];

            $pdfPath = $generateService->pdfSave(
                $payloadPdfData
            );

            // collect attachments
            $attachments[] = $pdfPath;
        }
    }


    // cleanup
    $this->cleanupOldFiles($path, $keepDays);

    if (!empty($mailTo) && !empty($attachments)) {

        $status = "success";
        $mailStatus = StatusHelper::mail($status);

        $metaData = array_merge($mailStatus, [
            'template' => 'email.system',
            'sender_id' => null,
            'receiver_id' => null,
            'module_id' => 0,
            'url' => ''
        ]);

        $subjectContent = AttendanceReportMailHelper::subject(
            $reportType,
            $from,
            $to,
            $attendanceTypes,
            $employee,
            $shiftType
        );

        $bodyContent = AttendanceReportMailHelper::body(
            $reportType,
            $from,
            $to,
            $attendanceTypes,
            $employee,
            $shiftType
        );

        app(NotificationService::class)->notify([
            'mail' => [
                'from_email' => config('mail.from.address'),
                'to_email' => $mailTo,
                'cc_email' => [
                  'monira@sasflydubai.com',
                  'sarkar.siddika@sasflydubai.com',
                              ],
                'subject' => $subjectContent,
                'module' => 'attendance_' . $reportType . '_schedule',
                'body' => $bodyContent,
                'status' => 'sent',
                'meta' => $metaData,
                'attachments' => $attachments, // ⭐ MUST
            ],
        ]);
    }
}

/**
 * Delete old report files (keep last N days)
 */
private
function cleanupOldFiles(string $path, int $keepDays): void
{
    $disk = Storage::disk('local');

    $files = $disk->files($path);

    if (empty($files)) {
        return;
    }

    $deleteBefore = now()->subDays($keepDays);

    foreach ($files as $file) {

        try {
            $lastModified = Carbon::createFromTimestamp(
                $disk->lastModified($file)
            );

            if ($lastModified->lt($deleteBefore)) {
                $disk->delete($file);
            }

        } catch (\Throwable $e) {
            logger()->error('Report cleanup failed', [
                'file' => $file,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
}