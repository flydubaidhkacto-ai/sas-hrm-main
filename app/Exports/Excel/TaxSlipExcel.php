<?php
namespace App\Exports\Excel;

use App\Actions\TaxCheck;
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

class TaxSlipExcel implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $data = [];
    protected $new_data = [];
    function __construct($data, $for_one)
    {
        $this->new_data = [];
        $data = TaxCheck::handle($data);
        $ii = 0;
        foreach($data['employees'] as $i => $item)
        {
            $ii++;
            $main = [
                "S.L" => $ii,
                "ID" => $item['employee']?->id,
                "Emp. ID" => $item['employee']?->employee_id,
                "Name" => $item['employee']?->name,
                "Sup. Department" => $item['employee']?->department?->super_department?->name,
                "Department" => $item['employee']?->department?->name,
                "Designation" => $item['employee']?->designation?->name,
                // "Month" => carbon($item->month_date)->format('F, Y'),
                // "Tax" => TaxCheck::caluclate_tax($item->deductions) . ' Tk'
            ];
            if($for_one)
            {
                foreach ($item['months'] as $key => $value) {
                    $this->new_data[] = [
                        ...$main,
                        "Month" => carbon($value['month'])->format('M, Y'),
                        "Tax" => $value['tax'] . ' Tk'
                    ];
                }
            }
            else {
                if(count($item['months']) > 1)
                {
                    $mm = carbon($item['months'][0]['month'])->format('M, Y') .' - '. carbon($item['months'][count($item['months']) - 1]['month'])->format('M, Y');
                }
                $this->new_data[] = [
                    ...$main,
                    "Month" => $mm ?? '',
                    "Tax" => $item['tax'] . ' Tk'
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
            "Salary Tax Sheet"
        ];
        $this->data[] = [
            'S.L' => "S.L",
            'ID' => "Code",
            'Emp. ID' => "Emp. ID",
            'Name' => "Name",
            'Sup. Department' => "Sup. Department",
            'Department' => "Department",
            'Designation' => "Designation",
            "Month" => "Month",
            "Tax" => "Tax"
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
