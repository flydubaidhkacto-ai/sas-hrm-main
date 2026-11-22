<?php 
namespace App\Exports;

use App\Exports\Docx\AttendanceReport as DocxAttendanceReport;
use App\Exports\Excel\AttendanceReport as ExcelAttendanceReport;
use App\Models\Attendance;
use Dompdf\Dompdf;

class AttendanceReport
{
    protected $attendances = [];
    function get_attendances ()
    {
        Attendance::query()
            ->with(['employee.designation', 'employee.department.super_department', 'employee.shift', 'shift'])
            ->whereDate('date', '<=', now()->format('Y-m-d'))
            ->whereDate('date', '>=', now()->subMonth()->format('Y-m-d'))
            ->chunk(200, function ($__attendances) {
                foreach ($__attendances as $user) {
                    $this->attendances[] = $user;
                }
            });
    }
    static function download_excel($attendances = null, $name = "Attendances.xlsx")
    {
        $th = new self;
        if($attendances)
            $th->attendances = $attendances;
        else 
            $th->get_attendances();
        return (new ExcelAttendanceReport($th->attendances))->download($name);
    }
    static function download_pdf($attendances = null, $name = "Attendances.pdf")
    {
        $th = new self;
        if($attendances)
            $th->attendances = $attendances;
        else 
            $th->get_attendances();
        $html = view('reports.attendance-table', [
            'data' => $th->attendances,
            'for_export' => true,
        ])->render();
        // echo($html);exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return $dompdf->stream($name);
    }
    static function download_docx($attendances = null, $name = "Attendances.docx")
    {
        $th = new self;
        if($attendances)
            $th->attendances = $attendances;
        else 
            $th->get_attendances();
        return (new DocxAttendanceReport($th->attendances))->download($name);
    }
    static function print($attendances = null)
    {
        $th = new self;
        if($attendances)
            $th->attendances = $attendances;
        else 
            $th->get_attendances();
        $html = view('reports.attendance-table', [
            'data' => $th->attendances,
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
}