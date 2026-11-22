<?php
namespace App\Exports;

use App\Actions\TaxCheck;
use App\Exports\Excel\InOutSummeryReport as ExcelInOutSummeryReport;
use App\Exports\Excel\PayslipSheetExcel;
use App\Exports\Excel\TaxSlipExcel;
use Dompdf\Dompdf;

class TaxSlipSheet
{
    static function download_excel($data = null, $for_one = null, $name = "Salary Tax Sheet.xlsx")
    {
        return (new TaxSlipExcel($data, $for_one))->download($name);
    }
    static function download_pdf($data = null, $for_one = null, $name = "Salary Tax Sheet.pdf")
    {
        $html = view('payslip.tax_table', [
            'data' => TaxCheck::handle($data),
            'for_export' => true,
            'for_one' => $for_one,
        ])->render();
        // echo($html);exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return $dompdf->stream($name);
    }
    static function print($data = null, $for_one = null)
    {
        $html = view('payslip.tax_table', [
            'data' => TaxCheck::handle($data),
            'for_export' => true,
            'for_one' => $for_one,
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
