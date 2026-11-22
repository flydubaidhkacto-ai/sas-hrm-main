<?php

namespace App\Exports\Excel;

use App\Models\User;
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

class EmployeeBonus implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $records = [];
    protected $new_records = [];
    protected $total = 0;
    function __construct($records)
    {
        $this->new_records = collect($records)->map(function ($record, $loop) {
            $user = $record->employee;
            $this->total += $record->amount;
            return [
                "S.L" => $loop + 1,
                "ID" => $user->id,
                "Emp. ID" => $user->employee_id,
                "Name" => $user->name,
                "Department" => $user->department?->name,
                "Sup. Department" => $user->department?->super_department?->name,
                "Designation" => $user->designation?->name,
                "Month" => carbon($record->month)->format('M, Y'),
                "Bonus" => $record->amount . " Tk",
                "Category" => $record->category?->name,
            ];
        })->toArray();
        $this->new_records[] = [
            "S.L" => '',
            "ID" => '',
            "Emp. ID" => '',
            "Name" => '',
            "Department" => '',
            "Sup. Department" => '',
            "Designation" => '',
            "Month" => '',
            "Bonus" => 'Total: ' . $this->total . ' Tk',
            "Category" => '',
        ];
    }
    function collection()
    {
        $company = company_data();
        $this->records[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->records[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "Employee Bonus List"
        ];
        $this->records[] = [
            'S.L' => "S.L",
            'ID' => "Code",
            'Emp. ID' => "Emp. ID",
            'Name' => "Name",
            'Department' => "Department",
            'Sup. Department' => "Sup. Department",
            'Designation' => "Designation",
            "Month" => "Month",
            "Bonus" => "Bonus",
            "Category" => "Category",
        ];
        return collect($this->records)->merge($this->new_records);
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
