<?php

namespace App\Exports\Excel;

use App\Models\VehicleRefuel;
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

class VehicleRefuelList implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $refuelings = [];
    protected $new_refuelings = [];
    function __construct($refuelings) 
    {
        $this->new_refuelings = collect($refuelings)->map(function ($refueling) {
            return [
                $refueling->id,
                $refueling->vehicle->name ?? '',
                $refueling->fuel_type,
                $refueling->driver->name ?? '',
                $refueling->fuel_station,
                $refueling->place ?? '',
                $refueling->budget.' TK' ?? '',
                $refueling->km_per_unit ?? '',
                $refueling->refuel_limit ?? '',
                $refueling->max_unit ?? '',
                $refueling->odometer_end_of_day ?? '',
                $refueling->odometer_at_refuel ?? '',
                $refueling->unit_taken ?? '',
                $refueling->last_reading ?? '',
                $refueling->last_unit ?? '',
                $refueling->consumption_percent ?? '',
            ];
        })->toArray();
    }
    function collection()
    {
        $company = company_data();
        $this->refuelings[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->refuelings[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "Vehicle Refueling List"
        ];
        $this->refuelings[] = [
            "ID", "Vehicle", "Fuel Type", "Driver", "Fuel Station","Place","Budget","Kilometer Per Unit","Refuel Limit","Max Unit","Odometer End","Odometer at Refueling","Unit Taken","Last Reading","Last Unit","Consumption Percent"
        ];
        return collect($this->refuelings)->merge($this->new_refuelings);
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:P1');
        $sheet->mergeCells('A2:P2');
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
