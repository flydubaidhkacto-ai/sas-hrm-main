<?php

namespace App\Exports\Excel;

use App\Models\Fine;
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

class FineReport implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $fines = [];
    protected $new_fines = [];
    function __construct($fines) 
    {
        $this->new_fines = collect($fines)->map(function ($fine) {
            return [
                $fine->id,
                $fine->employee?->id ?? 'N/A',
                $fine->employee?->employee_id ?? 'N/A',
                $fine->employee?->name ?? 'N/A',
                $fine->employee?->department?->name ?? 'N/A',
                $fine->employee?->department?->super_department?->name ?? 'N/A',
                $fine->employee?->designation?->name ?? 'N/A',
                $fine->reason,
                carbon($fine->created_at)->format('d F, Y'),
                ($fine->cut_off_as === 'once') ? "Once" : ($fine->cut_off_amount . " Tk / month"),
                $fine->is_decided()
                    ? ($fine->approved_at ? "Approved" : "Rejected")
                    : "Pending"
            ];
        })->toArray();
    }
    function collection()
    {
        $company = company_data();
        $this->fines[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->fines[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "Fine Report"
        ];
        $this->fines[] = [
            "Ref", "Emp. Code", "Emp. ID", "Employee Name", "Department", "Sup. Department",
            "Designation", "Reason", "Applied Date", "Pay Term", "Status"
        ];
        return collect($this->fines)->merge($this->new_fines);
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');
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
