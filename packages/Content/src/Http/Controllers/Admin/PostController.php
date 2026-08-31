<?php

namespace Packages\Content\Src\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Packages\Content\Src\Http\Requests\Admin\StorePostRequest;
use Packages\Content\Src\Http\Requests\Admin\UpdatePostRequest;
use Packages\Content\Src\Models\Category;
use Packages\Content\Src\Models\Post;
use Packages\Content\Src\Repositories\Interfaces\PostRepositoryInterface;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Tables\Actions\Action;
use Packages\Core\Src\Tables\Actions\BulkAction;
use Packages\Core\Src\Tables\Columns\AvatarColumn;
use Packages\Core\Src\Tables\Columns\BadgeColumn;
use Packages\Core\Src\Tables\Columns\BooleanColumn;
use Packages\Core\Src\Tables\Columns\DateColumn;
use Packages\Core\Src\Tables\Filters\SelectFilter;
use Packages\Core\Src\Tables\Table;

class PostController extends BaseController
{
    public function __construct(private readonly PostRepositoryInterface $repository) {}

    public function index(): View
    {
        $categories = Category::with('translations')->orderBy('sort_order')->get();
        $categoryOptions = $categories->mapWithKeys(fn ($c) => [
            $c->id => $c->translations->firstWhere('locale', app()->getLocale())?->name
                ?? $c->translations->first()?->name
                ?? '#'.$c->id,
        ])->toArray();

        $table = Table::make(Post::with(['translations', 'category.translations']))
            ->heading(__('content::content.post.index'))
            ->searchable(true, ['translations.title', 'translations.slug'])
            ->columns([
                AvatarColumn::make('title')
                    ->label(__('content::content.fields.title'))
                    ->secondary('slug'),
                BadgeColumn::make('status')
                    ->label(__('content::content.fields.status'))
                    ->colors(['draft' => 'gray', 'published' => 'green'])
                    ->formatStateUsing(fn ($v) => $v ? __('content::content.status.'.$v) : ''),
                BooleanColumn::make('is_featured')
                    ->label(__('content::content.fields.is_featured'))
                    ->labels('★', '—'),
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
                SelectFilter::make('category_id')
                    ->label(__('content::content.fields.category'))
                    ->options($categoryOptions)
                    ->placeholder('—'),
            ])
            ->actions([
                Action::make('edit')
                    ->label(__('content::content.actions.edit'))
                    ->iconEdit()
                    ->route(admin_route_name('posts.edit'))
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

        return view('content::admin.posts.index', compact('table'));
    }

    public function create(): View
    {
        return view('content::admin.posts.create', [
            'post' => new Post(['status' => 'draft', 'is_featured' => false]),
            'categories' => Category::with('translations')->orderBy('sort_order')->get(),
            'locales' => array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []])),
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request) {
            $post = $this->repository->create([
                'category_id' => $data['category_id'] ?? null,
                'author_id' => $request->user()->id,
                'cover_media_id' => $data['cover_media_id'] ?? null,
                'status' => $data['status'],
                'is_featured' => (bool) ($data['is_featured'] ?? false),
                'published_at' => $data['published_at'] ?? null,
            ]);

            $this->syncTranslations($post, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('posts.index'), __('content::content.post.created'));
    }

    public function edit(int $post): View
    {
        $model = Post::with(['translations', 'coverMedia'])->findOrFail($post);

        return view('content::admin.posts.edit', [
            'post' => $model,
            'categories' => Category::with('translations')->orderBy('sort_order')->get(),
            'locales' => array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []])),
        ]);
    }

    public function update(UpdatePostRequest $request, int $post): RedirectResponse
    {
        $data = $request->validated();
        $model = Post::findOrFail($post);

        DB::transaction(function () use ($model, $data) {
            $model->update([
                'category_id' => $data['category_id'] ?? null,
                'cover_media_id' => $data['cover_media_id'] ?? null,
                'status' => $data['status'],
                'is_featured' => (bool) ($data['is_featured'] ?? false),
                'published_at' => $data['published_at'] ?? null,
            ]);

            $this->syncTranslations($model, $data['translations']);
        });

        return $this->redirectWithSuccess(admin_route_name('posts.index'), __('content::content.post.updated'));
    }

    public function destroy(int $post): RedirectResponse
    {
        abort_unless(request()->user()?->hasPermission('content.delete'), 403);

        $this->repository->delete($post);

        return $this->redirectWithSuccess(admin_route_name('posts.index'), __('content::content.post.deleted'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->hasPermission('content.delete'), 403);

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:posts,id',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $id) {
                $this->repository->delete((int) $id);
            }
        });

        $count = count($validated['ids']);

        return $this->redirectWithSuccess(
            admin_route_name('posts.index'),
            __('content::content.post.bulk_deleted', ['count' => $count])
        );
    }

    private function syncTranslations(Post $post, array $translations): void
    {
        foreach ($translations as $row) {
            // Only fill SEO fields when the form actually submitted them — preserves
            // existing values when the admin form omits SEO inputs.
            $payload = [
                'title' => $row['title'],
                'slug' => $row['slug'],
                'excerpt' => $row['excerpt'] ?? null,
                'body' => $row['body'] ?? null,
            ];
            foreach (['seo_title', 'seo_description', 'seo_og_image'] as $seoField) {
                if (array_key_exists($seoField, $row)) {
                    $payload[$seoField] = $row[$seoField];
                }
            }
            $post->translateOrNew($row['locale'])->fill($payload)->save();
        }
    }
}
