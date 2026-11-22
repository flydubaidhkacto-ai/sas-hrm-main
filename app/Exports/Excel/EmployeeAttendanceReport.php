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

class EmployeeAttendanceReport implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $attendances = [];
    protected $new_attendances = [];
    function __construct($data, $employee, $from, $to) 
    {
        $this->new_attendances = [];
        foreach($data as $time => $item)
        {
            $this->new_attendances[] = [
                "ID" => $employee->id,
                "Emp. ID" => $employee->employee_id,
                "Name" => $employee->name,
                "Department" => $employee->department?->name,
                "Sup. Department" => $employee->department?->super_department?->name,
                "Designation" => $employee->designation?->name,
                "Shift" => ($item['attendance'] ?? false) && $item['attendance'] ? $item['attendance']->shift?->name : $employee->shift?->name,
                "Date" => date('d M, Y', $time),
                "Status" => (
                    $item['attendance'] ?? false ? 'Present' : (
                        $item['leave'] ?? false ? 'On Leave' : (
                            $item['absent'] ?? false ? 'Absent' : (
                                $item['weekend'] ?? false ? 'Weekend' : 'Absent'
                            )
                        )
                    )
                ),
                "In Time" => (
                    ($item['attendance'] ?? false) && $item['attendance']->worked_from 
                    ? carbon($item['attendance']->worked_from ?? '00:00')->format('h:i A') : "--:--"
                ),
                "Out Time" => (
                    ($item['attendance'] ?? false) && $item['attendance']->worked_to 
                    ? carbon($item['attendance']->worked_to ?? '00:00')->format('d M, Y h:i A') : '--:--'
                ),
                "W. Hour" => (
                    ($item['attendance'] ?? false) && $item['attendance']->working_time 
                    ? carbon($item['attendance']->working_time ?? '00:00')->format('H:i') : '--:--'
                ),
                "O.T." => (                        
                    ($item['attendance'] ?? false) && $item['attendance']->overtime_time 
                    ? carbon($item['attendance']->overtime_time ?? '00:00')->format('H:i') : '--:--'
                )
            ];
        }
    }
    function collection()
    {
        $company = company_data();
        $this->attendances[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->attendances[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "Attendance Report"
        ];
        $this->attendances[] = [
            'ID' => "Ref.",
            'Emp. ID' => "Emp. ID",
            'Name' => "Name",
            'Department' => "Department",
            'Sup. Department' => "Sup. Department",
            'Designation' => "Designation",
            "Shift" => "Shift",
            "Date" => "Date",
            "Status" => "Status",
            'In Time' => "In Time",
            'Out Time' => "Out Time",
            'W. Hour' => "W. Hour",
            'O.T.' => "O.T.",
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
