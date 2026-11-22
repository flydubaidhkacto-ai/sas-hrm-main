<?php

namespace App\Services;

use Excel;
use PDF;
use Illuminate\Support\Facades\Storage;
use App\Reports\AttendanceReportExcel;

class AttendanceReportGenerateService
{
    /* =====================================================
     * 🔵 COMMON: file name generator
     * ===================================================== */
    private function makeFileName(string $reportName, string $ext): string
    {
        return $reportName . '_' . now()->format('F_d_Y_His') . '.' . $ext;
    }

    /* =====================================================
     * EXCEL SAVE
     * ===================================================== */
    public function excelSave(array $payload): string
    {
        $data = $payload['data'];
        $reportName = $payload['reportName'];

        $fileName = $this->makeFileName($reportName, 'xlsx');
        $relativePath = 'reports/attendance/' . $fileName;

        Excel::store(
            new AttendanceReportExcel($data),
            $relativePath,
            'local'
        );

        return storage_path('app/' . $relativePath);
    }

    /* =====================================================
     * EXCEL DOWNLOAD (direct)
     * ===================================================== */
    public function excelDownload(array $payload)
    {
        $data = $payload['data'];
        $reportName = $payload['reportName'];

        $fileName = $this->makeFileName($reportName, 'xlsx');

        return Excel::download(
            new AttendanceReportExcel($data),
            $fileName
        );
    }

    /* =====================================================
     * PDF SAVE
     * ===================================================== */
    public function pdfSave(array $payload): string
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $reportName = $payload['reportName'];
        $data = $payload['data'];
        $reportTitle = $payload['reportTitle'];
        $dateRange = $payload['dateRange'];
        $employee = $payload['employee'];
        $shiftType = $payload['shiftType'];
        $attendanceType = $payload['attendanceType'];
        $reportBladeName = $payload['reportBladeName'];

        $fileName = $this->makeFileName($reportName, 'pdf');
        $relativePath = 'reports/attendance/' . $fileName;

        $payloadReportData = [
            'data' => $data,
            'reportTitle' => $reportTitle,
            'dateRange' => $dateRange,
            'employee' => $employee,
            'shiftType' => $shiftType,
            'attendanceType' => $attendanceType,
        ];

        $pdf = PDF::loadView($reportBladeName, $payloadReportData)->setPaper('a3', 'landscape');

        // render first
        $pdf->render();

        // left aligned page number
        $canvas = $pdf->getDomPDF()->get_canvas();
        $canvas->page_text(
            10,
            $canvas->get_height() - 20,
            "Page {PAGE_NUM} of {PAGE_COUNT}",
            null,
            10
        );

        Storage::disk('local')->put($relativePath, $pdf->output());

        return storage_path('app/' . $relativePath);
    }


    /* =====================================================
     *  PDF DOWNLOAD (direct)
     * ===================================================== */
    public function pdfDownload(array $payload)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $reportName = $payload['reportName'];
        $data = $payload['data'];
        $reportTitle = $payload['reportTitle'];
        $dateRange = $payload['dateRange'];
        $employee = $payload['employee'];
        $shiftType = $payload['shiftType'];
        $attendanceType = $payload['attendanceType'];
        $reportBladeName = $payload['reportBladeName'];

        $fileName = $this->makeFileName($reportName, 'pdf');

        $payloadReportData = [
            'data' => $data,
            'reportTitle' => $reportTitle,
            'dateRange' => $dateRange,
            'employee' => $employee,
            'shiftType' => $shiftType,
            'attendanceType' => $attendanceType,
        ];

        $pdf = PDF::loadView($reportBladeName, $payloadReportData)->setPaper('a3', 'landscape');

        // render first
        $pdf->render();

        //  left aligned page number
        $canvas = $pdf->getDomPDF()->get_canvas();
        $canvas->page_text(
            10,
            $canvas->get_height() - 20,
            "Page {PAGE_NUM} of {PAGE_COUNT}",
            null,
            10
        );

        return $pdf->download($fileName);
    }
}