<?php

namespace Packages\Core\Src\Tables\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Boolean toggle filter
 */
class BooleanFilter extends BaseFilter
{
    protected ?string $trueLabel = 'Có';

    protected ?string $falseLabel = 'Không';

    protected bool $nullable = true;

    /**
     * Set labels for true/false values
     */
    public function labels(string $trueLabel, string $falseLabel): static
    {
        $this->trueLabel = $trueLabel;
        $this->falseLabel = $falseLabel;

        return $this;
    }

    /**
     * Make filter non-nullable (must select true or false)
     */
    public function nullable(bool $nullable = true): static
    {
        $this->nullable = $nullable;

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

        return $query->where($this->getColumn(), (bool) $value);
    }

    /**
     * Render the filter input HTML
     */
    public function render(): string
    {
        $name = $this->name;
        $value = $this->getValue();

        $allSelected = $value === null || $value === '' ? 'selected' : '';
        $trueSelected = $value === '1' || $value === true ? 'selected' : '';
        $falseSelected = $value === '0' || $value === false ? 'selected' : '';

        $options = '';

        if ($this->nullable) {
            $options .= sprintf('<option value="" %s>Tất cả</option>', $allSelected);
        }

        $options .= sprintf('<option value="1" %s>%s</option>', $trueSelected, e($this->trueLabel));
        $options .= sprintf('<option value="0" %s>%s</option>', $falseSelected, e($this->falseLabel));

        return sprintf(
            '<select name="%s" class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">%s</select>',
            e($name),
            $options
        );
    }
}
