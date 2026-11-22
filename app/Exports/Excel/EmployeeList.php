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

class EmployeeList implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $users = [];
    protected $new_users = [];
    function __construct($users) 
    {
        $this->new_users = collect($users)->map(function ($user) {
            return [
                "ID" => $user->id,
                "Emp. ID" => $user->employee_id,
                "Name" => $user->name,
                "Department" => $user->department?->name,
                "Sup. Department" => $user->department?->super_department?->name,
                "Designation" => $user->designation?->name,
                'Mobile' => $user->phone_number,
                'Join Date' => carbon($user->join_date)->format('d M, Y'),
                'Shift' => $user->shift ? (
                    $user->shift->name . " - " . carbon($user->shift->start)->format('h:i A') . " - ". carbon($user->shift->end)->format('h:i A')
                ) : "Not Assigned",
                'Overtime' => $user->can_overtime ? "Yes" : "No",
                "Status" => $user->is_active ? "Active" : "Inactive"
                ];
        })->toArray();
    }
    function collection()
    {
        $company = company_data();
        $this->users[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->users[] = [
            // $company['address'] ?? 'Mirpur, Dhaka',
            "Employee List"
        ];
        $this->users[] = [
            'ID' => "Code",
            'Emp. ID' => "Emp. ID",
            'Name' => "Name",
            'Department' => "Department",
            'Sup. Department' => "Sup. Department",
            'Designation' => "Designation",
            'Mobile' => "Mobile",
            'Join Date' => "Join Date",
            'Shift' => 'Shift',
            'Overtime' => "Overtime",
            "Status" => "Status"
        ];
        return collect($this->users)->merge($this->new_users);
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
