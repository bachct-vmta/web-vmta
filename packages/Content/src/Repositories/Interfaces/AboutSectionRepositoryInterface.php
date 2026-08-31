<?php

namespace Packages\Content\Src\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Packages\Content\Src\Enums\AboutSectionPosition;
use Packages\Content\Src\Models\AboutSection;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface AboutSectionRepositoryInterface extends RepositoryInterface
{
    public function getOrderedSections(string $locale): Collection;

    public function getAllSectionsWithAllTranslations(): Collection;

    public function findByPosition(AboutSectionPosition $position): ?AboutSection;

    /** @param array<string, array<string, mixed>> $perLocalePayload */
    public function upsertSection(AboutSectionPosition $position, array $baseAttributes, array $perLocalePayload): AboutSection;
}
