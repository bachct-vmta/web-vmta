<?php

namespace Packages\Inquiry\Src\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Packages\Core\Src\Repositories\Eloquent\BaseRepository;
use Packages\Inquiry\Src\Enums\ContactSectionPosition;
use Packages\Inquiry\Src\Models\ContactSection;
use Packages\Inquiry\Src\Repositories\Interfaces\ContactSectionRepositoryInterface;

class ContactSectionRepository extends BaseRepository implements ContactSectionRepositoryInterface
{
    public function getModel(): string
    {
        return ContactSection::class;
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

    public function findByPosition(ContactSectionPosition $position): ?ContactSection
    {
        return $this->model
            ->with('translations')
            ->where('position', $position->value)
            ->first();
    }

    public function upsertSection(ContactSectionPosition $position, array $baseAttributes, array $perLocalePayload): ContactSection
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
