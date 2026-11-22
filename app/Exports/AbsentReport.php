<?php 
namespace App\Exports;

use App\Exports\Docx\AbsentReport as DocxAbsentReport;
use App\Exports\Docx\LateReport as DocxLateReport;
use App\Exports\Excel\AbsentReport as ExcelAbsentReport;
use App\Exports\Excel\LateReport as ExcelLateReport;
use App\Exports\PDF\LateReport as PDFLateReport;
use App\Models\Attendance;
use Dompdf\Dompdf;

class AbsentReport
{
    static function download_excel($lates, $from, $to, $name = "Absent-report.xlsx")
    {
        return (new ExcelAbsentReport($lates, $from, $to))->download($name);
    }
    static function download_docx($lates, $from, $to, $name = "Absent-report.docx")
    {
        return (new DocxAbsentReport($lates, $from, $to))->download($name);
    }
    static function download_pdf($lates, $from, $to, $name = "Absent-report.pdf")
    {
        $html = view('reports.absent-table', [
            'employees' => $lates,
            'start_date' => $from,
            'end_date' => $to,
            'for_export' => true,
        ])->render();
        // echo($html);exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return $dompdf->stream($name);
    }
    static function print($lates, $from, $to)
    {
        $html = view('reports.absent-table', [
            'employees' => $lates,
            'start_date' => $from,
            'end_date' => $to,
            'for_export' => true,
        ])->render();
        $html .= "<script>
        window.print();
        window.onafterprint = function() {
            // Close the tab after printing is done
            window.close();
        };
        let mediaQueryList = window.matchMedia('print');
        mediaQueryList.addListener(function(mql) {
            if (!mql.matches) {
                if (window && !window.closed) {
                    window.close();
                }
            }
        });
        </script>";
        return $html;
    }
}