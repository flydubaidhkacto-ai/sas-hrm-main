<?php

namespace App\Exports\Excel;

use App\Models\VehicleService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class VehicleServiceList implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $vehicle_services = [];
    protected $new_vehicle_services = [];
    function __construct($vehicle_services) 
    {
        $this->new_vehicle_services = collect($vehicle_services)->map(function ($vehicle_service) {
            return [
                $vehicle_service->id,
                $vehicle_service->vehicle->name ?? 'N/A',
                $vehicle_service->cost.' TK',
                $vehicle_service->reason,
                $vehicle_service->changed_parts,
                $vehicle_service->responsible_person_id,
                $vehicle_service->employee->name,
            ];
        })->toArray();
    }
    function collection()
    {
        $company = company_data();
        $this->vehicle_services[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->vehicle_services[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "Vehicle Service List"
        ];
        $this->vehicle_services[] = [
            "ID", "Vehicle", "Cost", "Reason", "Changed Parts", "Responsible Person ID","Responsible Person Name"
        ];
        return collect($this->vehicle_services)->merge($this->new_vehicle_services);
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        return [
            // Style the first row as bold text.
            1 => [
                'font' => ['bold' => true, 'size' => 17], // Increase font size for the company name
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER, // Center align the company name
                    'vertical' => Alignment::VERTICAL_CENTER, // Center align vertically
                ],
            ],
            2 => [
                'font' => ['size' => 14], // Set a smaller font size for the address
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER, // Center align the address
                    'vertical' => Alignment::VERTICAL_CENTER, // Center align vertically
                ],
            ],
            3 => ['font' => ['bold' => true]],
        ];
    }
}
