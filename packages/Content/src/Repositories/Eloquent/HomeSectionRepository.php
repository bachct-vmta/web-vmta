<?php

namespace Packages\Content\Src\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Enums\HomeSectionPosition;
use Packages\Content\Src\Models\HomeSection;
use Packages\Content\Src\Repositories\Interfaces\HomeSectionRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class HomeSectionRepository extends BaseRepository implements HomeSectionRepositoryInterface
{
    public function getModel(): string
    {
        return HomeSection::class;
    }

    public function getOrderedSections(string $locale): Collection
    {
        return $this->model
            ->with(['translations' => fn ($q) => $q->where('locale', $locale)])
            ->with('image')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getAllSectionsWithAllTranslations(): Collection
    {
        return $this->model
            ->with('translations')
            ->with('image')
            ->orderBy('sort_order')
            ->get();
    }

    public function findByPosition(HomeSectionPosition $position): ?HomeSection
    {
        return $this->model
            ->with('translations')
            ->where('position', $position->value)
            ->first();
    }

    public function upsertSection(HomeSectionPosition $position, array $baseAttributes, array $perLocalePayload): HomeSection
    {
        return DB::transaction(function () use ($position, $baseAttributes, $perLocalePayload) {
            $section = $this->model->firstOrNew(['position' => $position->value]);
            $section->fill(array_merge([
                'sort_order' => $position->defaultSortOrder(),
                'is_active' => true,
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
