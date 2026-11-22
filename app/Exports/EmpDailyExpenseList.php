<?php

namespace App\Exports;

use App\Models\EmpDailyExpense;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Exports\Excel\EmpDailyExpenseList as ExcelEmpDailyExpenseList;
use Dompdf\Dompdf;

class EmpDailyExpenseList implements FromCollection
{
    protected $emp_daily_expenses = [];

    function get_emp_daily_expenses()
    {
        EmpDailyExpense::orderBy('created_at', 'ASC')
            ->chunk(200, function ($__emp_daily_expenses) {
                foreach ($__emp_daily_expenses as $emp_daily_expense) {
                    $this->emp_daily_expenses[] = $emp_daily_expense;
                }
            });
    }

    static function download_excel($data = null, $name = "Employee Daily Expense.xlsx")
    {
        $th = new self;
        if($data)
            $th->emp_daily_expenses = $data;
        else 
            $th->get_emp_daily_expenses();
        return (new ExcelEmpDailyExpenseList($th->emp_daily_expenses))->download($name);
    }

    static function download_pdf($data = null, $name = "Employee Daily Expense.pdf")
    {
        $th = new self;
        if($data)
            $th->emp_daily_expenses = $data;
        else 
            $th->get_emp_daily_expenses();
        $html = view('emp-expenses.list_table', [
            'expenses' => $th->emp_daily_expenses,
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
            $th->emp_daily_expenses = $data;
        else 
            $th->get_emp_daily_expenses();
        $html = view('emp-expenses.list_table', [
            'expenses' => $th->emp_daily_expenses,
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
        return EmpDailyExpense::all();
    }
}
