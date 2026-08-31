<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Packages\Content\Src\Http\Requests\Admin\StoreCategoryRequest;
use Packages\Content\Src\Http\Requests\Admin\UpdateCategoryRequest;
use Packages\Content\Src\Models\Category;
use Packages\Content\Src\Repositories\Interfaces\CategoryRepositoryInterface;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\Columns\AvatarColumn;
use Packages\Core\Src\Tables\Columns\BooleanColumn;
use Packages\Core\Src\Tables\Columns\NumericColumn;
use Packages\Core\Src\Tables\Table;

class CategoryController extends BaseController
{
    public function __construct(private readonly CategoryRepositoryInterface $repository) {}

    public function index(): View
    {
        $table = Table::make(Category::with(['translations', 'parent.translations']))
            ->heading(__('content::content.category.index'))
            ->searchable(true, ['translations.name', 'translations.slug'])
            ->columns([
                AvatarColumn::make('name')
                    ->label(__('content::content.fields.name'))
                    ->secondary('slug'),
                BooleanColumn::make('is_active')
                    ->label(__('content::content.fields.is_active')),
                NumericColumn::make('sort_order')
                    ->label(__('content::content.fields.sort_order'))
                    ->alignRight()
                    ->sortable(),
            ])
            ->actions([
                Action::make('edit')
                    ->label(__('content::content.actions.edit'))
                    ->iconEdit()
                    ->route(admin_route_name('categories.edit'))
                    ->permission('content.edit'),
            ])
            ->bulkActions([
                BulkAction::make('delete')
                    ->label(__('content::content.actions.delete'))
                    ->icon('delete_sweep')
                    ->danger()
                    ->confirm(__('content::content.actions.delete_confirm'))
                    ->permission('content.delete'),
            ])
            ->defaultSort('sort_order', 'asc')
            ->paginate(20);

        return view('content::admin.categories.index', compact('table'));
    }

    public function create(): View
    {
        return view('content::admin.categories.create', [
            'category' => new Category(['is_active' => true, 'sort_order' => 0]),
            'parentOptions' => Category::with('translations')->orderBy('sort_order')->get(),
            'locales' => array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []])),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $category = $this->repository->create([
                'parent_id' => $data['parent_id'] ?? null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($category, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('categories.index'), __('content::content.category.created'));
    }

    public function edit(int $category): View
    {
        $model = Category::with('translations')->findOrFail($category);

        return view('content::admin.categories.edit', [
            'category' => $model,
            'parentOptions' => Category::with('translations')
                ->where('id', '!=', $model->id)
                ->orderBy('sort_order')
                ->get(),
            'locales' => array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []])),
        ]);
    }

    public function update(UpdateCategoryRequest $request, int $category): RedirectResponse
    {
        $data = $request->validated();
        $model = Category::findOrFail($category);

        DB::transaction(function () use ($model, $data) {
            $model->update([
                'parent_id' => $data['parent_id'] ?? null,
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $this->syncTranslations($model, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('categories.index'), __('content::content.category.updated'));
    }

    public function destroy(int $category): RedirectResponse
    {
        abort_unless(request()->user()?->hasPermission('content.delete'), 403);

        $this->repository->delete($category);

        return $this->redirectWithSuccess(admin_route_name('categories.index'), __('content::content.category.deleted'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('content.delete'), 403);

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:categories,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $id) {
                $this->repository->delete((int) $id);
            }
        });

        $count = count($validated['ids']);

        return $this->redirectWithSuccess(
            admin_route_name('categories.index'),
            __('content::content.category.bulk_deleted', ['count' => $count])
        );
    }

    private function syncTranslations(Category $category, array $translations): void
    {
        foreach ($translations as $row) {
            $category->translateOrNew($row['locale'])->fill([
                'name' => $row['name'],
                'slug' => $row['slug'],
                'description' => $row['description'] ?? null,
            ])->save();
        }
    }
}
