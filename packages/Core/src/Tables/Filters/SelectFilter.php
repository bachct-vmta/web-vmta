<?php

namespace Packages\Core\Src\Tables\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Dropdown select filter
 */
class SelectFilter extends BaseFilter
{
    protected array|Collection $options = [];

    protected ?string $placeholder = null;

    protected bool $multiple = false;

    protected bool $searchable = false;

    /**
     * Set the filter options
     *
     * @param  array|Collection  $options  ['value' => 'label', ...]
     */
    public function options(array|Collection $options): static
    {
        $this->options = $options instanceof Collection ? $options->toArray() : $options;

        return $this;
    }

    /**
     * Get the options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set placeholder
     */
    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Get placeholder
     */
    public function getPlaceholder(): string
    {
        return $this->placeholder ?? 'Tất cả';
    }

    /**
     * Allow multiple selections
     */
    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

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

        if ($this->multiple && is_array($value)) {
            return $query->whereIn($this->getColumn(), $value);
        }

        return $query->where($this->getColumn(), $value);
    }

    /**
     * Render the filter input HTML
     */
    public function render(): string
    {
        $name = $this->name;
        $value = $this->getValue();
        $options = '';

        // Placeholder option
        $options .= sprintf(
            '<option value="">%s</option>',
            e($this->getPlaceholder())
        );

        foreach ($this->options as $optionValue => $optionLabel) {
            $selected = (string) $optionValue === (string) $value ? 'selected' : '';
            $options .= sprintf(
                '<option value="%s" %s>%s</option>',
                e($optionValue),
                $selected,
                e($optionLabel)
            );
        }

        return sprintf(
            '<select name="%s" class="bg-transparent border-none focus:ring-0 text-sm py-1.5 pl-3 pr-8 text-slate-600 dark:text-slate-300 cursor-pointer">%s</select>',
            e($name),
            $options
        );
    }
}
