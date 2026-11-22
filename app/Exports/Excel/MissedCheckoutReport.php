<?php

namespace App\Exports\Excel;

use App\Models\InOutRecord;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MissedCheckoutReport implements FromCollection, WithStyles
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
        $this->rows[] = ['Missed Check-Out Report'];
        $this->rows[] = [
            'SL',
            'ID',
            'Emp. ID',
            'Name',
            'Department',
            ...(config('have_multi_sup_dept') ? ['Sup. Department'] : []),
            'Designation',
            'Shift',
            'Date',
            'In Time',
            'Out Time',
            'W. Hours',
            'O.T.',
            'Note',
            'Duty Type'
        ];
        $i = 1;
        $this->query->chunk(1000, function ($items) use (&$i) {
            foreach ($items as $item) {
                $data = [
                    $i++,
                    $item->employee?->id,
                    $item->employee?->employee_id,
                    $item->employee?->name,
                    $item->employee?->department?->name,
                    ...(config('have_multi_sup_dept') ? [$item->employee?->department?->super_department?->name] : []),
                    $item->employee?->designation?->name,
                    ($item->is_in_roster ? 'Roster' : $item->shift?->name),
                    carbon($item->date)->format('d F'),
                ];
                $used = \App\Actions\EmployeePunched\CalculateWorkingTimeOfAttendance::execute(
                    $item,
                    true,
                    null
                );
                $data[] = $used['checkedIn'] ? carbon($used['checkedIn'])->format('h:i A') : '--:--';
                $data[] = $used['checkedOut'] ? carbon($used['checkedOut'])->format('d M, Y h:i A') : '--:--';
                $data[] = carbon($item->working_time ?: '00:00')->format('H:i');
                $data[] = carbon($item->overtime_time ?: '00:00')->format('H:i');
                $data[] = $item->note ?? '';
                $data[] = $item->duty_type ? $item->duty_type : '';
                $this->rows[] = $data;
            }
        });

        return collect($this->rows);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:N1');
        $sheet->mergeCells('A2:N2');

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
