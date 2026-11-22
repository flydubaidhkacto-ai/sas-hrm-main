<?php

namespace App\Exports;

use App\Models\InterviewEmployee;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Exports\Excel\InterviewEmployeeList as ExcelInterviewEmployeeList;
use Dompdf\Dompdf;

class InterviewEmployeeList implements FromCollection
{
    protected $interview_employees = [];

    function get_interview_employees()
    {
        InterviewEmployee::orderBy('created_at', 'ASC')
            ->chunk(200, function ($__interview_employees) {
                foreach ($__interview_employees as $interview_employee) {
                    $this->interview_employees[] = $interview_employee;
                }
            });
    }

    static function download_excel($data = null, $name = "Interview Employee List.xlsx")
    {
        $th = new self;
        if($data)
            $th->interview_employees = $data;
        else 
            $th->get_interview_employees();
        return (new ExcelInterviewEmployeeList($th->interview_employees))->download($name);
    }

    static function download_pdf($data = null, $name = "Interview Employee List.pdf")
    {
        $th = new self;
        if($data)
            $th->interview_employees = $data;
        else 
            $th->get_interview_employees();
        $html = view('employees_interview.list-table', [
            'employees' => $th->interview_employees,
            'for_export' => true,
        ])->render();
        // echo($html);exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return $dompdf->stream($name);
    }

    static function print($data = null)
    {
        $th = new self;
        if($data)
            $th->interview_employees = $data;
        else 
            $th->get_interview_employees();
        $html = view('employees_interview.list-table', [
            'employees' => $th->interview_employees,
            'for_export' => true,
        ])->render();
        $html .= "<script>
        window.print();
        window.onafterprint = function() {
            // Close the tab after printing is done
            window.close();
        };
        let mediaQueryList = window.matchMedia('print');
        mediaQueryList.addListener(function(mql) {
            if (!mql.matches) {
                if (window && !window.closed) {
                    window.close();
                }
            }
        });
        </script>";
        return $html;
    }
    public function collection()
    {
        return InterviewEmployee::all();
    }
}
