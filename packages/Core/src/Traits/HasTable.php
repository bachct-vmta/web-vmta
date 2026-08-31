<?php

namespace Packages\Core\Src\Traits;

use Illuminate\View\View;
use Packages\Core\Src\Tables\Table;

/**
 * Trait for controllers that use Table Builder
 *
 * Provides helper methods for rendering tables
 */
trait HasTable
{
    /**
     * Render a table view
     *
     * @param  Table  $table  The table instance
     * @param  string  $view  The view to render (defaults to table's internal view)
     * @param  array  $data  Additional data to pass to the view
     */
    protected function table(Table $table, ?string $view = null, array $data = []): View
    {
        $records = $table->getRecords();

        if ($view) {
            return view($view, array_merge([
                'table' => $table,
                'records' => $records,
            ], $data));
        }

        return $table->render();
    }

    /**
     * Render a table within an existing view
     *
     * @param  string  $view  The view to render
     * @param  Table  $table  The table instance
     * @param  array  $data  Additional data to pass to the view
     */
    protected function viewWithTable(string $view, Table $table, array $data = []): View
    {
        return view($view, array_merge([
            'table' => $table,
            'records' => $table->getRecords(),
        ], $data));
    }
}
