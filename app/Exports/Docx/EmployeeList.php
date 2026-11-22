<?php 
namespace App\Exports\Docx;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Paragraph;

class EmployeeList {
    protected $users = [];
    function __construct($users)
    {
        $this->users = $users;
    }
    function download($name = "Employees.docx")
    {
        $html = view('employees.list-table', [
            'employees' => $this->users,
            'for_export' => true,
        ])->render();
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection([
            'orientation' => 'landscape'
        ]);
        // Add big company name
        $info = company_data();
        $companyName = $info['name'] ?? "Hr Payroll";
        $Addres = $info['address'] ?? "MIRPUR 12, DHAKA";
        $testRun = $section->addTextRun(['alignment' => Jc::CENTER]);
        $testRun->addText($companyName, ['size' => 16, 'bold' => true]);
        // $testRun = $section->addTextRun(['alignment' => Jc::CENTER]);
        // $testRun->addText($Addres, ['size' => 11]);
        $testRun = $section->addTextRun(['alignment' => Jc::CENTER]);
        $testRun->addText("Employee List", ['size' => 11]);

        // Define table style
        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 50,
        ];

        // Add a table to the section
        $table = $section->addTable($tableStyle);

        // Add table headers
        $table->addRow();
        $table->addCell(Converter::cmToTwip(2))->addText('Code');
        $table->addCell(Converter::cmToTwip(2))->addText('Emp. ID');
        $table->addCell(Converter::cmToTwip(4))->addText('Name');
        $table->addCell(Converter::cmToTwip(4))->addText('Department');
        $table->addCell(Converter::cmToTwip(4))->addText('Sup. Department');
        $table->addCell(Converter::cmToTwip(4))->addText('Designation');
        $table->addCell(Converter::cmToTwip(4))->addText('Mobile');
        $table->addCell(Converter::cmToTwip(4))->addText('Join Date');
        $table->addCell(Converter::cmToTwip(4))->addText('Shift');
        $table->addCell(Converter::cmToTwip(4))->addText('Overtime');

        // Add table data from the $employees array
        foreach ($this->users as $employee) {
            $table->addRow();
            $table->addCell()->addText($employee->id);
            $table->addCell()->addText($employee->employee_id);
            $table->addCell()->addText($employee->name);
            $table->addCell()->addText($employee->department?->name);
            $table->addCell()->addText($employee->department?->super_department?->name);
            $table->addCell()->addText($employee->designation?->name);
            $table->addCell()->addText($employee->phone_number);
            $table->addCell()->addText(carbon($employee->join_date)->format('d M, Y'));
            $table->addCell()->addText(
                $employee->shift ? (
                    $employee->shift?->name . " - " . carbon($employee->shift->start)->format('h:i A') . " - ". carbon($employee->shift->end)->format('h:i A')
                ) : "--"
            );
            $table->addCell()->addText($employee->can_overtime ? "Yes" : "No");
        }

        $temp_file = tempnam(sys_get_temp_dir(), 'PHPWord');
        $phpWord->save($temp_file);
        header("Content-Disposition: attachment; filename=" . $name);
        readfile($temp_file); // or echo file_get_contents($temp_file);
        unlink($temp_file); 
    }
} 