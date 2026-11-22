<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
// use App\Exports\Excel\LeaveApplicationList as ExcelLeaveApplicationList;
use Dompdf\Dompdf;
use Dompdf\Options;

class LeaveBalanceList implements FromCollection
{

    protected $leave_balances = [];

    function get_leave_balances()
    {
        User::active_employees()->orderBy('id')
            ->chunk(200, function ($__leave_balances) {
                foreach ($__leave_balances as $leave_balance) {
                    $this->leave_balances[] = $leave_balance;
                }
            });
    }

    // static function download_excel($data = null, $name = "Leave Application.xlsx")
    // {
    //     $th = new self;
    //     if($data)
    //         $th->leave_balances = $data;
    //     else
    //         $th->get_leave_balances();
    //     return (new ExcelLeaveApplicationList($th->leave_balances))->download($name);
    // }

    static function download_pdf($data = null, $types = null, $year = null, $name = "Leave Balance.pdf")
    {
        $th = new self;
        if($data && $types && $year)
            $th->leave_balances = $data;
        else
            $th->get_leave_balances();
        $html = view('leaves.list_balance', [
            'employees' => $th->leave_balances,
            'types' => $types,
            'year' => $year,
            'for_export' => true,
        ])->render();
        // echo($html);exit;
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        return $dompdf->stream($name);
    }

    static function print($data = null, $types = null, $year = null)
    {
        $th = new self;
        if($data && $types && $year)
            $th->leave_balances = $data;
        else
            $th->get_leave_balances();
        $html = view('leaves.list_balance', [
            'employees' => $th->leave_balances,
            'types' => $types,
            'year' => $year,
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
