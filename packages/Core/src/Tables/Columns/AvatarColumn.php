<?php

namespace Packages\Core\Src\Tables\Columns;

use Closure;

/**
 * Column for displaying user avatars with name/email
 *
 * Perfect for user listings with circular/rounded avatars
 * and optional secondary text (like email)
 */
class AvatarColumn extends BaseColumn
{
    protected ?string $secondaryField = null;

    protected ?Closure $avatarUrl = null;

    protected ?Closure $colorCallback = null;

    protected string $size = 'md';

    protected string $shape = 'rounded-2xl';

    protected array $colorPalette = [
        'blue', 'amber', 'emerald', 'indigo', 'purple', 'pink', 'rose', 'cyan', 'teal',
    ];

    /**
     * Set the secondary text field (e.g., 'email')
     */
    public function secondary(string $field): static
    {
        $this->secondaryField = $field;

        return $this;
    }

    /**
     * Set avatar URL callback
     */
    public function avatar(Closure $callback): static
    {
        $this->avatarUrl = $callback;

        return $this;
    }

    /**
     * Set color callback based on record
     */
    public function color(Closure $callback): static
    {
        $this->colorCallback = $callback;

        return $this;
    }

    /**
     * Set avatar size
     */
    public function size(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Use circular shape
     */
    public function circular(): static
    {
        $this->shape = 'rounded-full';

        return $this;
    }

    /**
     * Use rounded square shape (default)
     */
    public function rounded(): static
    {
        $this->shape = 'rounded-2xl';

        return $this;
    }

    /**
     * Get size classes
     */
    protected function getSizeClasses(): string
    {
        return match ($this->size) {
            'sm' => 'w-8 h-8 text-xs',
            'lg' => 'w-14 h-14 text-xl',
            'xl' => 'w-16 h-16 text-2xl',
            default => 'w-12 h-12 text-lg', // md
        };
    }

    /**
     * Get initials from name
     */
    protected function getInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            return mb_strtoupper(mb_substr($words[0], 0, 1).mb_substr($words[count($words) - 1], 0, 1));
        }

        return mb_strtoupper(mb_substr($name, 0, 2));
    }

    /**
     * Get color based on string hash
     */
    protected function getColorFromString(string $name): string
    {
        $hash = crc32($name);
        $index = abs($hash) % count($this->colorPalette);

        return $this->colorPalette[$index];
    }

    /**
     * Get gradient classes for a color
     */
    protected function getGradientClasses(string $color): array
    {
        return match ($color) {
            'blue' => ['from-blue-100 to-blue-200 dark:from-blue-900/30 dark:to-blue-800/30', 'text-primary', 'ring-blue-50 dark:ring-blue-900/10'],
            'amber' => ['from-amber-100 to-amber-200 dark:from-amber-900/30 dark:to-amber-800/30', 'text-amber-600', 'ring-amber-50 dark:ring-amber-900/10'],
            'emerald' => ['from-emerald-100 to-emerald-200 dark:from-emerald-900/30 dark:to-emerald-800/30', 'text-emerald-600', 'ring-emerald-50 dark:ring-emerald-900/10'],
            'indigo' => ['from-indigo-100 to-indigo-200 dark:from-indigo-900/30 dark:to-indigo-800/30', 'text-indigo-600', 'ring-indigo-50 dark:ring-indigo-900/10'],
            'purple' => ['from-purple-100 to-purple-200 dark:from-purple-900/30 dark:to-purple-800/30', 'text-purple-600', 'ring-purple-50 dark:ring-purple-900/10'],
            'pink' => ['from-pink-100 to-pink-200 dark:from-pink-900/30 dark:to-pink-800/30', 'text-pink-600', 'ring-pink-50 dark:ring-pink-900/10'],
            'rose' => ['from-rose-100 to-rose-200 dark:from-rose-900/30 dark:to-rose-800/30', 'text-rose-600', 'ring-rose-50 dark:ring-rose-900/10'],
            'cyan' => ['from-cyan-100 to-cyan-200 dark:from-cyan-900/30 dark:to-cyan-800/30', 'text-cyan-600', 'ring-cyan-50 dark:ring-cyan-900/10'],
            'teal' => ['from-teal-100 to-teal-200 dark:from-teal-900/30 dark:to-teal-800/30', 'text-teal-600', 'ring-teal-50 dark:ring-teal-900/10'],
            default => ['from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600', 'text-slate-600', 'ring-slate-50 dark:ring-slate-700'],
        };
    }

    /**
     * Render the column
     */
    public function render($record): string
    {
        $name = $this->getState($record) ?? '';

        if (empty($name)) {
            return '';
        }

        // Get color
        $color = $this->colorCallback
            ? call_user_func($this->colorCallback, $record)
            : $this->getColorFromString($name);

        [$gradientClass, $textClass, $ringClass] = $this->getGradientClasses($color);

        // Get avatar URL or initials
        $avatarUrl = $this->avatarUrl ? call_user_func($this->avatarUrl, $record) : null;
        $initials = $this->getInitials($name);

        // Get secondary text
        $secondary = '';
        if ($this->secondaryField) {
            $segments = explode('.', $this->secondaryField);
            $value = $record;
            foreach ($segments as $segment) {
                $value = is_object($value) ? ($value->{$segment} ?? null) : ($value[$segment] ?? null);
            }
            $secondary = $value;
        }

        $sizeClasses = $this->getSizeClasses();

        // Build avatar HTML
        if ($avatarUrl) {
            $avatarHtml = sprintf(
                '<img src="%s" alt="%s" class="%s %s ring-4 %s flex-shrink-0 object-cover">',
                e($avatarUrl),
                e($name),
                $sizeClasses,
                $this->shape,
                $ringClass
            );
        } else {
            $avatarHtml = sprintf(
                '<div class="%s %s bg-gradient-to-br %s flex items-center justify-center %s font-bold ring-4 %s flex-shrink-0">%s</div>',
                $sizeClasses,
                $this->shape,
                $gradientClass,
                $textClass,
                $ringClass,
                e($initials)
            );
        }

        // Build text HTML — min-w-0 lets the text block shrink inside flex,
        // truncate on each line shows the ellipsis when the column is narrow.
        $textHtml = sprintf(
            '<div class="min-w-0 flex-1">
                <div class="font-bold text-slate-800 dark:text-white group-hover:text-primary transition-colors truncate">%s</div>
                %s
            </div>',
            e($name),
            $secondary ? sprintf('<div class="text-sm text-slate-500 dark:text-slate-400 truncate">%s</div>', e($secondary)) : ''
        );

        return sprintf(
            '<div class="flex items-center gap-4 min-w-0">%s%s</div>',
            $avatarHtml,
            $textHtml
        );
    }
}
