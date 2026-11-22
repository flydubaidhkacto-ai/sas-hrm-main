<?php


namespace App\Reports;

use PDF;

class BankSalaryPdf
{
    private function makeFileName($name)
    {
        return $name . '_' . now()->format('d_m_Y_His') . '.pdf';
    }

    public function pdfDownload(array $payload)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $fileName = $this->makeFileName($payload['reportName']);

        $pdf = PDF::loadView($payload['reportBladeName'], [
            'reportData' => $payload['reportData'],
            'month' => $payload['month'],
            'filters' => $payload['filters'],
            'status' => $payload['status'],
        ])
            ->setPaper('a3', 'landscape');

        // ❌ REMOVE canvas page_text (THIS CAUSES HANG)
        // $canvas = $pdf->getDomPDF()->get_canvas();

        return $pdf->download($fileName);
    }
}