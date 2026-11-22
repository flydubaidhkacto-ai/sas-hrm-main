<?php 
namespace App\Exports\Docx;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Paragraph;

class AttendanceReport {
    protected $attendances = [];
    function __construct($attendances)
    {
        $this->attendances = $attendances;
    }
    function download($name = "Attendances.docx")
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
        $testRun->addText("Attendance Report", ['size' => 11]);

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
        $table->addCell(Converter::cmToTwip(4))->addText('In Time');
        $table->addCell(Converter::cmToTwip(4))->addText('Out Time');
        $table->addCell(Converter::cmToTwip(4))->addText('W. Hour');
        $table->addCell(Converter::cmToTwip(4))->addText('O.T.');
        $table->addCell(Converter::cmToTwip(4))->addText('Notes');
        $table->addCell(Converter::cmToTwip(4))->addText('Duty Type');

        // Add table data from the $employees array
        foreach ($this->attendances as $item) {
            $table->addRow();
            $table->addCell()->addText($item->employee?->id);
            $table->addCell()->addText($item->employee?->employee_id);
            $table->addCell()->addText($item->employee?->name);
            $table->addCell()->addText($item->employee?->department?->name);
            $table->addCell()->addText($item->employee?->department?->super_department?->name);
            $table->addCell()->addText($item->employee?->designation?->name);
            $date = carbon($item->date)->format('d F');
            if($item->added_manually_by)
            {
                $user = \App\Models\User::find($item->added_manually_by);
                if($user)
                {
                    $date .= "\nAdded manually By: " . $user->name;
                }
            }
            $table->addCell()->addText($date);
            $table->addCell()->addText(
                $item->check_in ? carbon($item->check_in ?? '00:00')->format('h:i A') : "--:--"
            );
            $table->addCell()->addText(
                $item->check_out ? carbon($item->check_out ?? '00:00')->format('d M, Y h:i A') : '--:--'
            );
            $table->addCell()->addText(
                $item->working_time ? carbon($item->working_time ?? '00:00')->format('d M, Y h:i A') : '--:--'
            );
            $table->addCell()->addText(
                $item->overtime_time ? carbon($item->overtime_time ?? '00:00')->format('d M, Y h:i A') : '--:--'
            );
            $table->addCell()->addText($item->notes ?? '--');
            $table->addCell()->addText(
                $item->duty_type ?? '--'
            );
        }

        $temp_file = tempnam(sys_get_temp_dir(), 'PHPWord');
        $phpWord->save($temp_file);
        header("Content-Disposition: attachment; filename=" . $name);
        readfile($temp_file); // or echo file_get_contents($temp_file);
        unlink($temp_file); 
    }
} 