<?php

namespace Packages\Content\Src\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Packages\Content\Src\Models\MedicalCase;
use Packages\Core\Src\Repositories\Interfaces\RepositoryInterface;

interface MedicalCaseRepositoryInterface extends RepositoryInterface
{
    public function getOrderedCases(string $locale): Collection;

    public function getAllForAdmin(): Collection;

    public function findActiveBySlug(string $slug, string $locale): ?MedicalCase;

    public function findWithTranslations(int $id): ?MedicalCase;

    /** @param array<string, array<string, mixed>> $perLocalePayload */
    public function createWithTranslations(array $baseAttributes, array $perLocalePayload): MedicalCase;

    /** @param array<string, array<string, mixed>> $perLocalePayload */
    public function updateWithTranslations(MedicalCase $case, array $baseAttributes, array $perLocalePayload): MedicalCase;

    /** @param array<string, array<string, mixed>> $perLocalePayload */
    public function updateDetailContent(MedicalCase $case, array $perLocalePayload): MedicalCase;

    public function deleteCase(MedicalCase $case): void;
}
