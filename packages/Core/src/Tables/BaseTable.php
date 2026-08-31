<?php

namespace Packages\Core\Src\Tables;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\Columns\BaseColumn;
use Packages\Core\Src\Tables\Filters\BaseFilter;

/**
 * Abstract base class for reusable table definitions
 *
 * Usage:
 * 1. Create a class extending BaseTable
 * 2. Implement required methods: model(), columns()
 * 3. Optionally override: filters(), actions(), bulkActions()
 * 4. Inject into Controller and call render()
 *
 * @example
 * class UserTable extends BaseTable
 * {
 *     protected function model(): string { return User::class; }
 *     protected function columns(): array { return [...]; }
 * }
 *
 * // In Controller:
 * public function index(UserTable $table)
 * {
 *     return view('users.index', ['table' => $table]);
 * }
 */
abstract class BaseTable
{
    protected ?Builder $query = null;

    protected int $perPage = 15;

    protected ?string $defaultSort = 'created_at';

    protected string $defaultSortDirection = 'desc';

    protected ?string $heading = null;

    protected string $headingIcon = 'group';

    protected ?string $description = null;

    protected ?string $emptyMessage = null;

    /**
     * Define the model class for this table
     */
    abstract protected function model(): string;

    /**
     * Define the columns for this table
     *
     * @return BaseColumn[]
     */
    abstract protected function columns(): array;

    /**
     * Define the filters (optional)
     *
     * @return BaseFilter[]
     */
    protected function filters(): array
    {
        return [];
    }

    /**
     * Define row actions (optional)
     *
     * @return Action[]
     */
    protected function actions(): array
    {
        return [];
    }

    /**
     * Define bulk actions (optional)
     *
     * @return BulkAction[]
     */
    protected function bulkActions(): array
    {
        return [];
    }

    /**
     * Customize the base query (optional)
     */
    protected function query(Builder $query): Builder
    {
        return $query;
    }

    /**
     * Get the table heading
     */
    public function getHeading(): ?string
    {
        return $this->heading;
    }

    /**
     * Set heading dynamically
     */
    public function heading(string $heading): static
    {
        $this->heading = $heading;

        return $this;
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
     * Build and return the Table instance
     */
    public function getTable(): Table
    {
        $modelClass = $this->model();
        $baseQuery = $modelClass::query();
        $query = $this->query($baseQuery);

        $table = Table::make($query)
            ->columns($this->columns())
            ->filters($this->filters())
            ->actions($this->actions())
            ->bulkActions($this->bulkActions())
            ->defaultSort($this->defaultSort, $this->defaultSortDirection)
            ->paginate($this->perPage);

        if ($this->heading) {
            $table->heading($this->heading);
        }

        $table->headingIcon($this->headingIcon);

        if ($this->description) {
            $table->description($this->description);
        }

        if ($this->emptyMessage) {
            $table->emptyMessage($this->emptyMessage);
        }

        return $table;
    }

    /**
     * Get paginated records
     */
    public function getRecords(): LengthAwarePaginator
    {
        return $this->getTable()->getRecords();
    }

    /**
     * Render the table
     */
    public function render()
    {
        return $this->getTable()->render();
    }

    /**
     * Convert to string
     */
    public function __toString(): string
    {
        return $this->render()->render();
    }
}
