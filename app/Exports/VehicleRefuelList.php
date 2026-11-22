<?php

namespace App\Exports;

use App\Models\VehicleRefuel;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Exports\Excel\VehicleRefuelList as ExcelVehicleRefuelList;
use Dompdf\Dompdf;

class VehicleRefuelList implements FromCollection
{
    protected $refuelings = [];

    function get_refuelings()
    {
        VehicleRefuel::with('vehicle', 'driver')->orderBy('created_at', 'ASC')
            ->chunk(200, function ($__refuelings) {
                foreach ($__refuelings as $refueling) {
                    $this->refuelings[] = $refueling;
                }
            });
    }

    static function download_excel($data = null, $name = "Vehicle Refueling.xlsx")
    {
        $th = new self;
        if($data)
            $th->refuelings = $data;
        else 
            $th->get_refuelings();
        return (new ExcelVehicleRefuelList($th->refuelings))->download($name);
    }

    static function download_pdf($data = null, $name = "Vehicle Service.pdf")
    {
        $th = new self;
        if($data)
            $th->refuelings = $data;
        else 
            $th->get_refuelings();
        $html = view('vehicle.refuelings.list_table', [
            'refuelings' => $th->refuelings,
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
        $th = new self;
        if($data)
            $th->refuelings = $data;
        else 
            $th->get_refuelings();
        $html = view('vehicle.refuelings.list_table', [
            'refuelings' => $th->refuelings,
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
    public function collection()
    {
        return VehicleRefuel::all();
    }
}
