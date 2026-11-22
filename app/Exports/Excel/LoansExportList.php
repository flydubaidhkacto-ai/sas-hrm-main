<?php
namespace App\Exports\Excel;

use App\Models\Loan;
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

class LoansExportList implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $loans = [];
    protected $new_loans = [];
    function __construct($loans) 
    {
        $this->new_loans = collect($loans)->map(function ($loan) {
            return [
                $loan->id,
                $loan->employee?->name ?? 'N/A',
                $loan->amount . ' Tk',
                ($loan->amount - $loan->paid_amount_till_now()->sum('amount')) . ' Tk',
                $loan->paid_amount_till_now()->sum('amount') . ' Tk',
                $loan->reason,
                $loan->created_at->format('d F, Y'),
                ($loan->cut_off_as === 'once') ? "Once" : ($loan->cut_off_amount . " Tk Every Month"),
                $loan->is_decided() 
                    ? ($loan->approved_at ? "Approved" : "Rejected") 
                    : "Pending",
            ];
        })->toArray();
    }
    function collection()
    {
        $company = company_data();
        $this->loans[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->loans[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "Loans List"
        ];
        $this->loans[] = [
            "Ref", "Employee", "Loan Amount", "Due Amount", "Paid Amount",
            "Reason", "Applied Date", "Pay Term", "Status"
        ];
        return collect($this->loans)->merge($this->new_loans);
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
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
