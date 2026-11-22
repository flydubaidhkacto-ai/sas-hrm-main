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
class TotalDutyReport implements FromCollection, WithStyles
{
    use Exportable;
    public $coll = [];
    
    public function __construct(public $data)
    {
    }
    function collection()
    {
        $company = company_data();
        $this->coll[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->coll[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "Total Duty Report ", 
        ];
        if($this->data[0]['data'] ?? false)
        {
            $first = array_keys($this->data[0]['data'])[0];
            $last = array_keys($this->data[0]['data'])[ count(array_keys($this->data[0]['data'])) - 1 ];
            // dd($first, $last);
            $this->coll[1][0] .= " (". date('d M, Y', $first) ." - ". date('d M, Y', $last) .")";
        }
        $this->coll[] = [
            'S.L.' => "S.L.",
            'Code' => "Code",
            'Emp. ID' => "Emp. ID",
            'Name' => "Name",
            'Department' => "Department",
            'Sup. Department' => "Sup. Department",
            'Designation' => "Designation",
            "W. Hours" => "W. Hours",
            "W. Days" => "W. Days",
            "O.T." => "O.T.",
            "Lates" => "Lates",
            "Late Days" => "Late Days",
            "Leave Days" => "Leave Days",
            "Absents" => "Absents",
            "Holidays" => "Holidays",
        ];
        $index = 0;
        // dd($this->data);
        foreach ($this->data as $item) 
        {
            $index++;
            $val = [
                "S.L." => $index,
                "Code" => $item['user']['id'],
                "Emp. ID" => $item['user']['employee_id'],
                "Name" => $item['user']['name'],
                "Department" => $item['user']['department']['name'] ?? '',
                "Sup. Department" => $item['user']['department']['super_department']['name'] ?? '',
                "Designation" => $item['user']['designation']['name'] ?? '',
                "W. Hours" => seconds_to_hh_mm($item['total_worked_seconds']),
                "W. Days" => $item['total_worked_days'],
                "O.T." => seconds_to_hh_mm($item['total_overtime_seconds']),
                "Lates" => seconds_to_hh_mm($item['total_late_seconds']),
                "Late Days" => $item['total_late_days'] ?? 0,
                "Leave Days" => $item['total_leave_days'] ?? 0,
                "Absents" => $item['total_absent_days'] ?? 0,
                "Holidays" => $item['total_holidays'] ?? 0, 
            ];
            
            $this->coll[] = $val;
        }
        return collect($this->coll);
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