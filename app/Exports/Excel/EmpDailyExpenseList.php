<?php

namespace App\Exports\Excel;

use App\Models\EmpDailyExpense;
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

class EmpDailyExpenseList implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $emp_daily_expenses = [];
    protected $new_emp_daily_expenses = [];
    function __construct($emp_daily_expenses) 
    {
        $this->new_emp_daily_expenses = collect($emp_daily_expenses)->map(function ($emp_daily_expense) {
            return [
                $emp_daily_expense->id,
                $emp_daily_expense->employee?->employee_id ?? 'N/A',
                $emp_daily_expense->employee?->name ?? 'N/A',
                $emp_daily_expense->employee?->department?->super_department?->name ?? 'N/A',
                $emp_daily_expense->employee?->department?->name ?? 'N/A',
                $emp_daily_expense->employee?->designation?->name ?? 'N/A',
                $emp_daily_expense->rel_to_expensed_category?->name ?? 'N/A',
                $emp_daily_expense->name ?? 'N/A',
                $emp_daily_expense->amount ?? '0.00',
                $emp_daily_expense->date ? date('d M, Y', strtotime($emp_daily_expense->date)) : 'N/A',
                $emp_daily_expense->is_final_decided()
                    ? ($emp_daily_expense->final_approved_at ? "Final Approved" : "Rejected")
                    : ($emp_daily_expense->is_decided()
                        ? ($emp_daily_expense->approved_at ? "First Approved" : "Rejected")
                        : "Pending")
            ];
        })->toArray();
    }
    function collection()
    {
        $company = company_data();
        $this->emp_daily_expenses[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->emp_daily_expenses[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "Employee Daily Expense List"
        ];
        $this->emp_daily_expenses[] = [
            "Ref", "Emp. ID", "Employee Name", "Super Department", "Department",
            "Designation", "Expense Category", "Name", "Amount", "Date", "Status"
        ];
        return collect($this->emp_daily_expenses)->merge($this->new_emp_daily_expenses);
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
