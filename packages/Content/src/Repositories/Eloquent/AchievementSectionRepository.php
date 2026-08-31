<?php

namespace Packages\Content\Src\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Enums\AchievementSectionPosition;
use Packages\Content\Src\Models\AchievementSection;
use Packages\Content\Src\Repositories\Interfaces\AchievementSectionRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class AchievementSectionRepository extends BaseRepository implements AchievementSectionRepositoryInterface
{
    public function getModel(): string
    {
        return AchievementSection::class;
    }

    public function getOrderedSections(string $locale): Collection
    {
        return $this->model
            ->with(['translations' => fn ($q) => $q->where('locale', $locale), 'image'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getAllSectionsWithAllTranslations(): Collection
    {
        return $this->model
            ->with(['translations', 'image'])
            ->orderBy('sort_order')
            ->get();
    }

    public function findByPosition(AchievementSectionPosition $position): ?AchievementSection
    {
        return $this->model
            ->with(['translations', 'image'])
            ->where('position', $position->value)
            ->first();
    }

    public function upsertSection(AchievementSectionPosition $position, array $baseAttributes, array $perLocalePayload): AchievementSection
    {
        return DB::transaction(function () use ($position, $baseAttributes, $perLocalePayload) {
            $section = $this->model->firstOrNew(['position' => $position->value]);
            $section->fill(array_merge([
                'sort_order' => $position->defaultSortOrder(),
                'is_active'  => true,
            ], $baseAttributes));
            $section->position = $position;
            $section->save();

            foreach ($perLocalePayload as $locale => $payload) {
                if (! is_array($payload)) {
                    continue;
                }
                $section->translateOrNew($locale)->fill($payload)->save();
            }

            return $section->fresh(['translations', 'image']);
        });
    }
}
