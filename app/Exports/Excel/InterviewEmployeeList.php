<?php

namespace App\Exports\Excel;

use App\Models\InterviewEmployee;
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

class InterviewEmployeeList implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $interview_employees = [];
    protected $new_interview_employees = [];
    function __construct($interview_employees)
    {
        $this->new_interview_employees = collect($interview_employees)->map(function ($interview_employee) {
            return [
                $interview_employee->id,
                $interview_employee->name ?? '',
                $interview_employee->department?->name ?? '',
                $interview_employee->department?->super_department->name ?? '',
                $interview_employee->designation?->name ?? '',
                $interview_employee->email ?? '',
                $interview_employee->phone_number ?? '',
                $interview_employee->address ?? '',
                $interview_employee->interview_date ? \Carbon\Carbon::parse($interview_employee->interview_date)->format('d F, Y') : '',
                $interview_employee->expected_salary ?? '',
            ];
        })->toArray();
    }
    function collection()
    {
        $company = company_data();
        $this->interview_employees[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->interview_employees[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "Interview Employee List"
        ];
        $this->interview_employees[] = [
            "Ref",
            "Name",
            "Department",
            "Sup. Department",
            "Designation",
            "Email",
            "Phone Number",
            "Address",
            "Interview Date",
            "Expected Salary"
        ];
        return collect($this->interview_employees)->merge($this->new_interview_employees);
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
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
