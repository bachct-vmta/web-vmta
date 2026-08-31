<?php

namespace Packages\Content\Src\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Models\Page;
use Packages\Content\Src\Models\Post;
use Packages\Content\Src\Repositories\Interfaces\CategoryRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\PageRepositoryInterface;
use Packages\Content\Src\Repositories\Interfaces\PostRepositoryInterface;

/**
 * Aggregate service for Page/Post/Category CRUD.
 * Handles nested translation payload from admin forms.
 */
class ContentService
{
    public function __construct(
        private PageRepositoryInterface $pages,
        private PostRepositoryInterface $posts,
        private CategoryRepositoryInterface $categories,
    ) {}

    public function createPage(array $data): Page
    {
        return DB::transaction(fn () => $this->pages->create($data));
    }

    public function updatePage(int $id, array $data): bool
    {
        return DB::transaction(fn () => $this->pages->update($id, $data));
    }

    public function deletePage(int $id): bool
    {
        return DB::transaction(fn () => $this->pages->delete($id));
    }

    public function createPost(array $data): Post
    {
        return DB::transaction(fn () => $this->posts->create($data));
    }

    public function updatePost(int $id, array $data): bool
    {
        return DB::transaction(fn () => $this->posts->update($id, $data));
    }

    public function deletePost(int $id): bool
    {
        return DB::transaction(fn () => $this->posts->delete($id));
    }

    public function createCategory(array $data): Model
    {
        return DB::transaction(fn () => $this->categories->create($data));
    }

    public function updateCategory(int $id, array $data): bool
    {
        return DB::transaction(fn () => $this->categories->update($id, $data));
    }

    public function deleteCategory(int $id): bool
    {
        return DB::transaction(fn () => $this->categories->delete($id));
    }
}
