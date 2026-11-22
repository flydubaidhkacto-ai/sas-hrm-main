<?php

namespace App\Exports\Excel;

use App\Models\Vehicle;
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

class VehicleList implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $vehicles = [];
    protected $new_vehicles = [];
    function __construct($vehicles) 
    {
        $this->new_vehicles = collect($vehicles)->map(function ($vehicle) {
            return [
                $vehicle->id,
                $vehicle->name,
                $vehicle->price . ' TK',
                $vehicle->sold_record ? 'Yes' : 'No',
                $vehicle->seller_details ?? 'N/A',
                $vehicle->active_location ?? 'N/A'
            ];
        })->toArray();
    }
    function collection()
    {
        $company = company_data();
        $this->vehicles[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->vehicles[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "vehicles List"
        ];
        $this->vehicles[] = [
            "ID", "Name", "Price", "Sold", "Seller", "Active Location"
        ];
        return collect($this->vehicles)->merge($this->new_vehicles);
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
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
