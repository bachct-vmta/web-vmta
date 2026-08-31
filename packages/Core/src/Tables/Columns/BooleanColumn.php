<?php

namespace Packages\Core\Src\Tables\Columns;

/**
 * Column for displaying boolean values as icons
 */
class BooleanColumn extends BaseColumn
{
    protected string $trueIcon = '<span class="material-symbols-rounded text-emerald-500 text-[18px]">check_circle</span>';

    protected string $falseIcon = '<span class="material-symbols-rounded text-slate-400 text-[18px]">cancel</span>';

    protected ?string $trueLabel = null;

    protected ?string $falseLabel = null;

    /**
     * Set custom icons
     */
    public function icons(string $trueIcon, string $falseIcon): static
    {
        $this->trueIcon = $trueIcon;
        $this->falseIcon = $falseIcon;

        return $this;
    }

    /**
     * Use simple dots instead of icons
     */
    public function dots(): static
    {
        $this->trueIcon = '<span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>';
        $this->falseIcon = '<span class="w-2 h-2 rounded-full bg-slate-400 inline-block"></span>';

        return $this;
    }

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
     * Format the value
     */
    protected function formatValue($value, $record = null): string
    {
        $isTrue = (bool) $value;
        $icon = $isTrue ? $this->trueIcon : $this->falseIcon;

        if ($this->trueLabel || $this->falseLabel) {
            $label = $isTrue ? $this->trueLabel : $this->falseLabel;
            $labelClass = $isTrue
                ? 'text-slate-600 dark:text-slate-400'
                : 'text-slate-500 dark:text-slate-500 opacity-60';

            return sprintf(
                '<span class="inline-flex items-center gap-2 text-sm %s">%s<span>%s</span></span>',
                $labelClass,
                $icon,
                e($label)
            );
        }

        return $icon;
    }
}
