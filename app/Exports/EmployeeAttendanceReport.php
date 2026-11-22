<?php 
namespace App\Exports;

use App\Exports\Docx\EmployeeAttendanceReport as DocxEmployeeAttendanceReport;
use App\Exports\Excel\EmployeeAttendanceReport as ExcelEmployeeAttendanceReport;
use Dompdf\Dompdf;

class EmployeeAttendanceReport
{
    static function download_pdf($data, $employee, $from, $to, $name = "Employee attendance.pdf")
    {
        $html = view('reports.attendance-employee-table', [
            'employee' => $employee,
            'data' => $data,
            'from' => $from,
            'to' => $to,
            'for_export' => true,
        ])->render();
        // echo($html);exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->stream($name);
    }
    static function print($data, $employee, $from, $to)
    {
        $html = view('reports.attendance-employee-table', [
            'employee' => $employee,
            'data' => $data,
            'from' => $from,
            'to' => $to,
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