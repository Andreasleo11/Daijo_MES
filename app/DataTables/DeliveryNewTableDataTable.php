<?php

namespace App\DataTables;

use App\Models\Delivery\DelschedFinal;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class DeliveryNewTableDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'deliverynewtable.action')
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\DeliveryNewTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(DelschedFinal $model): QueryBuilder
    {
        
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('deliverynewtable-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('<"flex justify-between items-center mb-4"lf>t<"flex justify-between items-center mt-4"ip>')
                    ->orderBy(1,'asc')
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    ])
                    ->language([
                        'paginate' => [
                            'previous' => '←',
                            'next' => '→',
                        ],
                        'search' => 'Search:',
                        'lengthMenu' => 'Show _MENU_ entries',
                        'info' => 'Showing _START_ to _END_ of _TOTAL_ entries',
                    ])
                    ->parameters([
                        'columnDefs' => [
                            ['className' => 'px-4 py-2 border', 'targets' => '_all'], // Tailwind padding for all columns
                        ],
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return [
            Column::make('status')->data('status')->renderRaw('function(data, type, row, meta){
                if (type === \'display\') {
                    if (data === "Finish") {
                        return \'<span class="inline-block rounded-full bg-green-500 text-white px-3 py-1 text-sm font-medium">Finish</span>\';
                    } else if (data === "Danger") {
                        return \'<span class="inline-block rounded-full bg-red-500 text-white px-3 py-1 text-sm font-medium">Danger</span>\';
                    } else if (data === "Warning") {
                        return \'<span class="inline-block rounded-full bg-yellow-500 text-white px-3 py-1 text-sm font-medium">Warning</span>\';
                    }
                }
                return data; // Return the original data for other types
            }'),
            Column::make('id'),
            Column::make('so_number'),
            Column::make('delivery_date'),
            Column::make('customer_code'),
            Column::make('customer_name'),
            Column::make('item_code'),
            Column::make('item_name'),
            Column::make('departement'),
            Column::make('delivery_qty'),
            Column::make('delivered'),
            Column::make('outstanding'),
            Column::make('customer_code'),
            Column::make('stock'),
            Column::make('balance'),
            Column::make('outstanding_stk'),
            Column::make('packaging_code'),
            Column::make('standar_pack'),
            Column::make('packaging_qty'),
            Column::make('doc_status'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'DeliveryNewTable_' . date('YmdHis');
    }
}
