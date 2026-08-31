<?php

namespace Packages\Core\Src\Tables\Columns;

/**
 * Column for displaying numeric values with formatting
 */
class NumericColumn extends BaseColumn
{
    protected int $decimals = 0;

    protected string $decimalSeparator = ',';

    protected string $thousandsSeparator = '.';

    protected ?string $prefix = null;

    protected ?string $suffix = null;

    /**
     * Set decimal precision
     */
    public function decimals(int $decimals): static
    {
        $this->decimals = $decimals;

        return $this;
    }

    /**
     * Set as currency format
     */
    public function money(string $currency = 'đ'): static
    {
        $this->suffix = ' '.$currency;

        return $this;
    }

    /**
     * Add prefix
     */
    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Add suffix
     */
    public function suffix(string $suffix): static
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Format the value
     */
    protected function formatValue($value, $record = null): string
    {
        if ($value === null) {
            return '';
        }

        // If a custom format callback is set, use it instead
        if ($this->formatCallback) {
            $result = call_user_func($this->formatCallback, $value, $record);

            return $this->html ? $result : e($result);
        }

        $formatted = number_format(
            (float) $value,
            $this->decimals,
            $this->decimalSeparator,
            $this->thousandsSeparator
        );

        if ($this->prefix) {
            $formatted = $this->prefix.$formatted;
        }

        if ($this->suffix) {
            $formatted = $formatted.$this->suffix;
        }

        return sprintf(
            '<div class="font-semibold text-slate-700 dark:text-slate-200">%s</div>',
            e($formatted)
        );
    }
}
