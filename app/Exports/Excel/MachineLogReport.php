<?php
namespace App\Exports\Excel;

use App\Models\InOutRecord;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MachineLogReport implements FromCollection, WithStyles
{
    use Exportable;

    protected $query;
    protected $rows = [];

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        $company = company_data();
        $this->rows[] = [$company['name'] ?? 'Hr payroll'];
        $this->rows[] = ['Machine Log'];
        $this->rows[] = [
            'SL', 'ID', 'Emp. ID', 'Name', 'Department', ...(config('have_multi_sup_dept') ? ['Sup. Department'] : []), 'Designation', 'Device/Added by', 'Date & Time', 'Location'
        ];
        $i = 1;
        $this->query->chunk(1000, function ($items) use (&$i) {
            foreach ($items as $item) {
                $this->rows[] = [
                    $i++,
                    $item->employee?->id,
                    $item->employee?->employee_id,
                    $item->employee?->name,
                    $item->employee?->department?->name,
                    ...(config('have_multi_sup_dept') ? [$item->employee?->department?->super_department?->name] : []),
                    $item->employee?->designation?->name,
                    $item->device_uid,
                    $item->date_time,
                    $item->location ?? '',
                ];
            }
        });

        return collect($this->rows);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 17],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            2 => [
                'font' => ['size' => 14],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            3 => ['font' => ['bold' => true]],
        ];
    }
}
