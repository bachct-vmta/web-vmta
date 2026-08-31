<?php

namespace Packages\Core\Src\Tables\Columns;

/**
 * Column for displaying text values
 */
class TextColumn extends BaseColumn
{
    protected ?int $limit = null;

    protected string $suffix = '...';

    protected bool $copyable = false;

    protected ?string $prefix = null;

    protected ?string $suffixText = null;

    protected ?string $default = null;

    protected bool $html = false;

    /**
     * Limit the text length
     */
    public function limit(int $length, string $suffix = '...'): static
    {
        $this->limit = $length;
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Add prefix text
     */
    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Add suffix text
     */
    public function suffix(string $suffix): static
    {
        $this->suffixText = $suffix;

        return $this;
    }

    /**
     * Set default value when null
     */
    public function default(string $default): static
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Render as HTML (be careful with XSS!)
     */
    public function html(bool $html = true): static
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Make the text copyable
     */
    public function copyable(bool $copyable = true): static
    {
        $this->copyable = $copyable;

        return $this;
    }

    /**
     * Format the value
     */
    protected function formatValue($value, $record = null): string
    {
        if ($this->formatCallback) {
            $value = call_user_func($this->formatCallback, $value, $record);
        }

        if ($value === null || $value === '') {
            $value = $this->default ?? '';
        }

        $text = (string) $value;

        // Limit text
        if ($this->limit && mb_strlen($text) > $this->limit) {
            $text = mb_substr($text, 0, $this->limit).$this->suffix;
        }

        // Add prefix/suffix
        if ($this->prefix) {
            $text = $this->prefix.$text;
        }
        if ($this->suffixText) {
            $text = $text.$this->suffixText;
        }

        // Escape HTML unless explicitly allowed
        if (! $this->html) {
            $text = e($text);
        }

        return $text;
    }
}
