<?php

namespace App\Exports\Excel;

use App\Models\Attendance;
use App\Models\Deduction;
use App\Models\EarnHead;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PayslipSheetExcel implements FromCollection, WithStyles, ShouldAutoSize
{
    use Exportable;
    protected $chunk_data = [];
    protected $data = [];
    protected $new_data = [];
    protected $grouped_data = [];

    protected $itemsToGetTotal = [];
    function __construct($data)
    {
        $this->new_data = [];
        $this->grouped_data = [];
        $total_salary = 0;
        $total_gross_salary = 0;
        $total_salary_payment = 0;

        // First, group data by super department and department
        $grouped_by_super_dept = [];

        foreach ($data as $item) {
            $super_dept_name = $item->employee?->department?->super_department?->name ?? 'No Super Department';
            $dept_name = $item->employee?->department?->name ?? 'No Department';

            if (!isset($grouped_by_super_dept[$super_dept_name])) {
                $grouped_by_super_dept[$super_dept_name] = [];
            }

            if (!isset($grouped_by_super_dept[$super_dept_name][$dept_name])) {
                $grouped_by_super_dept[$super_dept_name][$dept_name] = [];
            }

            $grouped_by_super_dept[$super_dept_name][$dept_name][] = $item;
        }

        // Sort super departments by name
        ksort($grouped_by_super_dept);

        $employee_counter = 1;

        foreach ($grouped_by_super_dept as $super_dept_name => $departments) {
            // Sort departments by name
            ksort($departments);

            foreach ($departments as $dept_name => $employees) {
                // Sort employees by name within each department
                usort($employees, function($a, $b) {
                    return strcasecmp($a->employee?->name ?? '', $b->employee?->name ?? '');
                });

                foreach ($employees as $item) {
                    $earn_heads = EarnHead::get();
                    $deductions = Deduction::all();

                    $salary = $item->total_salary;
                    $gross_salary = $item->employee?->payroll_card?->salary;

                    // Sum up total salary and total gross salary
                    $total_salary += $salary;
                    $total_gross_salary += $gross_salary;

                    $array_one = [
                        "S.L" => $employee_counter++,
                        "Super Department" => $super_dept_name,
                        "Emp. Code" => $item->employee?->id,
                        "Emp. ID" => $item->employee?->employee_id,
                        "Name" => $item->employee?->name,
                        "Designation" => $item->employee?->designation?->name,
                        "Department" => $dept_name,
                        "Join Date" => $item->employee?->join_date,
                        'Total Working Days' => Attendance::query()->where('employee_id', $item->employee?->id)
                            ->where('date', '>=', $item->start_date)
                            ->where('date', '<=', $item->end_date)
                            ->count(),
                    ];
                    if ($item->employee?->payroll_card?->grade) {
                        $array_one['Grade'] = $item->employee?->payroll_card?->grade?->name;
                    }
                    // add earn heads
                    foreach ($earn_heads as $earnHead) {
                        $array_one[$earnHead->name] = 0;
                        foreach ($item->earn_heads as $data) {
                            if ($data['name'] == $earnHead->name) {
                                $array_one[$earnHead->name] = 1 * ($data['total_amount'] ?? 0);
                                $this->itemsToGetTotal[] = $earnHead->name;
                            }
                        }
                    }
                    // $array_one['Basic Salary'] = $item->employee?->payroll_card?->basic_salary;
                    $array_one['Gross Salary'] = $gross_salary;

                    // add deductions
                    $total_deduction = 0;
                    foreach ($deductions as $deduction) {
                        $array_one[$deduction->name] = 0;
                        foreach ($item->deductions as $data) {
                            if ($data['name'] == $deduction->name) {
                                $amount = 1 * ($data['total_amount'] ?? 0);
                                $array_one[$deduction->name] = $amount;
                                $total_deduction += $amount;
                                $this->itemsToGetTotal[] = $deduction->name;
                            }
                        }
                    }
                    $array_one['Total Deduction'] = $total_deduction;
                    $this->itemsToGetTotal[] = 'Total Deduction';
                    $array_one['Net Salary'] = $item->total_salary;
                    $this->itemsToGetTotal[] = 'Net Salary';

                    if(has_module('payslip-payment')) {
                        $array_one['Curr. Balance'] = $item->employee?->payroll_balance ?: '0';
                        $this->itemsToGetTotal[] = 'Curr. Balance';
                        $array_one['Payment'] = $item->payment()?->paid_amount ?: '0';
                        $this->itemsToGetTotal[] = 'Payment';
                        $total_salary_payment += ($array_one['Payment']);
                    }

                    $array_one['Remarks'] = '';

                    // Store the grouped structure for later use
                    if (!isset($this->grouped_data[$super_dept_name])) {
                        $this->grouped_data[$super_dept_name] = [];
                    }
                    if (!isset($this->grouped_data[$super_dept_name][$dept_name])) {
                        $this->grouped_data[$super_dept_name][$dept_name] = [];
                    }

                    $this->grouped_data[$super_dept_name][$dept_name][] = $array_one;
                    $this->new_data[] = $array_one;
                }
            }
        }

        // Create total row
        $total_row = [];
        foreach (array_keys($this->new_data[0] ?? []) as $key => $value) {
            $total_row[$key] = '';
            if (in_array($value, $this->itemsToGetTotal)) {
                $total_row[$key] = array_sum(array_column($this->new_data, $value));
            }
        }

        // Add the total row at the end of all data
        $this->new_data[] = $total_row;
    }

    function collection()
    {
        $company = company_data();

        $this->data[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->data[] = [
            "Salary Sheet"
        ];

        // Add column headers
        $total_row = [];
        foreach (array_keys($this->new_data[0] ?? []) as $key => $value) {
            $total_row[$value] = $value;
        }
        $this->data[] = $total_row;

        // Build hierarchical structure with section headers
        $hierarchical_data = [];

        foreach ($this->grouped_data as $super_dept_name => $departments) {
            // Add Super Department header
            $super_dept_header = array_fill_keys(array_keys($this->new_data[0] ?? []), '');
            $super_dept_header[array_keys($super_dept_header)[0]] = "SUPER DEPARTMENT: " . strtoupper($super_dept_name);
            $hierarchical_data[] = $super_dept_header;

            foreach ($departments as $dept_name => $employees) {
                // Add Department header
                $dept_header = array_fill_keys(array_keys($this->new_data[0] ?? []), '');
                $dept_header[array_keys($dept_header)[0]] = "  → DEPARTMENT: " . strtoupper($dept_name);
                $hierarchical_data[] = $dept_header;

                // Add employees for this department
                foreach ($employees as $employee_data) {
                    $hierarchical_data[] = $employee_data;
                }

                // Add empty row for spacing between departments
                $empty_row = array_fill_keys(array_keys($this->new_data[0] ?? []), '');
                $hierarchical_data[] = $empty_row;
            }

            // Add empty row for spacing between super departments
            $empty_row = array_fill_keys(array_keys($this->new_data[0] ?? []), '');
            $hierarchical_data[] = $empty_row;
        }

        // Add grand total row at the end
        $total_row_data = [];
        foreach (array_keys($this->new_data[0] ?? []) as $key => $value) {
            $total_row_data[$key] = '';
            if (in_array($value, $this->itemsToGetTotal)) {
                $total_row_data[$key] = array_sum(array_column($this->new_data, $value));
            }
        }
        $total_row_data[array_keys($total_row_data)[0]] = "GRAND TOTAL";
        $hierarchical_data[] = $total_row_data;

        // Return the merged data
        return collect($this->data)->merge($hierarchical_data);
    }

    public function styles(Worksheet $sheet)
    {
        $letter = get_alphabet_from_number(count($this->new_data[0] ?? []));
        $sheet->mergeCells('A1:' . $letter . '1');
        $sheet->mergeCells('A2:' . $letter . '2');

        $styles = [
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
            3 => ['font' => ['bold' => true]], // Column headers
        ];

        // Add styles for section headers dynamically
        $row_number = 4; // Start after company name, title, and column headers

        foreach ($this->grouped_data as $super_dept_name => $departments) {
            // Style for Super Department header
            $styles[$row_number] = [
                'font' => ['bold' => true, 'size' => 14],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E6E6FA'] // Light lavender
                ]
            ];
            $sheet->mergeCells('A' . $row_number . ':' . $letter . $row_number);
            $row_number++;

            foreach ($departments as $dept_name => $employees) {
                // Style for Department header
                $styles[$row_number] = [
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0F8FF'] // Light blue
                    ]
                ];
                $sheet->mergeCells('A' . $row_number . ':' . $letter . $row_number);
                $row_number++;

                // Skip employee rows and empty row
                $row_number += count($employees) + 1;
            }

            // Skip empty row after super department
            $row_number++;
        }

        // Style for Grand Total row
        $styles[$row_number] = [
            'font' => ['bold' => true, 'size' => 12],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFD700'] // Gold
            ]
        ];

        return $styles;
    }
}
