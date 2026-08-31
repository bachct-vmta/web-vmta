<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Packages\Content\Src\Http\Requests\Admin\StorePageRequest;
use Packages\Content\Src\Http\Requests\Admin\UpdatePageRequest;
use Packages\Content\Src\Models\Page;
use Packages\Content\Src\Repositories\Interfaces\PageRepositoryInterface;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\Columns\AvatarColumn;
use Packages\Core\Src\Tables\Columns\BadgeColumn;
use Packages\Core\Src\Tables\Columns\DateColumn;
use Packages\Core\Src\Tables\Filters\SelectFilter;
use Packages\Core\Src\Tables\Table;

class PageController extends BaseController
{
    public function __construct(private readonly PageRepositoryInterface $repository) {}

    public function index(): View
    {
        $table = Table::make(Page::with(['translations']))
            ->heading(__('content::content.page.index'))
            ->searchable(true, ['translations.title', 'translations.slug'])
            ->columns([
                AvatarColumn::make('title')
                    ->label(__('content::content.fields.title'))
                    ->secondary('slug'),
                BadgeColumn::make('status')
                    ->label(__('content::content.fields.status'))
                    ->colors(['draft' => 'gray', 'published' => 'green'])
                    ->formatStateUsing(fn ($v) => $v ? __('content::content.status.'.$v) : ''),
                DateColumn::make('published_at')
                    ->label(__('content::content.fields.published_at'))
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('content::content.fields.status'))
                    ->options([
                        'draft' => __('content::content.status.draft'),
                        'published' => __('content::content.status.published'),
                    ])
                    ->placeholder('—'),
            ])
            ->actions([
                Action::make('edit')
                    ->label(__('content::content.actions.edit'))
                    ->iconEdit()
                    ->route(admin_route_name('pages.edit'))
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
            ->defaultSort('created_at', 'desc')
            ->paginate(15);

        return view('content::admin.pages.index', compact('table'));
    }

    public function create(): View
    {
        return view('content::admin.pages.create', [
            'page' => new Page(['status' => 'draft']),
            'locales' => array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []])),
        ]);
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request) {
            $page = $this->repository->create([
                'status' => $data['status'],
                'template' => $data['template'] ?? null,
                'cover_media_id' => $data['cover_media_id'] ?? null,
                'published_at' => $data['published_at'] ?? null,
                'author_id' => $request->user()->id,
            ]);

            $this->syncTranslations($page, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('pages.index'), __('content::content.page.created'));
    }

    public function edit(int $page): View
    {
        $model = Page::with('translations')->findOrFail($page);

        return view('content::admin.pages.edit', [
            'page' => $model,
            'locales' => array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []])),
        ]);
    }

    public function update(UpdatePageRequest $request, int $page): RedirectResponse
    {
        $data = $request->validated();
        $model = Page::findOrFail($page);

        DB::transaction(function () use ($model, $data) {
            $model->update([
                'status' => $data['status'],
                'template' => $data['template'] ?? null,
                'cover_media_id' => $data['cover_media_id'] ?? null,
                'published_at' => $data['published_at'] ?? null,
            ]);

            $this->syncTranslations($model, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('pages.index'), __('content::content.page.updated'));
    }

    public function destroy(int $page): RedirectResponse
    {
        abort_unless(request()->user()?->hasPermission('content.delete'), 403);

        $this->repository->delete($page);

        return $this->redirectWithSuccess(admin_route_name('pages.index'), __('content::content.page.deleted'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('content.delete'), 403);

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:pages,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $id) {
                $this->repository->delete((int) $id);
            }
        });

        $count = count($validated['ids']);

        return $this->redirectWithSuccess(
            admin_route_name('pages.index'),
            __('content::content.page.bulk_deleted', ['count' => $count])
        );
    }

    /**
     * Upsert each translation row (translateOrNew + fill) so existing translations are updated
     * in place rather than duplicated.
     */
    private function syncTranslations(Page $page, array $translations): void
    {
        foreach ($translations as $row) {
            $locale = $row['locale'];
            $page->translateOrNew($locale)->fill([
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
