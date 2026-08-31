<?php

namespace Packages\Dental\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\Columns\AvatarColumn;
use Packages\Core\Src\Tables\Columns\BadgeColumn;
use Packages\Core\Src\Tables\Columns\NumericColumn;
use Packages\Core\Src\Tables\Filters\SelectFilter;
use Packages\Core\Src\Tables\Table;
use Packages\Dental\Src\Enums\PublishStatus;
use Packages\Dental\Src\Http\Requests\Admin\StoreDentalFacilityRequest;
use Packages\Dental\Src\Http\Requests\Admin\UpdateDentalFacilityRequest;
use Packages\Dental\Src\Models\DentalCategory;
use Packages\Dental\Src\Models\DentalFacility;
use Packages\Dental\Src\Repositories\Interfaces\DentalFacilityRepositoryInterface;

class DentalFacilityController extends BaseController
{
    public function __construct(private readonly DentalFacilityRepositoryInterface $repository) {}

    public function index(): View
    {
        $table = Table::make(DentalFacility::with(['translations', 'category.translations']))
            ->heading(__('dental::dental.facility.index'))
            ->searchable(true, ['translations.name', 'translations.slug'])
            ->columns([
                AvatarColumn::make('name')
                    ->label(__('dental::dental.fields.name'))
                    ->secondary('slug'),
                BadgeColumn::make('status')
                    ->label(__('dental::dental.fields.status'))
                    ->colors(['draft' => 'gray', 'published' => 'green'])
                    ->formatStateUsing(fn ($v) => $v ? __('dental::dental.status.'.$v) : ''),
                NumericColumn::make('sort_order')
                    ->label(__('dental::dental.fields.sort_order'))
                    ->alignRight()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('dental_category_id')
                    ->label(__('dental::dental.fields.category'))
                    ->options($this->categoryOptions())
                    ->placeholder('—'),
                SelectFilter::make('status')
                    ->label(__('dental::dental.fields.status'))
                    ->options(PublishStatus::options())
                    ->placeholder('—'),
            ])
            ->actions([
                Action::make('edit')
                    ->label(__('dental::dental.actions.edit'))
                    ->iconEdit()
                    ->route(admin_route_name('dental_facilities.edit'))
                    ->permission('dental.edit'),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->label(__('dental::dental.actions.delete'))
                    ->icon('delete_sweep')
                    ->danger()
                    ->confirm(__('dental::dental.actions.delete_confirm'))
                    ->permission('dental.delete'),
            ])
            ->defaultSort('sort_order', 'asc')
            ->paginate(20);

        return view('dental::admin.facilities.index', compact('table'));
    }

    public function create(): View
    {
        return view('dental::admin.facilities.create', [
            'facility' => new DentalFacility(['status' => PublishStatus::Draft->value]),
            'categories' => $this->categoryOptions(),
            'locales' => $this->locales(),
        ]);
    }

    public function store(StoreDentalFacilityRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $facility = $this->repository->create($this->attributes($data));

            $this->syncTranslations($facility, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('dental_facilities.index'), __('dental::dental.facility.created'));
    }

    public function edit(int $facility): View
    {
        return view('dental::admin.facilities.edit', [
            'facility' => DentalFacility::with(['translations', 'coverMedia'])->findOrFail($facility),
            'categories' => $this->categoryOptions(),
            'locales' => $this->locales(),
        ]);
    }

    public function update(UpdateDentalFacilityRequest $request, int $facility): RedirectResponse
    {
        $data = $request->validated();
        $model = DentalFacility::findOrFail($facility);

        DB::transaction(function () use ($model, $data) {
            $model->update($this->attributes($data, $model));

            $this->syncTranslations($model, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('dental_facilities.index'), __('dental::dental.facility.updated'));
    }

    public function destroy(int $facility): RedirectResponse
    {
        abort_unless(request()->user()?->hasPermission('dental.delete'), 403);

        $this->repository->delete($facility);

        return $this->redirectWithSuccess(admin_route_name('dental_facilities.index'), __('dental::dental.facility.deleted'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('dental.delete'), 403);

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:dental_facilities,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $id) {
                $this->repository->delete((int) $id);
            }
        });

        return $this->redirectWithSuccess(
            admin_route_name('dental_facilities.index'),
            __('dental::dental.facility.bulk_deleted', ['count' => count($validated['ids'])])
        );
    }

    private function attributes(array $data, ?DentalFacility $existing = null): array
    {
        $publishedAt = $data['published_at'] ?? $existing?->published_at;

        // Chuyển sang published mà chưa có mốc thời gian thì lấy hiện tại, để cơ sở không bị ẩn
        if ($data['status'] === PublishStatus::Published->value && $publishedAt === null) {
            $publishedAt = now();
        }

        return [
            'dental_category_id' => $data['dental_category_id'],
            'status' => $data['status'],
            'published_at' => $publishedAt,
            'is_operating' => (bool) ($data['is_operating'] ?? false),
            'cover_media_id' => $data['cover_media_id'] ?? null,
            'certificates_media_ids' => $data['certificates_media_ids'] ?? null,
            'sort_order' => $data['sort_order'] ?? $existing?->sort_order ?? 0,
        ];
    }

    private function syncTranslations(DentalFacility $facility, array $translations): void
    {
        foreach ($translations as $row) {
            $facility->translateOrNew($row['locale'])->fill([
                'name' => $row['name'],
                'slug' => $row['slug'],
                'address' => $row['address'] ?? null,
            ])->save();
        }
    }

    /**
     * @return array<int, string>
     */
    private function categoryOptions(): array
    {
        return DentalCategory::with('translations')
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (DentalCategory $c) => [$c->id => $c->translate(app()->getLocale())?->name ?? $c->translations->first()?->name ?? '#'.$c->id])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function locales(): array
    {
        return array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
    }
}
