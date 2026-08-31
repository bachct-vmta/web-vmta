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
use Packages\Dental\Src\Http\Requests\Admin\StoreDentalServiceRequest;
use Packages\Dental\Src\Http\Requests\Admin\UpdateDentalServiceRequest;
use Packages\Dental\Src\Models\DentalFacility;
use Packages\Dental\Src\Models\DentalService;
use Packages\Dental\Src\Repositories\Interfaces\DentalServiceRepositoryInterface;

class DentalServiceController extends BaseController
{
    public function __construct(private readonly DentalServiceRepositoryInterface $repository) {}

    public function index(): View
    {
        $table = Table::make(DentalService::with(['translations', 'facility.translations']))
            ->heading(__('dental::dental.service.index'))
            ->searchable(true, ['translations.title', 'translations.slug'])
            ->columns([
                AvatarColumn::make('title')
                    ->label(__('dental::dental.fields.title'))
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
                SelectFilter::make('dental_facility_id')
                    ->label(__('dental::dental.fields.facility'))
                    ->options($this->facilityOptions())
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
                    ->route(admin_route_name('dental_services.edit'))
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

        return view('dental::admin.services.index', compact('table'));
    }

    public function create(): View
    {
        return view('dental::admin.services.create', [
            'service' => new DentalService(['status' => PublishStatus::Draft->value]),
            'facilities' => $this->facilityOptions(),
            'locales' => $this->locales(),
        ]);
    }

    public function store(StoreDentalServiceRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $service = $this->repository->create($this->attributes($data));

            $this->syncTranslations($service, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('dental_services.index'), __('dental::dental.service.created'));
    }

    public function edit(int $service): View
    {
        return view('dental::admin.services.edit', [
            'service' => DentalService::with(['translations', 'iconMedia', 'videoPosterMedia'])->findOrFail($service),
            'facilities' => $this->facilityOptions(),
            'locales' => $this->locales(),
        ]);
    }

    public function update(UpdateDentalServiceRequest $request, int $service): RedirectResponse
    {
        $data = $request->validated();
        $model = DentalService::findOrFail($service);

        DB::transaction(function () use ($model, $data) {
            $model->update($this->attributes($data, $model));

            $this->syncTranslations($model, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('dental_services.index'), __('dental::dental.service.updated'));
    }

    public function destroy(int $service): RedirectResponse
    {
        abort_unless(request()->user()?->hasPermission('dental.delete'), 403);

        $this->repository->delete($service);

        return $this->redirectWithSuccess(admin_route_name('dental_services.index'), __('dental::dental.service.deleted'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('dental.delete'), 403);

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:dental_services,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $id) {
                $this->repository->delete((int) $id);
            }
        });

        return $this->redirectWithSuccess(
            admin_route_name('dental_services.index'),
            __('dental::dental.service.bulk_deleted', ['count' => count($validated['ids'])])
        );
    }

    private function attributes(array $data, ?DentalService $existing = null): array
    {
        $publishedAt = $data['published_at'] ?? $existing?->published_at;

        // Chuyển sang published mà chưa có mốc thời gian thì lấy hiện tại, để bài không bị ẩn
        if ($data['status'] === PublishStatus::Published->value && $publishedAt === null) {
            $publishedAt = now();
        }

        return [
            'dental_facility_id' => $data['dental_facility_id'],
            'status' => $data['status'],
            'published_at' => $publishedAt,
            'icon_media_id' => $data['icon_media_id'] ?? null,
            'video_poster_media_id' => $data['video_poster_media_id'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'sort_order' => $data['sort_order'] ?? $existing?->sort_order ?? 0,
        ];
    }

    private function syncTranslations(DentalService $service, array $translations): void
    {
        foreach ($translations as $row) {
            $service->translateOrNew($row['locale'])->fill([
                'title' => $row['title'],
                'slug' => $row['slug'],
                'hero_h1' => $row['hero_h1'] ?? null,
                'video_caption' => $row['video_caption'] ?? null,
                'body' => $row['body'] ?? null,
                'comparison_html' => $row['comparison_html'] ?? null,
                'price_table_html' => $row['price_table_html'] ?? null,
            ])->save();
        }
    }

    /**
     * @return array<int, string>
     */
    private function facilityOptions(): array
    {
        return DentalFacility::with('translations')
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (DentalFacility $f) => [$f->id => $f->translate(app()->getLocale())?->name ?? $f->translations->first()?->name ?? '#'.$f->id])
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
