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
use Packages\Dental\Src\Http\Requests\Admin\StoreDentalCategoryRequest;
use Packages\Dental\Src\Http\Requests\Admin\UpdateDentalCategoryRequest;
use Packages\Dental\Src\Models\DentalCategory;
use Packages\Dental\Src\Repositories\Interfaces\DentalCategoryRepositoryInterface;

class DentalCategoryController extends BaseController
{
    public function __construct(private readonly DentalCategoryRepositoryInterface $repository) {}

    public function index(): View
    {
        $table = Table::make(DentalCategory::with(['translations']))
            ->heading(__('dental::dental.category.index'))
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
                SelectFilter::make('status')
                    ->label(__('dental::dental.fields.status'))
                    ->options(PublishStatus::options())
                    ->placeholder('—'),
            ])
            ->actions([
                Action::make('edit')
                    ->label(__('dental::dental.actions.edit'))
                    ->iconEdit()
                    ->route(admin_route_name('dental_categories.edit'))
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

        return view('dental::admin.categories.index', compact('table'));
    }

    public function create(): View
    {
        return view('dental::admin.categories.create', [
            'category' => new DentalCategory(['status' => PublishStatus::Draft->value]),
            'locales' => $this->locales(),
        ]);
    }

    public function store(StoreDentalCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $category = $this->repository->create($this->attributes($data));

            $this->syncTranslations($category, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('dental_categories.index'), __('dental::dental.category.created'));
    }

    public function edit(int $category): View
    {
        return view('dental::admin.categories.edit', [
            'category' => DentalCategory::with('translations')->findOrFail($category),
            'locales' => $this->locales(),
        ]);
    }

    public function update(UpdateDentalCategoryRequest $request, int $category): RedirectResponse
    {
        $data = $request->validated();
        $model = DentalCategory::findOrFail($category);

        DB::transaction(function () use ($model, $data) {
            $model->update($this->attributes($data, $model));

            $this->syncTranslations($model, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('dental_categories.index'), __('dental::dental.category.updated'));
    }

    public function destroy(int $category): RedirectResponse
    {
        abort_unless(request()->user()?->hasPermission('dental.delete'), 403);

        $this->repository->delete($category);

        return $this->redirectWithSuccess(admin_route_name('dental_categories.index'), __('dental::dental.category.deleted'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('dental.delete'), 403);

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:dental_categories,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $id) {
                $this->repository->delete((int) $id);
            }
        });

        return $this->redirectWithSuccess(
            admin_route_name('dental_categories.index'),
            __('dental::dental.category.bulk_deleted', ['count' => count($validated['ids'])])
        );
    }

    private function attributes(array $data, ?DentalCategory $existing = null): array
    {
        $publishedAt = $data['published_at'] ?? $existing?->published_at;

        // Chuyển sang published mà chưa có mốc thời gian thì lấy hiện tại, để danh mục không bị ẩn
        if ($data['status'] === PublishStatus::Published->value && $publishedAt === null) {
            $publishedAt = now();
        }

        return [
            'status' => $data['status'],
            'published_at' => $publishedAt,
            'sort_order' => $data['sort_order'] ?? $existing?->sort_order ?? 0,
        ];
    }

    private function syncTranslations(DentalCategory $category, array $translations): void
    {
        foreach ($translations as $row) {
            $category->translateOrNew($row['locale'])->fill([
                'name' => $row['name'],
                'slug' => $row['slug'],
            ])->save();
        }
    }

    /**
     * @return array<int, string>
     */
    private function locales(): array
    {
        return array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
    }
}
