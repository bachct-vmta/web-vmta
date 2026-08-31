<?php

namespace Packages\Core\Src\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Http\Requests\Media\MediaRequest;
use Packages\Core\Src\Http\Requests\Media\MediaUpdateRequest;
use Packages\Core\Src\Http\Resources\MediaFileResource;
use Packages\Core\Src\Http\Resources\MediaFolderResource;
use Packages\Core\Src\Repositories\Interfaces\MediaFileRepositoryInterface;
use Packages\Core\Src\Repositories\Interfaces\MediaFolderRepositoryInterface;
use Packages\Core\Src\Services\MediaFileService;
use Packages\Core\Src\Services\MediaFolderService;

/**
 * Media Controller
 *
 * Handles media library index, loading, and trash operations.
 */
class MediaController extends BaseController
{
    public function __construct(
        private MediaFileRepositoryInterface $fileRepository,
        private MediaFolderRepositoryInterface $folderRepository,
        private MediaFolderService $folderService,
        private MediaFileService $fileService,
    ) {}

    /**
     * Display media library index
     */
    public function index(Request $request)
    {
        $isChoose = $request->get('isChoose') ?? false;
        $popup = (bool) $request->get('popup');

        return view('core-media::media', compact('isChoose', 'popup'));
    }

    /**
     * Load media files and folders with pagination
     */
    public function loadMedia(MediaRequest $request): JsonResponse
    {
        $limit = file_manager_setting('media_limit', 30);

        Artisan::call('optimize:clear');

        $data = $request->validated();
        $isTrash = $request->get('view_in') == 'trash';
        $data['is_trash'] = $isTrash;

        $breadcrumbs = $this->buildBreadcrumbs($data['folder_id'] ?? null);

        $data['paged'] = $data['paged'] ?? 1;
        $limit = $data['posts_per_page'] = $data['posts_per_page'] ?? $limit;

        // Non-admin users can only see their own files
        if ($request->user() && isset($request->user()->role_id) && $request->user()->role_id !== 1) {
            $data['user_id'] = $request->user()->id;
        }

        $countFolders = $this->folderRepository->getCount($data);
        $countFiles = $this->fileRepository->getCount($data);

        if ($data['load_more'] == 'false') {
            return $this->loadInitialMedia($data, $breadcrumbs, $countFolders, $countFiles, $limit);
        }

        return $this->loadMoreMedia($data, $breadcrumbs, $countFolders, $countFiles, $limit);
    }

    /**
     * Rename file or folder
     */
    public function updateName(MediaUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $repoUsed = $data['is_folder'] !== 'false' ? $this->folderRepository : $this->fileRepository;
        $serviceUsed = $data['is_folder'] !== 'false' ? $this->folderService : $this->fileService;

        $item = $repoUsed->find($data['id']);

        if (! $item) {
            return $this->error(trans('core-media::media.not_found'), 404);
        }

        $isChecked = $serviceUsed->find($item->permalink);

        if (! $isChecked) {
            return $this->error(trans('core-media::media.not_found'), 404);
        }

        $parentFolder = null;
        if ($item->parent_id ?? $item->folder_id) {
            $findFolder = $this->folderRepository->find($item->parent_id ?? $item->folder_id);
            $parentFolder = $findFolder?->permalink;
        }

        $changed = $serviceUsed->renameItem($item, $data['name'], $parentFolder);

        if (! $changed['success']) {
            return response()->json($changed);
        }

        $updateData = [
            'name' => $data['name'],
            'permalink' => $changed['path'],
        ];

        if (isset($changed['alt'])) {
            $updateData['alt'] = $changed['alt'];
        }

        $repoUsed->update($item->id, $updateData);

        return response()->json($changed);
    }

    /**
     * Move item to trash (soft delete)
     */
    public function removeTrash(MediaUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $repoUsed = $data['is_folder'] !== 'false' ? $this->folderRepository : $this->fileRepository;
        $item = $repoUsed->find($data['id']);

        if (! $item) {
            return $this->error(trans('core-media::media.not_found'), 404);
        }

        $repoUsed->update($item->id, [
            'deleted_at' => Carbon::now()->addDays(7),
        ]);

        return $this->success(null, trans('core-media::media.trash_success'));
    }

