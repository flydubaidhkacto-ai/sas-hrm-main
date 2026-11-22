<?php
namespace App\Exports;

use App\Exports\Docx\LateReport as DocxLateReport;
use App\Exports\Excel\LateReport as ExcelLateReport;
use App\Exports\PDF\LateReport as PDFLateReport;
use App\Models\Attendance;
use Dompdf\Dompdf;

class LateReport
{
    protected $lates = [];
    function get_lates ()
    {
        Attendance::query()
            ->with(['employee.designation', 'employee.department.super_department', 'employee.shift', 'shift'])
            ->whereDate('date', '<=', now()->format('Y-m-d'))
            ->whereDate('date', '>=', now()->subMonth()->format('Y-m-d'))
            ->chunk(200, function ($__lates) {
                foreach ($__lates as $user) {
                    $this->lates[] = $user;
                }
            });
    }
    static function download_excel($lates = null, $name = "Late-report.xlsx")
    {
        $th = new self;
        if($lates)
            $th->lates = $lates;
        else
            $th->get_lates();
        return (new ExcelLateReport($th->lates))->download($name);
    }
    static function download_pdf($lates = null, $name = "Late-report.pdf")
    {
        $th = new self;
        if($lates)
            $th->lates = $lates;
        else
            $th->get_lates();
        $html = view('reports.late-table', [
            'data' => $th->lates,
            'for_export' => true,
        ])->render();
        // echo($html);exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return $dompdf->stream($name);
    }
    static function download_docx($lates = null, $name = "Late-report.docx")
    {
        $th = new self;
        if($lates)
            $th->lates = $lates;
        else
            $th->get_lates();
        return (new DocxLateReport($th->lates))->download($name);
    }
    static function print($lates = null)
    {
        $th = new self;
        if($lates)
            $th->lates = $lates;
        else
            $th->get_lates();
        $html = view('reports.late-table', [
            'data' => $th->lates,
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
