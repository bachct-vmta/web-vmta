<?php

namespace Packages\Inquiry\Src\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;
use Packages\Inquiry\Src\Enums\ContactSectionPosition;
use Packages\Inquiry\Src\Models\ContactSection;

interface ContactSectionRepositoryInterface extends RepositoryInterface
{
    public function getOrderedSections(string $locale): Collection;

    public function getAllSectionsWithAllTranslations(): Collection;

    public function findByPosition(ContactSectionPosition $position): ?ContactSection;

    /** @param array<string, array<string, mixed>> $perLocalePayload */
    public function upsertSection(ContactSectionPosition $position, array $baseAttributes, array $perLocalePayload): ContactSection;
}
