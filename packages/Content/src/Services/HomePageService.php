<?php

namespace Packages\Content\Src\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Packages\Content\Src\Enums\HomeSectionPosition;
use Packages\Content\Src\Models\HomeSection;
use Packages\Content\Src\Repositories\Interfaces\HomeSectionRepositoryInterface;

class HomePageService
{
    private const CACHE_TTL_SECONDS = 3600;

    public function __construct(
        private HomeSectionRepositoryInterface $sections,
    ) {}

    /**
     * @return array{sections: Collection<int, HomeSection>}
     */
    public function getRenderData(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return Cache::remember($this->cacheKey($locale), self::CACHE_TTL_SECONDS, function () use ($locale) {
            return [
                'sections' => $this->sections->getOrderedSections($locale),
            ];
        });
    }

    public function invalidateCache(): void
    {
        foreach ($this->locales() as $locale) {
            Cache::forget($this->cacheKey($locale));
        }
    }

    /**
     * Validate items[] cardinality against the position's [min, max] range.
     * Hero accepts 4–10 items; fixed-cardinality positions require exact count.
     *
     * @param  array<int, mixed>|null  $items
     */
    public function validateItems(HomeSectionPosition $position, ?array $items): void
    {
        $range = $position->expectedItemsRange();
        $count = is_array($items) ? count($items) : 0;

        if ($range === null) {
            // Position does not use items[]
            if ($count > 0) {
                throw new InvalidArgumentException(
                    "Position {$position->value} does not accept items[]."
                );
            }

            return;
        }

        [$min, $max] = $range;

        if ($count < $min || $count > $max) {
            $rangeDesc = $min === $max ? "exactly {$min}" : "between {$min} and {$max}";
            throw new InvalidArgumentException(
                "Position {$position->value} requires {$rangeDesc} items, got {$count}."
            );
        }
    }

    private function cacheKey(string $locale): string
    {
        return "content:home:render:{$locale}";
    }

    /**
     * @return array<int, string>
     */
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
