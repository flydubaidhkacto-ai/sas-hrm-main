<?php
namespace App\Exports\Docx;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Paragraph;

class LateReport {
    protected $attendances = [];
    function __construct($attendances)
    {
        $this->attendances = $attendances;
    }
    function download($name = "Late Report.docx")
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
        $testRun->addText("Late Report", ['size' => 11]);

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
        $table->addCell(Converter::cmToTwip(4))->addText('Late');
        $table->addCell(Converter::cmToTwip(4))->addText('Punched In');
        $table->addCell(Converter::cmToTwip(4))->addText('Shift');
        $table->addCell(Converter::cmToTwip(4))->addText('Shift started');
        $table->addCell(Converter::cmToTwip(4))->addText('Time Missed');

        // Add table data from the $employees array
        foreach ($this->attendances as $item) {
            $table->addRow();
            $table->addCell()->addText($item->employee?->id);
            $table->addCell()->addText($item->employee?->employee_id);
            $table->addCell()->addText($item->employee?->name);
            $table->addCell()->addText($item->employee?->department?->name);
            $table->addCell()->addText($item->employee?->department?->super_department?->name);
            $table->addCell()->addText($item->employee?->designation?->name);
            $table->addCell()->addText(carbon($item->date)->format('d M, Y'));
            $table->addCell()->addText(carbon($item->worked_from)->format('h:i A'));
            $table->addCell()->addText($item->shift?->name);
            $table->addCell()->addText($item->shift ? (
                carbon($item->shift->start)->format('h:i A')
                ) : "Not Assigned");
            $table->addCell()->addText(carbon($item->late_time ?? '00:00:00')->format('H:i'));
        }

        $temp_file = tempnam(sys_get_temp_dir(), 'PHPWord');
        $phpWord->save($temp_file);
        header("Content-Disposition: attachment; filename=" . $name);
        readfile($temp_file); // or echo file_get_contents($temp_file);
        unlink($temp_file);
    }
}
