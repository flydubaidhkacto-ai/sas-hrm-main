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

class AttendanceReport implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $attendances = [];
    protected $new_attendances = [];
    function __construct($attendances) 
    {
        $this->new_attendances = collect($attendances)->map(function ($item) {
            $date = carbon($item->date)->format('d F');
            if($item->added_manually_by)
            {
                $user = \App\Models\User::find($item->added_manually_by);
                if($user)
                {
                    $date .= "\n Added by: " . $user->name;
                }
            } 
            $notes = $item->notes ?? '';
            if($item->notes_by)
            {
                $user = \App\Models\User::find($item->notes_by);
                if($user)
                {
                    $notes .= "\n Added by: " . $user->name;
                }
            } 
            return [
                "ID" => $item->employee?->id,
                "Emp. ID" => $item->employee?->employee_id,
                "Name" => $item->employee?->name,
                "Department" => $item->employee?->department?->name,
                "Sup. Department" => $item->employee?->department?->super_department?->name,
                "Designation" => $item->employee?->designation?->name,
                "Shift" => $item->shift?->name,
                'Date' => $date,
                'In Time' => (
                    $item->check_in ? carbon($item->check_in ?? '00:00')->format('h:i A') : '--:--'
                ),
                'Out Time' => (
                    $item->check_out ? carbon($item->check_out ?? '00:00')->format('h:i A') : '--:--'
                ),
                'W. Hour' => carbon($item->working_time ?? '00:00')->format('H:i'),
                'O.T.' => carbon($item->overtime_time ?? '00:00')->format('H:i'),
                'Note' => $notes,
                'Duty Type' => $item->duty_type ?? '--',
            ];
        })->toArray();
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
            'ID' => "Code",
            'Emp. ID' => "Emp. ID",
            'Name' => "Name",
            'Department' => "Department",
            'Sup. Department' => "Sup. Department",
            'Designation' => "Designation",
            "Shift" => "Shift",
            "Date" => "Date",
            'In Time' => "In Time",
            'Out Time' => "Out Time",
            'W. Hour' => "W. Hour",
            'O.T.' => "O.T.",
            'Note' => "Note",
            'Duty Type' => "Duty Type",
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
