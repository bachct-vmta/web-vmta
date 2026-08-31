<?php

namespace Packages\Catalog\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Packages\Catalog\Src\Http\Requests\Admin\StoreSpecialtyRequest;
use Packages\Catalog\Src\Http\Requests\Admin\UpdateSpecialtyRequest;
use Packages\Catalog\Src\Models\Specialty;
use Packages\Catalog\Src\Repositories\Interfaces\SpecialtyRepositoryInterface;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Models\MediaFile;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\Columns\AvatarColumn;
use Packages\Core\Src\Tables\Columns\BooleanColumn;
use Packages\Core\Src\Tables\Columns\NumericColumn;
use Packages\Core\Src\Tables\Filters\SelectFilter;
use Packages\Core\Src\Tables\Table;

class SpecialtyController extends BaseController
{
    private const ALLOWED_HTML_TAGS = '<p><br><strong><em><ul><ol><li><h3><h4><a><blockquote>';

    private const TRANSLATABLE_FIELDS = [
        'name',
        'slug',
        'description',
        'hero_h1',
        'breadcrumb_label',
        'intro_h2',
        'intro_lead',
        'intro_body_html',
        'strengths_h2_line1',
        'strengths_h2_line2',
        'strengths_json',
        'hospitals_h2_line1',
        'hospitals_h2_line2',
        'hospitals_subtitle',
        'hospitals_json',
        'lead_h2_line1',
        'lead_h2_line2',
        'lead_subtitle',
        'lead_body_html',
        'lead_demand_placeholder',
        'lead_submit_label',
        'seo_title',
        'seo_description',
        'seo_og_image',
    ];

    public function __construct(private readonly SpecialtyRepositoryInterface $repository) {}

    public function index(): View
    {
        $table = Table::make(Specialty::with('translations'))
            ->heading(__('catalog::catalog.specialty.index'))
            ->searchable(true, ['translations.name', 'translations.slug'])
            ->columns([
                AvatarColumn::make('name')
                    ->label(__('catalog::catalog.fields.name'))
                    ->secondary('slug'),
                BooleanColumn::make('is_active')
                    ->label(__('catalog::catalog.fields.is_active')),
                BooleanColumn::make('show_lead_form')
                    ->label(__('catalog::catalog.specialty.fields.show_lead_form')),
                NumericColumn::make('sort_order')
                    ->label(__('catalog::catalog.fields.sort_order'))
                    ->alignRight()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label(__('catalog::catalog.fields.is_active'))
                    ->options(['1' => __('catalog::catalog.fields.is_active'), '0' => '—'])
                    ->placeholder('—'),
            ])
            ->actions([
                Action::make('edit')
                    ->label(__('catalog::catalog.actions.edit'))
                    ->iconEdit()
                    ->route(admin_route_name('specialties.edit'))
                    ->permission('catalog.edit'),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->label(__('catalog::catalog.actions.delete'))
                    ->icon('delete_sweep')
                    ->danger()
                    ->confirm(__('catalog::catalog.actions.delete_confirm'))
                    ->permission('catalog.delete'),
            ])
            ->defaultSort('sort_order', 'asc')
            ->paginate(20);

        return view('catalog::admin.specialties.index', compact('table'));
    }

    public function create(): View
    {
        return view('catalog::admin.specialties.create', [
            'specialty' => new Specialty(['is_active' => true, 'show_lead_form' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(StoreSpecialtyRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $specialty = $this->repository->create($this->extractSpecialtyAttributes($data));
            $this->syncTranslations($specialty, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('specialties.index'), __('catalog::catalog.specialty.created'));
    }

    public function edit(int $specialty): View
    {
        $model = Specialty::with(['translations', 'introImage'])->findOrFail($specialty);

        $this->hydrateRepeaterImageUrls($model);

        return view('catalog::admin.specialties.edit', [
            'specialty' => $model,
        ]);
    }

    /**
     * Attach human-friendly `image_url` to each strengths/hospitals JSON row so
     * the admin Alpine repeater can preview existing thumbnails on edit. The
     * URL is stripped server-side before validation; only `image_media_id`
     * round-trips to storage.
     */
    private function hydrateRepeaterImageUrls(Specialty $specialty): void
    {
        $ids = collect();
        foreach ($specialty->translations as $tr) {
            foreach (['strengths_json', 'hospitals_json'] as $col) {
                $value = $tr->{$col};
                if (! is_array($value)) continue;
                foreach ($value as $row) {
                    if (! empty($row['image_media_id'])) {
                        $ids->push((int) $row['image_media_id']);
                    }
                }
            }
        }

        if ($ids->isEmpty()) return;

        $mediaMap = MediaFile::whereIn('id', $ids->unique()->values())->get()->keyBy('id');

        foreach ($specialty->translations as $tr) {
            foreach (['strengths_json', 'hospitals_json'] as $col) {
                $value = $tr->{$col};
                if (! is_array($value)) continue;
                $tr->{$col} = array_map(function ($row) use ($mediaMap) {
                    if (is_array($row) && ! empty($row['image_media_id'])) {
                        $media = $mediaMap->get((int) $row['image_media_id']);
                        if ($media && $media->permalink) {
                            $row['image_url'] = preg_match('#^https?://#', $media->permalink)
                                ? $media->permalink
                                : asset('storage/'.ltrim($media->permalink, '/'));
                        }
                    }
                    return $row;
                }, $value);
            }
        }
    }

    public function update(UpdateSpecialtyRequest $request, int $specialty): RedirectResponse
    {
        $data = $request->validated();
        $model = Specialty::findOrFail($specialty);

        DB::transaction(function () use ($model, $data) {
            $model->update($this->extractSpecialtyAttributes($data));
            $this->syncTranslations($model, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('specialties.index'), __('catalog::catalog.specialty.updated'));
    }

    public function destroy(int $specialty): RedirectResponse
    {
        abort_unless(request()->user()?->hasPermission('catalog.delete'), 403);

        $this->repository->delete($specialty);

        return $this->redirectWithSuccess(admin_route_name('specialties.index'), __('catalog::catalog.specialty.deleted'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('catalog.delete'), 403);

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:specialties,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $id) {
                $this->repository->delete((int) $id);
            }
        });

        $count = count($validated['ids']);

        return $this->redirectWithSuccess(
            admin_route_name('specialties.index'),
            __('catalog::catalog.specialty.bulk_deleted', ['count' => $count])
        );
    }

    private function extractSpecialtyAttributes(array $data): array
    {
        return [
            'icon' => $data['icon'] ?? null,
            'cover_media_id' => $data['cover_media_id'] ?? null,
            'hero_media_id' => $data['hero_media_id'] ?? null,
            'intro_image_media_id' => $data['intro_image_media_id'] ?? null,
            'show_lead_form' => (bool) ($data['show_lead_form'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];
    }

    private function syncTranslations(Specialty $specialty, array $translations): void
    {
        foreach ($translations as $row) {
            $payload = [];
            foreach (self::TRANSLATABLE_FIELDS as $field) {
                if (! array_key_exists($field, $row)) continue;
                $payload[$field] = $this->sanitiseField($field, $row[$field]);
            }

            $specialty->translateOrNew($row['locale'])->fill($payload)->save();
        }
    }

    private function sanitiseField(string $field, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (in_array($field, ['intro_body_html', 'lead_body_html'], true) && is_string($value)) {
            return trim(strip_tags($value, self::ALLOWED_HTML_TAGS)) ?: null;
        }

        if (in_array($field, ['strengths_json', 'hospitals_json'], true)) {
            return is_array($value) ? array_values($value) : [];
        }

        return $value;
    }
}
