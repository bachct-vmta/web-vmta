<?php

namespace Packages\Content\Src\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Packages\Content\Src\Enums\AboutSectionPosition;
use Packages\Content\Src\Repositories\Interfaces\AboutSectionRepositoryInterface;

class AboutPageService
{
    private const CACHE_TTL_SECONDS = 3600;

    public function __construct(
        private AboutSectionRepositoryInterface $sections,
    ) {}

    /** @return array{sections: Collection} */
    public function getRenderData(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return Cache::remember($this->cacheKey($locale), self::CACHE_TTL_SECONDS, function () use ($locale) {
            return ['sections' => $this->sections->getOrderedSections($locale)];
        });
    }

    public function invalidateCache(): void
    {
        foreach ($this->locales() as $locale) {
            Cache::forget($this->cacheKey($locale));
        }
    }

    public function validateItems(AboutSectionPosition $position, ?array $items): void
    {
        $range = $position->expectedItemsRange();
        $count = is_array($items) ? count($items) : 0;

        if ($range === null) {
            if ($count > 0) {
                throw new InvalidArgumentException("Position {$position->value} does not accept items[].");
            }
            return;
        }

        [$min, $max] = $range;

        if ($count < $min || $count > $max) {
            $desc = $min === $max ? "exactly {$min}" : "between {$min} and {$max}";
            throw new InvalidArgumentException("Position {$position->value} requires {$desc} items, got {$count}.");
        }
    }

    private function cacheKey(string $locale): string
    {
        return "content:about:render:{$locale}";
    }

    /** @return array<int, string> */
    private function locales(): array
    {
        $configured = config('translatable.locales', ['vi', 'en']);
        $flat = [];
        foreach ($configured as $key => $value) {
            $flat[] = is_array($value) ? (string) $key : (string) $value;
        }
        return array_values(array_unique($flat));
    }
}
