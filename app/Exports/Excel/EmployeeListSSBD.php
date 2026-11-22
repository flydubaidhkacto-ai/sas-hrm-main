<?php

namespace App\Exports\Excel;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class EmployeeListSSBD implements FromCollection, WithStyles, ShouldAutoSize, WithDrawings
{
    use Exportable;

    protected $new_users = [];
    protected $company;

    public function __construct($users)
    {
        $this->company = company_data();
        $i = 0;
        $this->new_users = collect($users)->map(function ($user) use(&$i) {
            return [
                "ID" => ++$i . " ",
                "Emp. ID" => $user->id,
                "Name" => $user->name,
                "Department" => $user->department?->name,
                "Designation" => $user->designation?->name,
                "Mobile" => $user->phone_number,
                'Sector/Line' => "",
                'Floor' => "",
                'Staff/Worker' => "",
                'Emp. Type' => $user->employee_type,
                'Joining Date' => carbon($user->join_date)->format('d M, Y'),
                'Discontinuation' => $user->is_active ? 'No' : (
                    $user->schedule_termination_at ? carbon($user->schedule_termination_at)->format('d M, Y h:i A') : 'Yes'
                ),
                'Grade' => "",
                'Account No' => $user->account_number,
                'Bank Name' => $user->bank_name,
                'Att. Card No.' => "",
                'Contact No' => $user->phone_number,
                'Date of Birth' => $user->birth_date,
                'Gender' => $user->gender,
                'Religion' => $user->religion,
                'National ID' => $user->nid_or_passport,
                'Blood Grp.' => $user->blood_group,
                'Emergency Cont. No' => $user->emergency_phone,
                'Permanent Address' => $user->permanent_address,
                'Present Address' => $user->address,
            ];
        });
    }

    public function collection()
    {
        // Leave first 4 rows empty for header area
        $headerRow = [[
            'ID' => "  SL ",
            'Emp. ID' => "Emp. ID",
            'Name' => "Name",
            'Designation' => "Designation",
            'Department' => "Department",
            'Mobile' => "Mobile",
            'Sector/Line' => "Sector/Line",
            'Floor' => "Floor",
            'Staff/Worker' => "Staff/Worker",
            'Emp. Type' => "Emp. Type",
            'Joining Date' => "Joining Date",
            'Discontinuation' => "Discontinuation",
            'Grade' => "Grade",
            'Account No' => "Account No",
            'Bank Name' => "Bank Name",
            'Att. Card No.' => "Att. Card No.",
            'Contact No' => "Contact No",
            'Date of Birth' => "Date of Birth",
            'Gender' => "Gender",
            'Religion' => "Religion",
            'National ID' => "National ID",
            'Blood Grp.' => "Blood Grp.",
            'Emergency Cont. No' => "Emergency Cont. No",
            'Permanent Address' => "Permanent Address",
            'Present Address' => "Present Address",
        ]];

        // Pad with 4 empty rows so data starts at row 5
        $padding = array_fill(0, 4, array_fill(0, 11, null));

        return collect($padding)->merge($headerRow)->merge($this->new_users);
    }

    public function drawings()
    {
        // Your base64 logo (replace with actual one)
        $imagePath = storage_path('app/files/ssbd_excel_logo.png');

        $drawing = new Drawing();
        $drawing->setName('Company Logo');
        $drawing->setDescription('Company Logo');
        $drawing->setPath($imagePath);
        $drawing->setHeight(60);

        // Position logo in top-left corner (A1)
        $drawing->setCoordinates('A1');

        // Add padding (offset) in pixels
        $drawing->setOffsetX(10); // left margin
        $drawing->setOffsetY(10); // top margin

        return [$drawing];
    }

    public function styles(Worksheet $sheet)
    {
        // Fix first two columns width for logo area
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);

        // Merge cells for layout
        $sheet->mergeCells('A1:B4'); // Logo area
        $sheet->mergeCells('C1:AA2'); // Company name
        $sheet->mergeCells('C3:AA3'); // Address
        $sheet->mergeCells('C4:AA4'); // Address

        // Style company name
        $sheet->getStyle('C1:H2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 18,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style address
        $sheet->getStyle('C3:H3')->applyFromArray([
            'font' => [
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Fill text
        $sheet->setCellValue('C1', strtoupper('SAJNIA SOURCING BD LTD.'));
        $sheet->setCellValue('C3', 'Factory: 15-16 (4th Floor), Road 4, Section 7, Mirpur I/A, Dhaka-1216, Bangladesh');

        // Remove borders for top rows
        foreach (range(1, 4) as $row) {
            $sheet->getStyle("A{$row}:K{$row}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);
        }

        // Bold header row (row 5)
        $sheet->getStyle('A5:K5')->applyFromArray([
            'font' => ['bold' => true],
        ]);

        return [];
    }
}

