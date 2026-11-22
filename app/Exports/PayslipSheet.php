<?php
namespace App\Exports;

use App\Exports\Excel\InOutSummeryReport as ExcelInOutSummeryReport;
use App\Exports\Excel\PayslipSheetExcel;
use Dompdf\Dompdf;

class PayslipSheet
{
    static function download_excel($data = null, $name = "Salary Sheet.xlsx", $month)
    {
        return (new PayslipSheetExcel($data, $month))->download($name);
    }
    static function download_pdf($data = null, $name = "Salary Sheet.pdf", $month)
    {
        $html = view('payslip.all-table', [
            'payslips' => $data,
            'for_export' => true,
            'month' => $month,
        ])->render();

        // echo($html);exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return $dompdf->stream($name);
    }
    static function print($data = null, $name, $month)
    {
        $html = view('payslip.all-table', [
            'payslips' => $data,
            'for_export' => true,
            'month' => $month,
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
