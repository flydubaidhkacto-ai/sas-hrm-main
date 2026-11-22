<?php

namespace App\Exports;

use App\Exports\Excel\EmployeeBonus;
use App\Exports\Excel\FineReport as ExcelFineReport;
use App\Models\Fine;
use Dompdf\Dompdf;

class FineReport
{

    protected $fines = [];

    function get_fines()
    {
        Fine::orderBy('created_at', 'ASC')
            ->chunk(200, function ($__fines) {
                foreach ($__fines as $fine) {
                    $this->fines[] = $fine;
                }
            });
    }

    static function download_excel($data = null, $name = "Fine report.xlsx")
    {
        $th = new self;
        if($data)
            $th->fines = $data;
        else 
            $th->get_fines();
        return (new ExcelFineReport($th->fines))->download($name);
    }
    static function download_pdf($data, $name = "Fine report.pdf")
    {
        $html = view('fines.all_table', [
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
    static function download_print($data)
    {
        $html = view('fines.all_table', [
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

