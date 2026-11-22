<?php

namespace App\DataTables;

use App\Models\InventoryPurchase;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class InventoryPurchaseDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', function ($row) {
                return view('components.action-button', [
                    'id' => $row->id,
                    'viewRoute' => route('inventory.purchase.show', $row->id),
                    'editRoute' => route('inventory.purchase.edit', $row->id),
                    'deleteRoute' => route('inventory.purchase.destroy', $row->id),
                ])->render();
            })
            ->editColumn('inventory_supplier_id', function ($row) {
                $supplier = $row->rel_to_inv_supplier;
                return $supplier ? $supplier->name : 'Unknown Supplier';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('Y-m-d'); // Format the date as "YYYY-MM-DD"
            })
            ->filter(function ($query) {
                if ($supplierFilter = request()->get('supplierFilter')) {
                    $query->where('inventory_supplier_id', $supplierFilter);
                }

                if ($dateFilter = request()->get('dateFilter')) {
                    // Single date filtering
                    $query->whereDate('created_at', $dateFilter);
                }
            })
            ->setRowId('id');
    }


    /**
     * Get the query source of dataTable.
     */
    public function query(InventoryPurchase $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('inventory_purchase-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax(route('inventory.purchase.index')) // Pass the route URL here
                    ->ajax([
                        'data' => 'function(d) {
                            d.supplierFilter = $("#supplierFilter").val();
                            d.dateFilter = $("#dateFilter").val();
                        }'
                    ])
                    ->orderBy(5)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('add'),
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload'),
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            // Column::make('id'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),

            Column::make('challan_number'),
            Column::make('ref_no'),
            Column::make('inventory_supplier_id')
                    ->title('Supplier'),
            Column::make('total_cost'),
            Column::make('created_at')
                    ->title('Date'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'InventoryPurchase_' . date('YmdHis');
    }
}
