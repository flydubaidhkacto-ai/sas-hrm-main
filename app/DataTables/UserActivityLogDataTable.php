<?php

namespace App\DataTables;

use App\Models\UserActivityLog;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UserActivityLogDataTable extends DataTable
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
                    'deleteRoute' => route('half-day-fines.destroy', $row->id),
                ])->render();
            })
            ->editColumn('status', function ($row) {

                switch ($row->status) {
                    case 0:
                        return 'Visited';
                    case 1:
                        return 'Created';
                    case 2:
                        return 'Updated';
                    case 3:
                        return 'Deleted';
                    default:
                        return 'Unknown';
                }
            })

            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(UserActivityLog $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('useractivitylog-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
            Column::make('id'),
            Column::make('model_name'),
            Column::make('status'),
            // Column::make('visited_section_name'),
            Column::make('operation_data'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'UserActivityLog_' . date('YmdHis');
    }
}
