<?php

namespace Packages\Content\Src\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Packages\Content\Src\Enums\HomeSectionPosition;
use Packages\Content\Src\Models\HomeSection;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface HomeSectionRepositoryInterface extends RepositoryInterface
{
    public function getOrderedSections(string $locale): Collection;

    public function getAllSectionsWithAllTranslations(): Collection;

    public function findByPosition(HomeSectionPosition $position): ?HomeSection;

    /**
     * @param  array<string, array<string, mixed>>  $perLocalePayload  Keyed by locale.
     */
    public function upsertSection(HomeSectionPosition $position, array $baseAttributes, array $perLocalePayload): HomeSection;
}
