<?php

namespace App\Exports\Excel;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AssetList implements FromCollection, WithStyles
{
    use Exportable;
    protected $chunk_data = [];
    protected $assets = [];
    protected $new_assets = [];
    function __construct($assets)
    {
        $this->new_assets = collect($assets)->map(function ($asset) {
            return [
                $asset->id,
                $asset->name,
                $asset->tag,
                $asset->asset_category?->name ?? 'N/A',
                $asset->user ?? 'N/A',
                $asset->super_department?->name ?? ($asset->location ?? 'N/A'),
                $asset->purchase_value ?? 'N/A',
                $asset->present_value ?? 'N/A',
                $asset->status == 1 ? 'Active' : 'Inactive',
            ];
        })->toArray();
    }
    function collection()
    {
        $company = company_data();
        $this->assets[] = [
            $company['name'] ?? "Hr payroll",
        ];
        $this->assets[] = [
            "Asset List"
        ];
        $this->assets[] = [
            "ID", "Name", "Tag", "Category", "User", "Location", "Purchase Value", "Present Value", "Status"
        ];
        return collect($this->assets)->merge($this->new_assets);
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 17],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            2 => [
                'font' => ['size' => 14],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            3 => ['font' => ['bold' => true]],
        ];
    }
}
