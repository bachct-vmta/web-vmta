<?php

namespace Packages\Content\Src\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Models\MedicalCase;
use Packages\Content\Src\Repositories\Interfaces\MedicalCaseRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class MedicalCaseRepository extends BaseRepository implements MedicalCaseRepositoryInterface
{
    public function getModel(): string
    {
        return MedicalCase::class;
    }

    public function getOrderedCases(string $locale): Collection
    {
        return $this->model
            ->with(['translations' => fn ($q) => $q->where('locale', $locale), 'image'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getAllForAdmin(): Collection
    {
        return $this->model
            ->with(['translations', 'image'])
            ->orderBy('sort_order')
            ->get();
    }

    public function findActiveBySlug(string $slug, string $locale): ?MedicalCase
    {
        // Per-locale slug lookup via translation table; falls back to parent slug
        // for backward compatibility with hardcoded controller references.
        return $this->model
            ->with(['translations', 'image'])
            ->where('is_active', true)
            ->where(function ($q) use ($slug, $locale) {
                $q->whereHas('translations', fn ($t) => $t->where('locale', $locale)->where('slug', $slug))
                  ->orWhere('slug', $slug);
            })
            ->first();
    }

    public function findWithTranslations(int $id): ?MedicalCase
    {
        return $this->model
            ->with(['translations', 'image'])
            ->find($id);
    }

    public function createWithTranslations(array $baseAttributes, array $perLocalePayload): MedicalCase
    {
        return DB::transaction(function () use ($baseAttributes, $perLocalePayload) {
            $case = $this->model->newInstance();
            $case->fill($baseAttributes);
            $case->save();

            foreach ($perLocalePayload as $locale => $payload) {
                if (! is_array($payload)) {
                    continue;
                }
                $case->translateOrNew($locale)->fill($payload)->save();
            }

            return $case->fresh(['translations', 'image']);
        });
    }

    public function updateWithTranslations(MedicalCase $case, array $baseAttributes, array $perLocalePayload): MedicalCase
    {
        return DB::transaction(function () use ($case, $baseAttributes, $perLocalePayload) {
            $case->fill($baseAttributes);
            $case->save();

            foreach ($perLocalePayload as $locale => $payload) {
                if (! is_array($payload)) {
                    continue;
                }
                $case->translateOrNew($locale)->fill($payload)->save();
            }

            return $case->fresh(['translations', 'image']);
        });
    }

    public function updateDetailContent(MedicalCase $case, array $perLocalePayload): MedicalCase
    {
        return DB::transaction(function () use ($case, $perLocalePayload) {
            foreach ($perLocalePayload as $locale => $payload) {
                if (! is_array($payload) || ! array_key_exists('detail_content', $payload)) {
                    continue; // Skip when key missing to avoid wiping existing detail
                }
                $translation = $case->translateOrNew($locale);
                $translation->detail_content = $payload['detail_content'] ?? [];
                $translation->save();
            }

            return $case->fresh(['translations', 'image']);
        });
    }

    public function deleteCase(MedicalCase $case): void
    {
        DB::transaction(fn () => $case->delete());
    }
}
