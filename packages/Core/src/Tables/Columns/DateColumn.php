<?php

namespace Packages\Core\Src\Tables\Columns;

use Carbon\Carbon;

/**
 * Column for displaying date/time values
 */
class DateColumn extends BaseColumn
{
    protected string $format = 'd/m/Y';

    protected ?string $timezone = null;

    protected bool $since = false;

    protected bool $diffForHumans = false;

    protected ?string $tooltip = null;

    /**
     * Set the date format
     */
    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Common format presets
     */
    public function date(): static
    {
        $this->format = 'd/m/Y';

        return $this;
    }

    public function dateTime(): static
    {
        $this->format = 'd/m/Y H:i';

        return $this;
    }

    public function time(): static
    {
        $this->format = 'H:i';

        return $this;
    }

    /**
     * Display as relative time (e.g., "2 hours ago")
     */
    public function since(bool $since = true): static
    {
        $this->diffForHumans = $since;
        $this->tooltip = 'd/m/Y H:i';

        return $this;
    }

    /**
     * Alias for since()
     */
    public function diffForHumans(bool $diff = true): static
    {
        return $this->since($diff);
    }

    /**
     * Set timezone
     */
    public function timezone(string $timezone): static
    {
        $this->timezone = $timezone;

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

        try {
            $date = $value instanceof Carbon
                ? $value
                : Carbon::parse($value);

            if ($this->timezone) {
                $date = $date->timezone($this->timezone);
            }

            if ($this->diffForHumans) {
                $formatted = $date->diffForHumans();

                // Add icon for relative time display
                $icon = '<span class="material-symbols-rounded text-emerald-500 text-[18px]">check_circle</span>';

                if ($this->tooltip) {
                    return sprintf(
                        '<div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400" title="%s">%s<span>%s</span></div>',
                        $date->format($this->tooltip),
                        $icon,
                        e($formatted)
                    );
                }

                return sprintf(
                    '<div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">%s<span>%s</span></div>',
                    $icon,
                    e($formatted)
                );
            }

            return sprintf(
                '<span class="text-sm text-slate-700 dark:text-slate-300">%s</span>',
                e($date->format($this->format))
            );
        } catch (\Exception $e) {
            return (string) $value;
        }
    }
}
