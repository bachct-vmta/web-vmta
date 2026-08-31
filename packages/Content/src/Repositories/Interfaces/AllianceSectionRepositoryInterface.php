<?php

namespace Packages\Content\Src\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Packages\Content\Src\Enums\AllianceSectionPosition;
use Packages\Content\Src\Models\AllianceSection;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface AllianceSectionRepositoryInterface extends RepositoryInterface
{
    public function getOrderedSections(string $locale): Collection;

    public function getAllSectionsWithAllTranslations(): Collection;

    public function findByPosition(AllianceSectionPosition $position): ?AllianceSection;

    /** @param array<string, array<string, mixed>> $perLocalePayload */
    public function upsertSection(AllianceSectionPosition $position, array $baseAttributes, array $perLocalePayload): AllianceSection;
}
