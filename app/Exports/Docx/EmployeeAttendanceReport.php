<?php 
namespace App\Exports\Docx;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Paragraph;

class EmployeeAttendanceReport {
    
    function __construct(public $data, public $employee, public $from, public $to)
    {
        
    }
    function download($name = "Employee Attendances.docx")
    {
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
        $testRun->addText("Employee Attendance Report", ['size' => 11]);

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
        $table->addCell(Converter::cmToTwip(4))->addText('Date');
        $table->addCell(Converter::cmToTwip(4))->addText('Status');
        $table->addCell(Converter::cmToTwip(4))->addText('In Time');
        $table->addCell(Converter::cmToTwip(4))->addText('Out Time');
        $table->addCell(Converter::cmToTwip(4))->addText('W. Hour');
        $table->addCell(Converter::cmToTwip(4))->addText('O.T.');

        // Add table data from the $employees array
        foreach ($this->data as $time => $item) 
        {
            $table->addRow();
            $table->addCell()->addText($this->employee?->id);
            $table->addCell()->addText($this->employee?->employee_id);
            $table->addCell()->addText($this->employee?->name);
            $table->addCell()->addText($this->employee?->department?->name);
            $table->addCell()->addText($this->employee?->department?->super_department?->name);
            $table->addCell()->addText($this->employee?->designation?->name);
            $table->addCell()->addText(date('d M, Y', $time));
            $table->addCell()->addText(
                $item['attendance'] ?? false ? 'Present' : (
                    $item['leave'] ?? false ? 'On Leave' : (
                        $item['absent'] ?? false ? 'Absent' : (
                            $item['weekend'] ?? false ? 'Weekend' : 'Absent'
                        )
                    )
                )
            );
            $table->addCell()->addText(
                ($item['attendance'] ?? false) && $item['attendance']->worked_from 
                    ? carbon($item['attendance']->worked_from ?? '00:00')->format('h:i A') : "--:--"
            );
            $table->addCell()->addText(
                ($item['attendance'] ?? false) && $item['attendance']->worked_to 
                    ? carbon($item['attendance']->worked_to ?? '00:00')->format('d M, Y h:i A') : '--:--'
            );
            $table->addCell()->addText(
                ($item['attendance'] ?? false) && $item['attendance']->working_time 
                    ? carbon($item['attendance']->working_time ?? '00:00')->format('H:i') : '--:--'
            );
            $table->addCell()->addText(
                ($item['attendance'] ?? false) && $item['attendance']->overtime_time 
                    ? carbon($item['attendance']->overtime_time ?? '00:00')->format('H:i') : '--:--'
            );
        }

        $temp_file = tempnam(sys_get_temp_dir(), 'PHPWord');
        $phpWord->save($temp_file);
        header("Content-Disposition: attachment; filename=" . $name);
        readfile($temp_file); // or echo file_get_contents($temp_file);
        unlink($temp_file); 
    }
} 