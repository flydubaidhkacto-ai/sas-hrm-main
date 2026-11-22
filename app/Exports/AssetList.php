<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Exports\Excel\AssetList as ExcelAssetList;
use Dompdf\Dompdf;

class AssetList implements FromCollection
{
    protected $assets = [];

    function get_assets()
    {
        Asset::orderBy('created_at', 'ASC')
            ->chunk(200, function ($__assets) {
                foreach ($__assets as $asset) {
                    $this->assets[] = $asset;
                }
            });
    }

    static function download_excel($data = null, $name = "Asset List.xlsx")
    {
        $th = new self;
        if($data)
            $th->assets = $data;
        else
            $th->get_assets();
        return (new ExcelAssetList($th->assets))->download($name);
    }

    static function download_pdf($data = null, $name = "Asset List.pdf")
    {
        $th = new self;
        if($data)
            $th->assets = $data;
        else
            $th->get_assets();
        $html = view('asset.export_table', [
            'data' => $th->assets,
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
            $th->assets = $data;
        else
            $th->get_assets();
        $html = view('asset.export_table', [
            'data' => $th->assets,
            'for_export' => true,
        ])->render();
        $html .= "<script>
        window.print();
        window.onafterprint = function() {
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
        return Asset::all();
    }
}
