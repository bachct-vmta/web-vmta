<?php

namespace Packages\Catalog\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Packages\Catalog\Src\Http\Requests\Admin\StoreTourPackageRequest;
use Packages\Catalog\Src\Http\Requests\Admin\UpdateTourPackageRequest;
use Packages\Catalog\Src\Models\Partner;
use Packages\Catalog\Src\Models\TourPackage;
use Packages\Catalog\Src\Repositories\Interfaces\TourPackageRepositoryInterface;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\Columns\AvatarColumn;
use Packages\Core\Src\Tables\Columns\BadgeColumn;
use Packages\Core\Src\Tables\Columns\BooleanColumn;
use Packages\Core\Src\Tables\Columns\DateColumn;
use Packages\Core\Src\Tables\Filters\SelectFilter;
use Packages\Core\Src\Tables\Table;

class TourPackageController extends BaseController
{
    public function __construct(private readonly TourPackageRepositoryInterface $repository) {}

    public function index(): View
    {
        $partners = Partner::with('translations')->orderBy('sort_order')->get();
        $partnerOptions = $partners->mapWithKeys(fn ($p) => [
            $p->id => $p->translations->firstWhere('locale', app()->getLocale())?->name
                ?? $p->translations->first()?->name
                ?? '#'.$p->id,
        ])->toArray();

        $table = Table::make(TourPackage::with(['translations', 'partner.translations']))
            ->heading(__('catalog::catalog.tour.index'))
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
                SelectFilter::make('partner_id')
                    ->label(__('catalog::catalog.item_fields.partner'))
                    ->options($partnerOptions)
                    ->placeholder(__('catalog::catalog.item_fields.filter_all_partners')),
            ])
            ->actions([
                Action::make('edit')
                    ->label(__('catalog::catalog.actions.edit'))
                    ->iconEdit()
                    ->route(admin_route_name('tours.edit'))
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

        return view('catalog::admin.tours.index', compact('table'));
    }

    public function create(): View
    {
        return view('catalog::admin.tours.create', [
            'tour' => new TourPackage(['status' => 'draft', 'currency' => 'VND', 'is_featured' => false, 'sort_order' => 0]),
            'partners' => Partner::with('translations')->orderBy('sort_order')->get(),
            'locales' => array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []])),
        ]);
    }

    public function store(StoreTourPackageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $tour = $this->repository->create($this->scalarPayload($data));
            $this->syncTranslations($tour, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('tours.index'), __('catalog::catalog.tour.created'));
    }

    public function edit(int $tour): View
    {
        $model = TourPackage::with(['translations'])->findOrFail($tour);

        return view('catalog::admin.tours.edit', [
            'tour' => $model,
            'partners' => Partner::with('translations')->orderBy('sort_order')->get(),
            'locales' => array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []])),
        ]);
    }

    public function update(UpdateTourPackageRequest $request, int $tour): RedirectResponse
    {
        $data = $request->validated();
        $model = TourPackage::findOrFail($tour);

        DB::transaction(function () use ($model, $data) {
            $model->update($this->scalarPayload($data));
            $this->syncTranslations($model, $data['translations']);
        });

        // Pivot resync isn't observed by Scout — force re-index so labels in the
        // search payload stay fresh.
        $model->fresh()->searchable();

        return $this->redirectWithSuccess(admin_route_name('tours.index'), __('catalog::catalog.tour.updated'));
    }

    public function destroy(int $tour): RedirectResponse
    {
        abort_unless(request()->user()?->hasPermission('catalog.delete'), 403);

        $this->repository->delete($tour);

        return $this->redirectWithSuccess(admin_route_name('tours.index'), __('catalog::catalog.tour.deleted'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('catalog.delete'), 403);

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:tour_packages,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $id) {
                $this->repository->delete((int) $id);
            }
        });

        $count = count($validated['ids']);

        return $this->redirectWithSuccess(
            admin_route_name('tours.index'),
            __('catalog::catalog.tour.bulk_deleted', ['count' => $count])
        );
    }

    private function scalarPayload(array $data): array
    {
        $gallery = $data['gallery_media_ids'] ?? null;

        return [
            'partner_id' => $data['partner_id'] ?? null,
            'cover_media_id' => $data['cover_media_id'] ?? null,
            'gallery_media_ids' => is_array($gallery) && $gallery !== [] ? array_values(array_map('intval', $gallery)) : null,
            'duration_days' => $data['duration_days'] ?? null,
            'price_from' => $data['price_from'] ?? null,
            'currency' => strtoupper(($data['currency'] ?? '') ?: 'VND'),
            'cta_app_url' => $data['cta_app_url'] ?? null,
            'status' => $data['status'],
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'published_at' => $data['published_at'] ?? null,
        ];
    }

    private function syncTranslations(TourPackage $tour, array $translations): void
    {
        foreach ($translations as $row) {
            $tour->translateOrNew($row['locale'])->fill([
                'title' => $row['title'],
                'slug' => $row['slug'],
                'excerpt' => $row['excerpt'] ?? null,
                'body' => $row['body'] ?? null,
                'itinerary' => $row['itinerary'] ?? null,
                'seo_title' => $row['seo_title'] ?? null,
                'seo_description' => $row['seo_description'] ?? null,
                'seo_og_image' => $row['seo_og_image'] ?? null,
            ])->save();
        }
    }
}
