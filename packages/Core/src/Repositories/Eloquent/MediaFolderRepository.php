<?php

namespace Packages\Core\Src\Repositories\Eloquent;

use Packages\Core\Src\Models\MediaFolder;
use Packages\Core\Src\Repositories\Interfaces\MediaFolderRepositoryInterface;

/**
 * MediaFolder Repository
 */
class MediaFolderRepository extends BaseRepository implements MediaFolderRepositoryInterface
{
    public function getModel(): string
    {
        return MediaFolder::class;
    }

    /**
     * Filter folders by criteria
     */
    public function filter(array $data)
    {
        $query = $this->model->query();

        // Filter by trash status
        if (isset($data['is_trash']) && $data['is_trash']) {
            $query->whereNotNull('deleted_at');
        } else {
            $query->whereNull('deleted_at');
        }

        // Pagination
        $paged = $data['paged'] ?? 1;
        $limit = $data['posts_per_page'] ?? 30;
        $offset = ($paged - 1) * $limit;
        $query->limit($limit)->offset($offset);

        // Multiple conditions
        $query->where(function ($q) use ($data) {
            if (isset($data['folder_id'])) {
                $q->where('parent_id', $data['folder_id']);
            }

            if (! empty($data['search'])) {
                $q->where(function ($sq) use ($data) {
                    $sq->where('name', 'like', '%'.$data['search'].'%')
                        ->orWhere('permalink', 'like', '%'.$data['search'].'%');
                });
            }

            if (! empty($data['ids'])) {
                $q->whereNotIn('id', $data['ids']);
            }
        });

        // Sorting (exclude 'size' which doesn't apply to folders)
        if (isset($data['sort_by'])) {
            $order = explode('-', $data['sort_by']);
            if ($order[0] !== 'size') {
                $query->orderBy($order[0], $order[1]);
            }
        }

        return $query->get();
    }

    /**
     * Get count of folders matching criteria
     */
    public function getCount($data)
    {
        $query = $this->model->whereNull('deleted_at');

        if (isset($data['is_trash']) && $data['is_trash']) {
            $query = $this->model->whereNotNull('deleted_at');
        }

        if (isset($data['folder_id'])) {
            $query->where(function ($q) use ($data) {
                $q->where('id', $data['folder_id'])
                    ->orWhere('parent_id', $data['folder_id']);
            });
        }

        return $query->count('id');
    }

    /**
     * Get soft-deleted folders
     */
    public function getDelete()
    {
        return $this->model->whereNotNull('deleted_at')->get();
    }

    /**
     * Find a folder by ID including trashed rows.
     * MediaFolder has no SoftDeletes global scope, so a plain find already returns trashed rows.
     */
    public function findWithTrashed($id)
    {
        return $this->find($id);
    }
}
