<?php

namespace App\Exports;

use App\Models\LeaveApplication;
use Maatwebsite\Excel\Concerns\FromCollection;
use Dompdf\Dompdf;
use Dompdf\Options;

class EmpLeaveReport implements FromCollection
{

    protected $leave_applications = [];

    function get_leave_applications()
    {
        LeaveApplication::orderBy('created_at', 'ASC')
            ->chunk(200, function ($__leave_applications) {
                foreach ($__leave_applications as $leave_application) {
                    $this->leave_applications[] = $leave_application;
                }
            });
    }


    static function download_pdf($data = null, $employee = null, $start_date = null, $end_date = null, $name = "Leave Application.pdf")
    {
        $th = new self;
        if($data)
            $th->leave_applications = $data;
        else
            $th->get_leave_applications();
        $html = view('leaves.emp_leave_report_table', [
            'data' => $th->leave_applications,
            'employee' => $employee,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'for_export' => true,
        ])->render();
        // echo($html);exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->stream($name);
    }

    static function print($data = null, $employee = null, $start_date = null, $end_date = null)
    {
        $th = new self;
        if($data)
            $th->leave_applications = $data;
        else
            $th->get_leave_applications();
        $html = view('leaves.emp_leave_report_table', [
            'data' => $th->leave_applications,
            'employee' => $employee,
            'start_date' => $start_date,
            'end_date' => $end_date,
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
        return LeaveApplication::all();
    }
}