    /**
     * Restore item from trash
     */
    public function restoreTrash(MediaUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $repoUsed = $data['is_folder'] !== 'false' ? $this->folderRepository : $this->fileRepository;
        $item = $repoUsed->findWithTrashed($data['id']);

        if (! $item) {
            return $this->error(trans('core-media::media.not_found'), 404);
        }

        $item->update(['deleted_at' => null]);

        return $this->success(null, trans('core-media::media.restore_success'));
    }

    /**
     * Permanently delete item
     */
    public function removeNotRestore(MediaUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $repoUsed = $data['is_folder'] !== 'false' ? $this->folderRepository : $this->fileRepository;
        $item = $repoUsed->findWithTrashed($data['id']);

        if (! $item) {
            return $this->error(trans('core-media::media.not_found'), 404);
        }

        $item->forceDelete();

        return $this->success(null, trans('core-media::media.trash_success'));
    }

    /**
     * Empty all trash
     */
    public function emptyAllTrash(): JsonResponse
    {
        $this->fileRepository->getDelete()->each->forceDelete();
        $this->folderRepository->getDelete()->each->forceDelete();

        return $this->success(null, trans('core-media::media.empty_trash_success'));
    }

    /**
     * Build breadcrumbs navigation
     */
    private function buildBreadcrumbs(?int $folderId): array
    {
        $breadcrumbs = [
            0 => [
                'id' => 0,
                'name' => 'All Media',
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M15 8h.01"></path><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z"></path><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5"></path><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3"></path></svg>',
            ],
        ];

        if ($folderId) {
            $breadcrumbs = array_merge($breadcrumbs, $this->getFolderBreadcrumbs($folderId));
        }

        return $breadcrumbs;
    }

    /**
     * Get folder breadcrumbs recursively
     */
    private function getFolderBreadcrumbs(int $folderId): array
    {
        $breadcrumbs = [];
        $folder = $this->folderRepository->find($folderId);

        if ($folder) {
            $breadcrumbs[] = [
                'id' => $folder->id,
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path></svg>',
                'name' => ucfirst($folder->name),
            ];

            if ($folder->parent_id) {
                $breadcrumbs = array_merge($this->getFolderBreadcrumbs($folder->parent_id), $breadcrumbs);
            }
        }

        return $breadcrumbs;
    }

    /**
     * Load initial media (first page)
     */
    private function loadInitialMedia(array $data, array $breadcrumbs, int $countFolders, int $countFiles, int $limit): JsonResponse
    {
        $allFiles = [];
        $allFolders = $this->folderRepository->filter($data);

        if (count($allFolders) < $data['posts_per_page']) {
            $data['posts_per_page'] = $data['posts_per_page'] - count($allFolders);
            $allFiles = $this->fileRepository->filter($data);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'breadcrumbs' => $breadcrumbs,
                'folders' => MediaFolderResource::collection($allFolders),
                'files' => MediaFileResource::collection($allFiles),
            ],
            'load_more' => $countFolders > $limit || $countFiles > $limit,
            'next' => (int) $data['paged'] + 1,
            'type' => $countFolders > $limit ? 'folder' : 'file',
        ]);
    }

    /**
     * Load more media (pagination)
     */
    private function loadMoreMedia(array $data, array $breadcrumbs, int $countFolders, int $countFiles, int $limit): JsonResponse
    {
        $dataResponse = match ($data['type']) {
            'file' => ['file' => $this->fileRepository->filter($data)],
            'folder' => ['folder' => $this->folderRepository->filter($data)],
            default => [],
        };

        return response()->json([
            'success' => true,
            'data' => [
                'breadcrumbs' => $breadcrumbs,
                'folders' => isset($dataResponse['folder']) ? MediaFolderResource::collection($dataResponse['folder']) : null,
                'files' => isset($dataResponse['file']) ? MediaFileResource::collection($dataResponse['file']) : null,
            ],
            'load_more' => ($countFolders > $limit * $data['paged']) || ($countFiles > $limit * $data['paged']),
            'next' => (int) $data['paged'] + 1,
            'type' => $data['type'],
        ]);
    }
}
