<?php

namespace Packages\Core\Src\Repositories\Eloquent;

use Packages\Core\Src\Models\MediaFile;
use Packages\Core\Src\Repositories\Interfaces\MediaFileRepositoryInterface;

/**
 * MediaFile Repository
 */
class MediaFileRepository extends BaseRepository implements MediaFileRepositoryInterface
{
    public function getModel(): string
    {
        return MediaFile::class;
    }

    /**
     * Filter files by various criteria
     */
    public function filter(array $data)
    {
        $isTrash = isset($data['is_trash']) && $data['is_trash'];

        // Trashed rows are hidden by the SoftDeletes global scope; include them when listing trash.
        $query = $isTrash ? $this->model->withTrashed() : $this->model->query();

        if ($isTrash) {
            $query->whereNotNull('deleted_at');
        } else {
            $query->whereNull('deleted_at');
        }

        // Pagination
        $paged = $data['paged'] ?? 1;
        $limit = $data['posts_per_page'] ?? 30;
        $offset = ($paged - 1) * $limit;
        $query->limit($limit)->offset($offset);

        // User filter
        if (isset($data['user_id'])) {
            $query->where('user_id', $data['user_id']);
        }

        // Multiple conditions
        $query->where(function ($q) use ($data) {
            if (isset($data['folder_id'])) {
                $q->where('folder_id', $data['folder_id']);
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

            if (isset($data['filter_type'])) {
                $this->applyTypeFilter($q, $data['filter_type']);
            }
        });

        // Sorting
        if (isset($data['sort_by'])) {
            $order = explode('-', $data['sort_by']);
            $query->orderBy($order[0], $order[1]);
        }

        return $query->get();
    }

    /**
     * Apply MIME type filter
     */
    protected function applyTypeFilter($query, string $type): void
    {
        match ($type) {
            'image' => $query->where('mine_type', 'like', 'image/%'),
            'video' => $query->where('mine_type', 'like', 'video/%'),
            'document' => $query->where(function ($q) {
                $q->where('mine_type', 'like', 'application/pdf')
                    ->orWhere('mine_type', 'like', 'application/msword')
                    ->orWhere('mine_type', 'like', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
                    ->orWhere('mine_type', 'like', 'application/vnd.ms-excel')
                    ->orWhere('mine_type', 'like', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                    ->orWhere('mine_type', 'like', 'text/csv');
            }),
            'media' => $query->where(function ($q) {
                $q->where('mine_type', 'like', 'image/%')
                    ->orWhere('mine_type', 'like', 'video/%');
            }),
            default => null, // 'everything' - no additional filter
        };
    }

    /**
     * Get count of files matching criteria
     */
    public function getCount($data)
    {
        $query = $this->model->whereNull('deleted_at');

        if (isset($data['is_trash']) && $data['is_trash']) {
            $query = $this->model->withTrashed()->whereNotNull('deleted_at');
        }

        if (isset($data['folder_id'])) {
            $query->where('folder_id', $data['folder_id']);
        }

        if (isset($data['user_id'])) {
            $query->where('user_id', $data['user_id']);
        }

        return $query->count('id');
    }

    /**
     * Find files by folder ID
     */
    public function findByFolderId($folderId)
    {
        return $this->model->where('folder_id', $folderId)->get();
    }

    /**
     * Update file by permalink pattern
     */
    public function updateFileByPermalink($permalink, array $data)
    {
        return $this->model->where('permalink', 'LIKE', "%$permalink%")->update($data);
    }

    /**
     * Insert uploaded files
     */
    public function updateFile(array $data, $request): array
    {
        if (empty($data)) {
            return [
                'success' => false,
                'message' => trans('core-media::media.message.file_not_uploaded'),
            ];
        }

        $insertData = collect($data)->map(function ($item) use ($request) {
            if (isset($item['data'])) {
                $fileData = $item['data'];
                $fileData['user_id'] = $request->user()->id;
                $fileData['folder_id'] = $request->get('folderId') ?? 0;

                return $fileData;
            }
        })->filter()->toArray();

        if (empty($insertData)) {
            return [
                'success' => false,
                'message' => trans('core-media::media.message.file_not_uploaded'),
            ];
        }

        $inserted = $this->insert($insertData);

        if (! $inserted) {
            return [
                'success' => false,
                'message' => trans('core-media::media.message.file_not_uploaded'),
            ];
        }

        return [
            'success' => true,
            'message' => trans('core-media::media.message.file_uploaded'),
            'data' => $data,
        ];
    }

    /**
     * Update file size after cropping
     */
    public function updateSize($id, array $size): array
    {
        $file = $this->find($id);

        if (! $file) {
            return [
                'success' => false,
                'message' => trans('core-media::media.message.file_not_found'),
            ];
        }

        $isUpdated = $this->update($id, $size);

        if (! $isUpdated) {
            return [
                'success' => false,
                'message' => trans('core-media::media.message.file_not_found'),
            ];
        }

        return [
            'success' => true,
            'message' => trans('core-media::media.message.update_success'),
        ];
    }

    /**
     * Get soft-deleted files
     */
    public function getDelete()
    {
        return $this->model->withTrashed()->whereNotNull('deleted_at')->get();
    }

    /**
     * Find a file by ID including trashed rows (bypasses SoftDeletes scope)
     */
    public function findWithTrashed($id)
    {
        return $this->model->withTrashed()->find($id);
    }

    /**
     * Bulk insert files
     */
    public function insert(array $data): bool
    {
        return $this->model->insert($data);
    }
}
