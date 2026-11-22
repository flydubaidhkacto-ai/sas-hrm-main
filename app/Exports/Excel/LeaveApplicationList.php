<?php

namespace App\Exports\Excel;

use App\Models\LeaveApplication;
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

class LeaveApplicationList implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $leave_applications = [];
    protected $new_leave_applications = [];
    function __construct($leave_applications)
    {
        $this->new_leave_applications = collect($leave_applications)->map(function ($leave_application) {
            return [
                $leave_application->id,
                $leave_application->employee?->id ?? '',
                $leave_application->employee?->employee_id ?? '',
                $leave_application->employee?->name ?? '',
                $leave_application->employee?->department?->name ?? '',
                $leave_application->employee?->department?->super_department->name ?? '',
                $leave_application->employee?->designation?->name ?? '',
                $leave_application->title,
                $leave_application->is_hourly
                    ? carbon((explode(' ', $leave_application->start_date)[0]) . ' ' . ($leave_application->start_time))->format('d M, y h:i A')
                    : carbon($leave_application->start_date)->format('d F, Y'),
                $leave_application->is_hourly
                    ? carbon($leave_application->end_time)->format('h:i A')
                    : carbon($leave_application->end_date)->format('d F, Y'),
                $leave_application->is_hourly
                    ? carbon($leave_application->start_time)->diffInHours(carbon($leave_application->end_time)) . ' hours'
                    : carbon($leave_application->start_date)->diffInDays(carbon($leave_application->end_date)) + 1 . ' days',
                $leave_application->leave_type?->name ?? $leave_application->type,
                $leave_application->status ?? ''
            ];
        })->toArray();
    }
    function collection()
    {
        $company = company_data();
        $this->leave_applications[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->leave_applications[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "Leave Application List"
        ];
        $this->leave_applications[] = [
            "Ref",
            "Emp. Code",
            "Emp. ID",
            "Employee Name",
            "Department",
            "Sup. Department",
            "Designation",
            "Reason",
            "Start Date",
            "End",
            "Duration",
            "Type",
            "Status"
        ];
        return collect($this->leave_applications)->merge($this->new_leave_applications);
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:L1');
        $sheet->mergeCells('A2:L2');
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
