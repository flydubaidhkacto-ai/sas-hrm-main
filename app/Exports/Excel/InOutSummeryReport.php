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

class InOutSummeryReport implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $data = [];
    protected $new_data = [];
    function __construct($data)
    {
        $this->new_data = [];
        foreach($data as $item)
        {
            $date = carbon($item->date)->format('d F');
            if($item->added_manually_by)
            {
                $user = \App\Models\User::find($item->added_manually_by);
                if($user)
                {
                    $date .= "\nAdded by: " . $user->name;
                }
            }
            $notes = $item->notes ?? '';
            if($item->notes_by)
            {
                $user = \App\Models\User::find($item->notes_by);
                if($user)
                {
                    $notes .= "\nAdded by: " . $user->name;
                }
            }
            if($item->shift?->is_24_hour_shift)
            {
                $mappedInOuts = \App\Actions\EmployeePunched\Calculate24HourShift::mappedInOuts($item);
            }else {
                $mappedInOuts = \App\Actions\EmployeePunched\CalculateWorkingTimeOfAttendance::execute(
                    $item, true
                )['mappedInOuts'] ?? [];
            }
            foreach ($mappedInOuts as $key => $value)
            {
                $in = $value[0] ?? null;
                $out = $value[1] ?? null;
                $this->new_data[] = [
                    "ID" => $item->employee?->id,
                    "Emp. ID" => $item->employee?->employee_id,
                    "Name" => $item->employee?->name,
                    "Department" => $item->employee?->department?->name,
                    "Sup. Department" => $item->employee?->department?->super_department?->name,
                    "Designation" => $item->employee?->designation?->name,
                    "Shift" => $item->is_in_roster ? 'In Roster' : $item->shift?->name,
                    'Date' => $date,
                    'In Time' => (
                        $in ? ($in->date_time)->format(
                            carbon($item->date)->format('d-m-Y') === ($in->date_time)->format('d-m-Y')
                            ? 'h:i:s A' : 'd M, h:i:s A'
                        ) : "--:--"
                    ),
                    'Out Time' => (
                        $out ? ($out->date_time)->format(
                            $in && $out && ($in->date_time)->format('d-m-Y') === ($out->date_time)->format('d-m-Y')
                                ? 'h:i:s A' : 'd M, h:i:s A'
                        ) : "--:--"
                    ),
                    'W. Hour' => gmdate('H:i', $value[2]['worked'] ?? 0) ,
                    'O.T.' =>  gmdate('H:i', $value[2]['overtime'] ?? 0) ,
                    'Note' => $notes,
                    'Duty Type' => $item->duty_type ?? '--',
                ];
            }
        }
    }
    function collection()
    {
        $company = company_data();
        $this->data[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->data[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "In Out Summery Report"
        ];
        $this->data[] = [
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
        return collect($this->data)->merge($this->new_data);
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
