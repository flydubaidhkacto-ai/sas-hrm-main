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

class AbsentReport implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $attendances = [];
    protected $new_attendances = [];
    function __construct($attendances, public $from, public $to)
    {
        $currentDate = $this->from->copy();
        $i = 1;
        while ($currentDate->lte($this->to))
        {
            $emps = $attendances[$currentDate->format('Y_m_d')];
            foreach ($emps as $employee)
            {
                $this->new_attendances[] = [
                    "SL" => $i++,
                    "Date" => $currentDate->format('d F'),
                    "ID" => $employee?->id,
                    "Emp. ID" => $employee?->employee_id,
                    "Name" => $employee?->name,
                    "Department" => $employee?->department?->name,
                    ...(config('have_multi_sup_dept') ? ["Sup. Department" => $employee?->department?->super_department?->name] : []),
                    "Designation" => $employee?->designation?->name,
                    "Shift" => $employee?->shift?->name,
                    "Phone" => $employee?->phone_number
                ];
            }
            $currentDate->addDay();
        }
        $this->new_attendances = collect($this->new_attendances);
    }
    function collection()
    {
        $company = company_data();
        $this->attendances[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->attendances[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "Absent Report"
        ];
        $this->attendances[] = [
            'SL' => "SL",
            'Date' => "Date",
            'ID' => "Code",
            'Emp. ID' => "Emp. ID",
            'Name' => "Name",
            'Department' => "Department",
            ...(config('have_multi_sup_dept') ? ['Sup. Department' => "Sup. Department"] : []),
            'Designation' => "Designation",
            "Shift" => "Shift",
            "Phone" => "Phone",
        ];
        return collect($this->attendances)->merge($this->new_attendances);
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
