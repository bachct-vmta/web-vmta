<?php

namespace Packages\Content\Src\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Packages\Content\Src\Enums\AchievementSectionPosition;
use Packages\Content\Src\Models\AchievementSection;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface AchievementSectionRepositoryInterface extends RepositoryInterface
{
    public function getOrderedSections(string $locale): Collection;

    public function getAllSectionsWithAllTranslations(): Collection;

    public function findByPosition(AchievementSectionPosition $position): ?AchievementSection;

    /** @param array<string, array<string, mixed>> $perLocalePayload */
    public function upsertSection(AchievementSectionPosition $position, array $baseAttributes, array $perLocalePayload): AchievementSection;
}
