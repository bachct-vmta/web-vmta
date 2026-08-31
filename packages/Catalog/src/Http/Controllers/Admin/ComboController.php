<?php

namespace Packages\Catalog\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Packages\Catalog\Src\Http\Requests\Admin\StoreComboRequest;
use Packages\Catalog\Src\Http\Requests\Admin\UpdateComboRequest;
use Packages\Catalog\Src\Models\Combo;
use Packages\Catalog\Src\Models\Service;
use Packages\Catalog\Src\Models\TourPackage;
use Packages\Catalog\Src\Repositories\Interfaces\ComboRepositoryInterface;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\Columns\AvatarColumn;
use Packages\Core\Src\Tables\Columns\BadgeColumn;
use Packages\Core\Src\Tables\Columns\BooleanColumn;
use Packages\Core\Src\Tables\Columns\DateColumn;
use Packages\Core\Src\Tables\Filters\SelectFilter;
use Packages\Core\Src\Tables\Table;

class ComboController extends BaseController
{
    public function __construct(private readonly ComboRepositoryInterface $repository) {}

    public function index(): View
    {
        $table = Table::make(Combo::with(['translations']))
            ->heading(__('catalog::catalog.combo.index'))
            ->searchable(true, ['translations.title', 'translations.slug'])
            ->columns([
                AvatarColumn::make('title')
                    ->label(__('catalog::catalog.item_fields.title'))
                    ->secondary('slug'),
                BadgeColumn::make('status')
                    ->label(__('catalog::catalog.item_fields.status'))
                    ->colors(['draft' => 'gray', 'published' => 'green'])
                    ->formatStateUsing(fn ($v) => $v ? __('catalog::catalog.status.'.$v) : ''),
                BooleanColumn::make('is_featured')
                    ->label(__('catalog::catalog.item_fields.is_featured'))
                    ->labels('★', '—'),
                DateColumn::make('published_at')
                    ->label(__('catalog::catalog.item_fields.published_at'))
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('catalog::catalog.item_fields.status'))
                    ->options([
                        'draft' => __('catalog::catalog.status.draft'),
                        'published' => __('catalog::catalog.status.published'),
                    ])
                    ->placeholder(__('catalog::catalog.item_fields.filter_all_statuses')),
            ])
            ->actions([
                Action::make('edit')
                    ->label(__('catalog::catalog.actions.edit'))
                    ->iconEdit()
                    ->route(admin_route_name('combos.edit'))
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
            ->defaultSort('created_at', 'desc')
            ->paginate(15);

        return view('catalog::admin.combos.index', compact('table'));
    }

    public function create(): View
    {
        return view('catalog::admin.combos.create', [
            'combo' => new Combo(['status' => 'draft', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0]),
            'services' => Service::with('translations')->orderBy('sort_order')->get(),
            'tours' => TourPackage::with('translations')->orderBy('sort_order')->get(),
            'selectedServiceIds' => [],
            'selectedTourIds' => [],
            'locales' => array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []])),
        ]);
    }

    public function store(StoreComboRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $combo = $this->repository->create($this->scalarPayload($data));
            $this->syncTranslations($combo, $data['translations']);
            $combo->services()->sync($data['service_ids'] ?? []);
            $combo->tourPackages()->sync($data['tour_package_ids'] ?? []);
        });

        return $this->redirectWithSuccess(admin_route_name('combos.index'), __('catalog::catalog.combo.created'));
    }

    public function edit(int $combo): View
    {
        $model = Combo::with(['translations', 'services', 'tourPackages'])->findOrFail($combo);

        return view('catalog::admin.combos.edit', [
            'combo' => $model,
            'services' => Service::with('translations')->orderBy('sort_order')->get(),
            'tours' => TourPackage::with('translations')->orderBy('sort_order')->get(),
            'selectedServiceIds' => $model->services->pluck('id')->all(),
            'selectedTourIds' => $model->tourPackages->pluck('id')->all(),
            'locales' => array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []])),
        ]);
    }

    public function update(UpdateComboRequest $request, int $combo): RedirectResponse
    {
        $data = $request->validated();
        $model = Combo::findOrFail($combo);

        DB::transaction(function () use ($model, $data) {
            $model->update($this->scalarPayload($data));
            $this->syncTranslations($model, $data['translations']);
            $model->services()->sync($data['service_ids'] ?? []);
            $model->tourPackages()->sync($data['tour_package_ids'] ?? []);
        });

        // Force Scout re-index — bundled service/tour titles change the search payload.
        $model->fresh()->searchable();

        return $this->redirectWithSuccess(admin_route_name('combos.index'), __('catalog::catalog.combo.updated'));
    }

    public function destroy(int $combo): RedirectResponse
    {
        abort_unless(request()->user()?->hasPermission('catalog.delete'), 403);

        $this->repository->delete($combo);

        return $this->redirectWithSuccess(admin_route_name('combos.index'), __('catalog::catalog.combo.deleted'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('catalog.delete'), 403);

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:combos,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $id) {
                $this->repository->delete((int) $id);
            }
        });

        $count = count($validated['ids']);

        return $this->redirectWithSuccess(
            admin_route_name('combos.index'),
            __('catalog::catalog.combo.bulk_deleted', ['count' => $count])
        );
    }

    private function scalarPayload(array $data): array
    {
        $gallery = $data['gallery_media_ids'] ?? null;

        return [
            'cover_media_id' => $data['cover_media_id'] ?? null,
            'gallery_media_ids' => is_array($gallery) && $gallery !== [] ? array_values(array_map('intval', $gallery)) : null,
            'price_from' => $data['price_from'] ?? null,
            'discount_percent' => $data['discount_percent'] ?? null,
            'currency' => strtoupper(($data['currency'] ?? '') ?: 'VND'),
            'cta_app_url' => $data['cta_app_url'] ?? null,
            'status' => $data['status'],
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'published_at' => $data['published_at'] ?? null,
        ];
    }

    private function syncTranslations(Combo $combo, array $translations): void
    {
        foreach ($translations as $row) {
            $combo->translateOrNew($row['locale'])->fill([
                'title' => $row['title'],
                'slug' => $row['slug'],
                'excerpt' => $row['excerpt'] ?? null,
                'body' => $row['body'] ?? null,
                'seo_title' => $row['seo_title'] ?? null,
                'seo_description' => $row['seo_description'] ?? null,
                'seo_og_image' => $row['seo_og_image'] ?? null,
            ])->save();
        }
    }
}
