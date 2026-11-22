<?php

namespace App\Exports;

use App\Models\LeaveApplication;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Exports\Excel\LeaveApplicationList as ExcelLeaveApplicationList;
use Dompdf\Dompdf;
use Dompdf\Options;

class LeaveApplicationList implements FromCollection
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

    static function download_excel($data = null, $name = "Leave Application.xlsx")
    {
        $th = new self;
        if($data)
            $th->leave_applications = $data;
        else
            $th->get_leave_applications();
        return (new ExcelLeaveApplicationList($th->leave_applications))->download($name);
    }

    static function download_pdf($data = null, $name = "Leave Application.pdf")
    {
        $th = new self;
        if($data)
            $th->leave_applications = $data;
        else
            $th->get_leave_applications();
        $html = view('leaves.all_table', [
            'data' => $th->leave_applications,
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
            $th->leave_applications = $data;
        else
            $th->get_leave_applications();
        $html = view('leaves.all_table', [
            'data' => $th->leave_applications,
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
