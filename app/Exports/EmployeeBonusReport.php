<?php

namespace App\Exports;

use App\Exports\Excel\EmployeeBonus;
use Dompdf\Dompdf;

class EmployeeBonusReport
{
    static function download_xlsx($data, $name = "Bonus Sheet.xlsx")
    {
        return (new EmployeeBonus($data))->download($name);
    }
    static function download_pdf($data, $name = "Bonus Sheet.pdf")
    {
        $html = view('bonuses.all_table', [
            'bonuses' => $data,
            'for_export' => true,
        ])->render();
        // echo($html);exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return $dompdf->stream($name);
    }
    static function download_print($data)
    {
        $html = view('bonuses.all_table', [
            'bonuses' => $data,
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
