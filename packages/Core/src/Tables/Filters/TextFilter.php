<?php

namespace Packages\Core\Src\Tables\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Text search filter
 */
class TextFilter extends BaseFilter
{
    protected ?string $placeholder = null;

    protected array $searchColumns = [];

    protected string $operator = 'like';

    /**
     * Set placeholder
     */
    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Search across multiple columns
     */
    public function searchColumns(array $columns): static
    {
        $this->searchColumns = $columns;

        return $this;
    }

    /**
     * Use exact match instead of LIKE
     */
    public function exact(): static
    {
        $this->operator = '=';

        return $this;
    }

    /**
     * Apply the filter to a query
     */
    public function apply(Builder $query, $value): Builder
    {
        if ($value === null || $value === '') {
            return $query;
        }

        $columns = ! empty($this->searchColumns)
            ? $this->searchColumns
            : [$this->getColumn()];

        $searchValue = $this->operator === 'like' ? "%{$value}%" : $value;

        return $query->where(function ($q) use ($columns, $searchValue) {
            foreach ($columns as $index => $column) {
                if ($index === 0) {
                    $q->where($column, $this->operator, $searchValue);
                } else {
                    $q->orWhere($column, $this->operator, $searchValue);
                }
            }
        });
    }

    /**
     * Render the filter input HTML
     */
    public function render(): string
    {
        $name = $this->name;
        $value = $this->getValue() ?? '';
        $placeholder = $this->placeholder ?? 'Tìm kiếm...';

        return sprintf(
            '<input type="text" name="%s" value="%s" placeholder="%s" class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">',
            e($name),
            e($value),
            e($placeholder)
        );
    }
}
