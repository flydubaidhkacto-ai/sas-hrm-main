<?php
namespace App\Exports\Excel;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CheckinReport implements FromCollection, WithStyles, ShouldAutoSize
{
    use Exportable;

    protected $data;
    protected $from_date;
    protected $to_date;
    protected $export_data = [];

    public function __construct($data, $from_date = null, $to_date = null)
    {
        $this->data = $data;
        $this->from_date = $from_date;
        $to_date = $to_date;
        $this->prepareExportData();
    }

    protected function prepareExportData()
    {
        $sl = 1;
        foreach ($this->data as $item) {
            // For each check-in record, we need to handle the visited locations
            $locations_text = '';
            if ($item->locations && $item->locations->count() > 0) {
                $location_parts = [];
                foreach ($item->locations as $index => $location) {
                    $note = $location->note ?: '--';
                    $location_parts[] = ($index + 1) . '. ' . $location->location_name . ' (Note: ' . $note . ')';
                }
                $locations_text = implode(PHP_EOL, $location_parts);
            } else {
                $locations_text = '--';
            }

            $this->export_data[] = [
                'SL' => $sl++,
                'Emp Code' => $item->user?->id,
                'Emp ID' => $item->user?->employee_id,
                'Employee' => $item->user?->name,
                ...(config('have_multi_sup_dept') ? ['Sup Department' => $item->user?->department?->super_department?->name] : []),
                'Department' => $item->user?->department?->name,
                'Designation' => $item->user?->designation?->name,
                'Date' => $item->check_at?->format('d F, Y'),
                'Visited Locations' => $locations_text,
                'Total Distance (KM)' => number_format($item->total_distance_km ?? 0, 2) . ' KM'
            ];
        }
    }

    public function collection()
    {
        $company = company_data();
        $final_data = [];

        // Company header
        $final_data[] = [
            $company['name'] ?? "Hr Payroll",
        ];

        // Report title
        $final_data[] = [
            "Geo Location Check-In Report"
        ];

        // Date range if provided
        if ($this->from_date && $this->to_date) {
            $final_data[] = [
                "Date Range: " . $this->from_date . " to " . $this->to_date
            ];
        }

        // Table headers
        $headers = [
            'SL' => 'S.L',
            'Emp Code' => 'Emp Code',
            'Emp ID' => 'Emp ID',
            'Employee' => 'Employee',
        ];

        if (config('have_multi_sup_dept')) {
            $headers['Sup Department'] = 'Sup. Dept.';
        }

        $headers = array_merge($headers, [
            'Department' => 'Department',
            'Designation' => 'Designation',
            'Date' => 'Date',
            'Visited Locations' => 'Visited Locations',
            'Total Distance (KM)' => 'Total Distance (KM)'
        ]);

        $final_data[] = $headers;

        // Add the prepared data
        return collect($final_data)->merge($this->export_data);
    }

    public function styles(Worksheet $sheet)
    {
        $header_row = $this->from_date && $this->to_date ? 4 : 3;
        $total_columns = config('have_multi_sup_dept') ? 'K' : 'J';

        // Merge cells for company name
        $sheet->mergeCells('A1:' . $total_columns . '1');

        // Merge cells for report title
        $sheet->mergeCells('A2:' . $total_columns . '2');

        // Merge cells for date range if present
        if ($this->from_date && $this->to_date) {
            $sheet->mergeCells('A3:' . $total_columns . '3');
        }

        // Set wrap text for "Visited Locations" column (Column I)
        $visited_locations_column = config('have_multi_sup_dept') ? 'I' : 'H';
        $sheet->getStyle($visited_locations_column . ':' . $visited_locations_column)->getAlignment()->setWrapText(true);

        $styles = [
            // Company name style
            1 => [
                'font' => ['bold' => true, 'size' => 17],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Report title style
            2 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Header row style
            $header_row => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];

        // Date range style if present
        if ($this->from_date && $this->to_date) {
            $styles[3] = [
                'font' => ['size' => 12],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
        }

        return $styles;
    }
}
