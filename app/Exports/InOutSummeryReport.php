<?php
namespace App\Exports;

use App\Exports\Excel\InOutSummeryReport as ExcelInOutSummeryReport;
use Dompdf\Dompdf;

class InOutSummeryReport
{
    static function download_excel($data = null, $name = "In out summery.xlsx")
    {
        return (new ExcelInOutSummeryReport($data))->download($name);
    }
    static function download_pdf($data = null, $name = "In out summery.pdf")
    {
        $html = view('reports.in_out_summery-table', [
            'data' => $data,
            'for_export' => true,
        ])->render();
        // echo($html);exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return $dompdf->stream($name);
    }
    static function print($data = null)
    {   
        $html = view('reports.in_out_summery-table', [
            'data' => $data,
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