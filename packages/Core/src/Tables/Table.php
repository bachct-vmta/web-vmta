<?php

namespace Packages\Core\Src\Tables;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\Columns\BaseColumn;
use Packages\Core\Src\Tables\Filters\BaseFilter;

/**
 * Main Table Builder class
 *
 * Usage:
 * Table::make(User::query())
 *     ->columns([...])
 *     ->filters([...])
 *     ->actions([...])
 *     ->paginate(15);
 */
class Table
{
    protected Builder $query;

    protected array $columns = [];

    protected array $filters = [];

    protected array $actions = [];

    protected array $bulkActions = [];

    protected int $perPage = 15;

    protected ?string $defaultSort = null;

    protected string $defaultSortDirection = 'desc';

    protected bool $searchable = true;

    protected array $searchColumns = [];

    protected ?string $heading = null;

    protected string $headingIcon = 'group';

    protected ?string $description = null;

    protected ?string $emptyMessage = null;

    protected bool $striped = false;

    protected ?string $recordUrl = null;

    /**
     * Create a new Table instance
     */
    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * Static factory method
     */
    public static function make(Builder $query): static
    {
        return new static($query);
    }

    /**
     * Set the columns
     *
     * @param  BaseColumn[]  $columns
     */
    public function columns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Get all columns
     *
     * @return BaseColumn[]
     */
    public function getColumns(): array
    {
        return array_filter($this->columns, fn (BaseColumn $col) => $col->isVisible());
    }

    /**
     * Get searchable column names
     */
    public function getSearchableColumns(): array
    {
        if (! empty($this->searchColumns)) {
            return $this->searchColumns;
        }

        return collect($this->columns)
            ->filter(fn (BaseColumn $col) => $col->isSearchable())
            ->map(fn (BaseColumn $col) => $col->getName())
            ->values()
            ->toArray();
    }

    /**
     * Set the filters
     *
     * @param  BaseFilter[]  $filters
     */
    public function filters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Get all filters
     *
     * @return BaseFilter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Set the row actions
     *
     * @param  Action[]  $actions
     */
    public function actions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * Get all actions
     *
     * @return Action[]
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Set bulk actions
     *
     * @param  BulkAction[]  $bulkActions
     */
    public function bulkActions(array $bulkActions): static
    {
        $this->bulkActions = $bulkActions;

        return $this;
    }

    /**
     * Get bulk actions
     *
     * @return BulkAction[]
     */
    public function getBulkActions(): array
    {
        return array_filter($this->bulkActions, fn (BulkAction $action) => $action->isVisible());
    }

    /**
     * Set pagination
     */
    public function paginate(int $perPage = 15): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * Set default sort
     */
    public function defaultSort(string $column, string $direction = 'desc'): static
    {
        $this->defaultSort = $column;
        $this->defaultSortDirection = $direction;

        return $this;
    }

    /**
     * Enable/disable global search
     */
    public function searchable(bool $searchable = true, array $columns = []): static
    {
        $this->searchable = $searchable;
        $this->searchColumns = $columns;

        return $this;
    }

    /**
     * Set table heading
     */
    public function heading(string $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    /**
     * Set table heading icon (Material Symbols name)
     */
    public function headingIcon(string $icon): static
    {
        $this->headingIcon = $icon;

        return $this;
    }

    /**
     * Get heading icon
     */
    public function getHeadingIcon(): string
    {
        return $this->headingIcon;
    }

    /**
     * Set table description
     */
    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set empty state message
     */
    public function emptyMessage(string $message): static
    {
        $this->emptyMessage = $message;

        return $this;
    }

    /**
     * Enable striped rows
     */
    public function striped(bool $striped = true): static
    {
        $this->striped = $striped;

        return $this;
    }

    /**
     * Check if striped
     */
    public function isStriped(): bool
    {
        return $this->striped;
    }

    /**
     * Get heading
     */
    public function getHeading(): ?string
    {
        return $this->heading;
    }

    /**
     * Get description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get empty message
     */
    public function getEmptyMessage(): string
    {
        return $this->emptyMessage ?? 'Không có dữ liệu';
    }

    /**
     * Apply search to query
     */
    protected function applySearch(Builder $query, ?string $search): Builder
    {
        if (! $search || ! $this->searchable) {
            return $query;
        }

        $searchColumns = $this->getSearchableColumns();

        if (empty($searchColumns)) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $searchColumns) {
            foreach ($searchColumns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';

                // Handle relationship columns (dot notation)
                if (str_contains($column, '.')) {
                    [$relation, $field] = explode('.', $column, 2);
                    $q->$method(function ($sq) use ($relation, $field, $search) {
                        $sq->whereHas($relation, function ($rq) use ($field, $search) {
                            $rq->where($field, 'like', "%{$search}%");
                        });
                    });
                } else {
                    $q->$method($column, 'like', "%{$search}%");
                }
            }
        });
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters(Builder $query): Builder
    {
        foreach ($this->filters as $filter) {
            if ($filter->hasValue()) {
                $query = $filter->apply($query, $filter->getValue());
            }
        }

        return $query;
    }

    /**
     * Apply sorting to query
     */
    protected function applySorting(Builder $query): Builder
    {
        $sortBy = request()->get('sort', $this->defaultSort);
        $sortDir = request()->get('direction', $this->defaultSortDirection);

        if ($sortBy) {
            // Check if column is sortable
            $column = collect($this->columns)
                ->first(fn (BaseColumn $col) => $col->getName() === $sortBy && $col->isSortable());

            if ($column) {
                // Handle relationship sorting
                if (str_contains($sortBy, '.')) {
                    // For now, skip relationship sorting (complex)
                    // Can be enhanced later with joins
                } else {
                    $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
                }
            }
        }

        return $query;
    }

    /**
     * Get the records
     */
    public function getRecords(): LengthAwarePaginator
    {
        $query = clone $this->query;

        // Apply search
        $query = $this->applySearch($query, request()->get('search'));

        // Apply filters
        $query = $this->applyFilters($query);

        // Apply sorting
        $query = $this->applySorting($query);

        return $query->paginate($this->perPage)->withQueryString();
    }

    /**
     * Check if search is enabled
     */
    public function isSearchable(): bool
    {
        return $this->searchable && ! empty($this->getSearchableColumns());
    }

    /**
     * Get current sort column
     */
    public function getCurrentSort(): ?string
    {
        return request()->get('sort', $this->defaultSort);
    }

    /**
     * Get current sort direction
     */
    public function getCurrentSortDirection(): string
    {
        return request()->get('direction', $this->defaultSortDirection);
    }

    /**
     * Get sort URL for a column
     */
    public function getSortUrl(BaseColumn $column): string
    {
        $currentSort = $this->getCurrentSort();
        $currentDir = $this->getCurrentSortDirection();

        $newDir = ($currentSort === $column->getName() && $currentDir === 'asc')
            ? 'desc'
            : 'asc';

        return request()->fullUrlWithQuery([
            'sort' => $column->getName(),
            'direction' => $newDir,
        ]);
    }

    /**
     * Check if column is currently sorted
     */
    public function isColumnSorted(BaseColumn $column): bool
    {
        return $this->getCurrentSort() === $column->getName();
    }

    /**
     * Render the table (returns view)
     */
    public function render()
    {
        return view('core::components.table.index', [
            'table' => $this,
            'records' => $this->getRecords(),
        ]);
    }

    /**
     * Convert to string (render)
     */
    public function __toString(): string
    {
        return $this->render()->render();
    }
}
