<?php

namespace App\Exports;

// use App\Exports\Docx\EmployeeList as DocxEmployeeList;
use App\Exports\Excel\LoansExportList as ExcelLoansList;
// use App\Exports\PDF\EmployeeList as PDFEmployeeList;
use Maatwebsite\Excel\Concerns\FromCollection;
use Dompdf\Dompdf;
use App\Models\Loan;

class LoansExportList implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */


    protected $loans = [];

    function get_loans()
    {
        Loan::orderBy('created_at', 'desc') // Order by latest loan entries
            ->chunk(200, function ($__loans) {
                foreach ($__loans as $loan) {
                    $this->loans[] = $loan;
                }
            });
    }

    static function download_excel($data = null, $name = "Loans.xlsx")
    {
        $th = new self;
        if($data)
            $th->loans = $data;
        else 
            $th->get_loans();
        return (new ExcelLoansList($th->loans))->download($name);
    }
    static function download_pdf($data = null, $name = "Loans.pdf")
    {
        $th = new self;
        
        if ($data) {
            $th->loans = $data;
        } else {
            $th->get_loans();
        }

        $html = view('loans.list_table', [ 
            'data' => $th->loans,
            'for_export' => true,
        ])->render();

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
            $th->loans = $data;
        else 
            $th->get_loans();
            $html = view('loans.list_table', [ 
                'data' => $th->loans,
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
        return Loan::all();
    }
}
