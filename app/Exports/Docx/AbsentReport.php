<?php 
namespace App\Exports\Docx;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Paragraph;

class AbsentReport {
    
    function __construct(public $attendances, public $from, public $to)
    {

    }
    function download($name = "Absent Report.docx")
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
        $testRun->addText("Absent Report", ['size' => 11]);

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
        $table->addCell(Converter::cmToTwip(2))->addText('Date');
        $table->addCell(Converter::cmToTwip(2))->addText('Code');
        $table->addCell(Converter::cmToTwip(2))->addText('Emp. ID');
        $table->addCell(Converter::cmToTwip(4))->addText('Name');
        $table->addCell(Converter::cmToTwip(4))->addText('Department');
        $table->addCell(Converter::cmToTwip(4))->addText('Sup. Department');
        $table->addCell(Converter::cmToTwip(4))->addText('Designation');
        $table->addCell(Converter::cmToTwip(4))->addText('Phone');

        $currentDate = $this->from->copy();
        while ($currentDate->lte($this->to))
        {
            $emps = $this->attendances[$currentDate->format('Y_m_d')];
            foreach ($emps as $employee)
            {
                $table->addRow();     
                $table->addCell()->addText($currentDate->format('d F, Y'));
                $table->addCell()->addText($employee->id);
                $table->addCell()->addText($employee->employee_id);
                $table->addCell()->addText($employee->name);
                $table->addCell()->addText($employee->department?->name);
                $table->addCell()->addText($employee->department?->super_department?->name);
                $table->addCell()->addText($employee->designation?->name);
                $table->addCell()->addText($employee->phone_number);     
            }
            $currentDate->addDay();
        }

        $temp_file = tempnam(sys_get_temp_dir(), 'PHPWord');
        $phpWord->save($temp_file);
        header("Content-Disposition: attachment; filename=" . $name);
        readfile($temp_file); // or echo file_get_contents($temp_file);
        unlink($temp_file); 
    }
} 