<?php

namespace Packages\Catalog\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Packages\Catalog\Src\Http\Requests\Admin\StorePartnerRequest;
use Packages\Catalog\Src\Http\Requests\Admin\UpdatePartnerRequest;
use Packages\Catalog\Src\Models\Partner;
use Packages\Catalog\Src\Repositories\Interfaces\PartnerRepositoryInterface;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\Columns\AvatarColumn;
use Packages\Core\Src\Tables\Columns\BadgeColumn;
use Packages\Core\Src\Tables\Columns\BooleanColumn;
use Packages\Core\Src\Tables\Columns\NumericColumn;
use Packages\Core\Src\Tables\Filters\SelectFilter;
use Packages\Core\Src\Tables\Table;

class PartnerController extends BaseController
{
    public function __construct(private readonly PartnerRepositoryInterface $repository) {}

    public function index(): View
    {
        $table = Table::make(Partner::with(['translations']))
            ->heading(__('catalog::catalog.partner.index'))
            ->searchable(true, ['translations.name', 'translations.slug', 'phone', 'email'])
            ->columns([
                AvatarColumn::make('name')
                    ->label(__('catalog::catalog.fields.name'))
                    ->secondary('slug'),
                BadgeColumn::make('type')
                    ->label(__('catalog::catalog.fields.type'))
                    ->colors([
                        Partner::TYPE_HOSPITAL => 'blue',
                        Partner::TYPE_HOTEL => 'purple',
                        Partner::TYPE_TRAVEL => 'green',
                        Partner::TYPE_INSURANCE => 'yellow',
                    ])
                    ->formatStateUsing(fn ($v) => $v ? __('catalog::catalog.types.'.$v) : ''),
                BooleanColumn::make('is_active')
                    ->label(__('catalog::catalog.fields.is_active')),
                NumericColumn::make('sort_order')
                    ->label(__('catalog::catalog.fields.sort_order'))
                    ->alignRight()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('catalog::catalog.fields.type'))
                    ->options(collect(Partner::TYPES)
                        ->mapWithKeys(fn ($type) => [$type => __('catalog::catalog.types.'.$type)])
                        ->all())
                    ->placeholder(__('catalog::catalog.fields.filter_all_types')),
            ])
            ->actions([
                Action::make('edit')
                    ->label(__('catalog::catalog.actions.edit'))
                    ->iconEdit()
                    ->route(admin_route_name('partners.edit'))
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

        return view('catalog::admin.partners.index', compact('table'));
    }

    public function create(): View
    {
        return view('catalog::admin.partners.create', [
            'partner' => new Partner(['type' => Partner::TYPE_HOSPITAL, 'is_active' => true, 'sort_order' => 0]),
            'tr' => null,
            'partnerTypes' => Partner::TYPES,
        ]);
    }

    public function store(StorePartnerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $partner = $this->repository->create($this->scalarPayload($data));
            $this->syncTranslations($partner, $this->withGeneratedSlug($data['translations'], null));
        });

        return $this->redirectWithSuccess(admin_route_name('partners.index'), __('catalog::catalog.partner.created'));
    }

    public function edit(int $partner): View
    {
        $model = Partner::with(['translations', 'logoMedia'])->findOrFail($partner);

        return view('catalog::admin.partners.edit', [
            'partner' => $model,
            'tr' => $model->translations->firstWhere('locale', 'vi'),
            'partnerTypes' => Partner::TYPES,
        ]);
    }

    public function update(UpdatePartnerRequest $request, int $partner): RedirectResponse
    {
        $data = $request->validated();
        $model = Partner::findOrFail($partner);

        DB::transaction(function () use ($model, $data, $partner) {
            $model->update($this->scalarPayload($data));
            $this->syncTranslations($model, $this->withGeneratedSlug($data['translations'], $partner));
        });

        return $this->redirectWithSuccess(admin_route_name('partners.index'), __('catalog::catalog.partner.updated'));
    }

    public function destroy(int $partner): RedirectResponse
    {
        abort_unless(request()->user()?->hasPermission('catalog.delete'), 403);

        $this->repository->delete($partner);

        return $this->redirectWithSuccess(admin_route_name('partners.index'), __('catalog::catalog.partner.deleted'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('catalog.delete'), 403);

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:partners,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $id) {
                $this->repository->delete((int) $id);
            }
        });

        $count = count($validated['ids']);

        return $this->redirectWithSuccess(
            admin_route_name('partners.index'),
            __('catalog::catalog.partner.bulk_deleted', ['count' => $count])
        );
    }

    private function scalarPayload(array $data): array
    {
        return [
            'type' => $data['type'],
            'logo_media_id' => $data['logo_media_id'] ?? null,
            'website' => $data['website'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'sort_order' => 0,
        ];
    }

    /**
     * Inject auto-generated unique slug into each translation row.
     * Form no longer asks for slug — derived from name with `-{n}` suffix on collision.
     */
    private function withGeneratedSlug(array $translations, ?int $excludePartnerId): array
    {
        foreach ($translations as $idx => $row) {
            $translations[$idx]['slug'] = $this->generateUniqueSlug(
                (string) ($row['name'] ?? ''),
                (string) ($row['locale'] ?? 'vi'),
                $excludePartnerId,
            );
        }

        return $translations;
    }

    private function generateUniqueSlug(string $name, string $locale, ?int $excludePartnerId): string
    {
        $base = Str::slug($name) ?: 'partner';
        $candidate = $base;
        $suffix = 2;

        while ($this->slugExists($candidate, $locale, $excludePartnerId)) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function slugExists(string $slug, string $locale, ?int $excludePartnerId): bool
    {
        $query = DB::table('partner_translations')
            ->where('locale', $locale)
            ->where('slug', $slug);

        if ($excludePartnerId !== null) {
            $query->where('partner_id', '!=', $excludePartnerId);
        }

        return $query->exists();
    }

    private function syncTranslations(Partner $partner, array $translations): void
    {
        foreach ($translations as $row) {
            $fill = [
                'name' => $row['name'],
                'slug' => $row['slug'],
                'description' => $row['description'] ?? null,
                'address' => $row['address'] ?? null,
            ];

            // Preserve existing short_description when the form does not submit it.
            if (array_key_exists('short_description', $row)) {
                $fill['short_description'] = $row['short_description'];
            }

            $partner->translateOrNew($row['locale'])->fill($fill)->save();
        }
    }
}
