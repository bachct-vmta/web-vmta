<?php

namespace Packages\Content\Src\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Packages\Content\Src\Enums\AchievementSectionPosition;
use Packages\Content\Src\Repositories\Interfaces\AchievementSectionRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\MedicalCaseRepositoryInterface;

class AchievementPageService
{
    private const CACHE_TTL_SECONDS = 3600;

    public function __construct(
        private AchievementSectionRepositoryInterface $sections,
        private MedicalCaseRepositoryInterface $cases,
    ) {}

    /** @return array{sections: Collection} */
    public function getRenderData(?string $locale = null): array
    {
        $locale ??= app()->getLocale();

        return Cache::remember($this->sectionsCacheKey($locale), self::CACHE_TTL_SECONDS, function () use ($locale) {
            return ['sections' => $this->sections->getOrderedSections($locale)];
        });
    }

    public function getCasesForRender(?string $locale = null): Collection
    {
        $locale ??= app()->getLocale();

        return Cache::remember($this->casesCacheKey($locale), self::CACHE_TTL_SECONDS, function () use ($locale) {
            return $this->cases->getOrderedCases($locale);
        });
    }

    public function invalidateCache(): void
    {
        foreach ($this->locales() as $locale) {
            Cache::forget($this->sectionsCacheKey($locale));
            Cache::forget($this->casesCacheKey($locale));
        }
    }

    public function validateItems(AchievementSectionPosition $position, ?array $items): void
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

    private function sectionsCacheKey(string $locale): string
    {
        return "content:achievement:render:{$locale}";
    }

    private function casesCacheKey(string $locale): string
    {
        return "content:achievement:cases:{$locale}";
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
