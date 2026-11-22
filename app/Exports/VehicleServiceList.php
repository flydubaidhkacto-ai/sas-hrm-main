<?php

namespace App\Exports;

use App\Models\VehicleService;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Exports\Excel\VehicleServiceList as ExcelVehicleServiceList;
use Dompdf\Dompdf;

class VehicleServiceList implements FromCollection
{
    protected $vehicle_services = [];

    function get_vehicle_services()
    {
        VehicleService::orderBy('created_at', 'ASC')
            ->chunk(200, function ($__vehicle_services) {
                foreach ($__vehicle_services as $vehicle_service) {
                    $this->vehicle_services[] = $vehicle_service;
                }
            });
    }

    static function download_excel($data = null, $name = "Vehicle Services.xlsx")
    {
        $th = new self;
        if($data)
            $th->vehicle_services = $data;
        else 
            $th->get_vehicle_services();
        return (new ExcelVehicleServiceList($th->vehicle_services))->download($name);
    }

    static function download_pdf($data = null, $name = "Vehicle Service.pdf")
    {
        $th = new self;
        if($data)
            $th->vehicle_services = $data;
        else 
            $th->get_vehicle_services();
        $html = view('vehicle_service.list_table', [
            'data' => $th->vehicle_services,
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
            $th->vehicle_services = $data;
        else 
            $th->get_vehicle_services();
        $html = view('vehicle_service.list_table', [
            'data' => $th->vehicle_services,
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
        return VehicleService::all();
    }
}
