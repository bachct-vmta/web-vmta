<?php

namespace Packages\Core\Src\Tables\Columns;

/**
 * Column for displaying images
 */
class ImageColumn extends BaseColumn
{
    protected int $width = 40;

    protected int $height = 40;

    protected bool $circular = false;

    protected ?string $defaultUrl = null;

    /**
     * Set image dimensions
     */
    public function size(int $width, ?int $height = null): static
    {
        $this->width = $width;
        $this->height = $height ?? $width;

        return $this;
    }

    /**
     * Make image circular
     */
    public function circular(bool $circular = true): static
    {
        $this->circular = $circular;

        return $this;
    }

    /**
     * Set default image URL
     */
    public function defaultImageUrl(string $url): static
    {
        $this->defaultUrl = $url;

        return $this;
    }

    /**
     * Format the value
     */
    protected function formatValue($value, $record = null): string
    {
        $url = $value ?: $this->defaultUrl;

        if (! $url) {
            return '';
        }

        $roundedClass = $this->circular ? 'rounded-full' : 'rounded';

        return sprintf(
            '<img src="%s" alt="" class="object-cover %s" style="width: %dpx; height: %dpx;">',
            e($url),
            $roundedClass,
            $this->width,
            $this->height
        );
    }
}
