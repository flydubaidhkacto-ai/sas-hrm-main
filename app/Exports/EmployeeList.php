<?php

namespace App\Exports;

use App\Exports\Docx\EmployeeList as DocxEmployeeList;
use App\Exports\Excel\EmployeeList as ExcelEmployeeList;
use App\Exports\Excel\EmployeeListSSBD;
use App\Exports\PDF\EmployeeList as PDFEmployeeList;
use App\Models\User;
use Dompdf\Dompdf;

class EmployeeList
{
    protected $users = [];
    function get_employees ()
    {
        User::active_employees()
            ->with(['designation', 'department.super_department', 'shift'])
            ->orderBy('name')
            ->chunk(200, function ($__users) {
                foreach ($__users as $user) {
                    $this->users[] = $user;
                }
            });
    }
    static function download_excel($employees = null, $name = "Employees.xlsx")
    {
        $th = new self;
        if($employees)
            $th->users = $employees;
        else
            $th->get_employees();
        if(config('app.customer') === 'ssbd')
            return (new EmployeeListSSBD($th->users))->download($name);
        return (new ExcelEmployeeList($th->users))->download($name);
    }
    static function download_pdf($employees = null, $name = "Employees.pdf")
    {
        $th = new self;
        if($employees)
            $th->users = $employees;
        else
            $th->get_employees();
        $html = view('employees.list-table', [
            'employees' => $th->users,
            'for_export' => true,
        ])->render();
        // echo($html);exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return $dompdf->stream($name);
    }
    static function download_docx($employees = null, $name = "Employees.docx")
    {
        $th = new self;
        if($employees)
            $th->users = $employees;
        else
            $th->get_employees();
        return (new DocxEmployeeList($th->users))->download($name);
    }
    static function print($employees = null)
    {
        $th = new self;
        if($employees)
            $th->users = $employees;
        else
            $th->get_employees();
        $html = view('employees.list-table', [
            'employees' => $th->users,
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
