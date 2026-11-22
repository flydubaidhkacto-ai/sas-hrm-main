<?php

namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Exports\Excel\VehicleList as ExcelVehicleList;
use Dompdf\Dompdf;

class VehicleList implements FromCollection
{
    protected $vehicles = [];

    function get_vehicles()
    {
        Vehicle::orderBy('created_at', 'ASC')
            ->chunk(200, function ($__vehicles) {
                foreach ($__vehicles as $vehicle) {
                    $this->vehicles[] = $vehicle;
                }
            });
    }

    static function download_excel($data = null, $name = "Vehicles List.xlsx")
    {
        $th = new self;
        if($data)
            $th->vehicles = $data;
        else 
            $th->get_vehicles();
        return (new ExcelVehicleList($th->vehicles))->download($name);
    }

    static function download_pdf($data = null, $name = "Vehicles List.pdf")
    {
        $th = new self;
        if($data)
            $th->vehicles = $data;
        else 
            $th->get_vehicles();
        $html = view('vehicle.list_table', [
            'data' => $th->vehicles,
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
            $th->vehicles = $data;
        else 
            $th->get_vehicles();
        $html = view('vehicle.list_table', [
            'data' => $th->vehicles,
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
        return Vehicle::all();
    }
}
