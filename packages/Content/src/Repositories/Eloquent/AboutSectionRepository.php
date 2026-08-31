<?php

namespace Packages\Content\Src\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Enums\AboutSectionPosition;
use Packages\Content\Src\Models\AboutSection;
use Packages\Content\Src\Repositories\Interfaces\AboutSectionRepositoryInterface;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;

class AboutSectionRepository extends BaseRepository implements AboutSectionRepositoryInterface
{
    public function getModel(): string
    {
        return AboutSection::class;
    }

    public function getOrderedSections(string $locale): Collection
    {
        return $this->model
            ->with(['translations' => fn ($q) => $q->where('locale', $locale)])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getAllSectionsWithAllTranslations(): Collection
    {
        return $this->model
            ->with('translations')
            ->orderBy('sort_order')
            ->get();
    }

    public function findByPosition(AboutSectionPosition $position): ?AboutSection
    {
        return $this->model
            ->with('translations')
            ->where('position', $position->value)
            ->first();
    }

    public function upsertSection(AboutSectionPosition $position, array $baseAttributes, array $perLocalePayload): AboutSection
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

            return $section->fresh(['translations']);
        });
    }
}
