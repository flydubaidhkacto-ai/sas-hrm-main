<?php

namespace App\Exports\Excel;

use App\Models\LeaveApplication;
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

class EmployeeRewardList implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $rewards = [];
    protected $new_rewards = [];
    function __construct($rewards) 
    {
        $this->new_rewards = collect($rewards)->map(function ($reward) {
            return [
                $reward->id,
                $reward->employee?->id ?? 'N/A',
                $reward->employee?->employee_id ?? 'N/A',
                $reward->employee?->name ?? 'N/A',
                $reward->employee?->department?->name ?? 'N/A',
                $reward->employee?->department?->super_department?->name ?? 'N/A',
                $reward->employee?->designation?->name ?? 'N/A',
                $reward->amount . ' Tk',
                $reward->name ?? 'N/A',
                $reward->date->format('d M, Y') ?? 'N/A',
                $reward->added?->name ?? '--',
            ];
        })->toArray();
    }
    function collection()
    {
        $company = company_data();
        $this->rewards[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->rewards[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "Reward List"
        ];
        $this->rewards[] = [
            "Ref", "Emp. Code", "Emp. ID", "Employee Name", "Department", "Sup. Department",
            "Designation", "Reward", "Reason", "Date", "Added by"
        ];
        return collect($this->rewards)->merge($this->new_rewards);
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
