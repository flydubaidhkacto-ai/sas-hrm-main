<?php

namespace App\Exports;

use App\Models\EmployeeReward;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Exports\Excel\EmployeeRewardList as ExcelEmployeeRewardList;
use Dompdf\Dompdf;

class EmployeeRewardList implements FromCollection
{
    protected $rewards = [];

    function get_rewards()
    {
        EmployeeReward::orderBy('created_at', 'ASC')
            ->chunk(200, function ($__rewards) {
                foreach ($__rewards as $reward) {
                    $this->rewards[] = $reward;
                }
            });
    }

    static function download_excel($data = null, $name = "Reward.xlsx")
    {
        $th = new self;
        if($data)
            $th->rewards = $data;
        else 
            $th->get_rewards();
        return (new ExcelEmployeeRewardList($th->rewards))->download($name);
    }

    static function download_pdf($data = null, $name = "Reward.pdf")
    {
        $th = new self;
        if($data)
            $th->rewards = $data;
        else 
            $th->get_rewards();
        $html = view('rewards.list_table', [
            'data' => $th->rewards,
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
            $th->rewards = $data;
        else 
            $th->get_rewards();
        $html = view('rewards.list_table', [
            'data' => $th->rewards,
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
        return EmployeeReward::all();
    }
}
