<?php

namespace Packages\Core\Src\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Http\Requests\Media\FolderRequest;
use Packages\Core\Src\Repositories\Interfaces\MediaFolderRepositoryInterface;
use Packages\Core\Src\Services\MediaFolderService;

/**
 * MediaFolder Controller
 *
 * Handles folder creation operations.
 */
class MediaFolderController extends BaseController
{
    public function __construct(
        private MediaFolderRepositoryInterface $folderRepository,
        private MediaFolderService $folderService,
    ) {}

    /**
     * Create a new folder
     */
    public function saveFolder(FolderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $path = null;

        if ($data['parent_id']) {
            $parentFolder = $this->folderRepository->find($data['parent_id']);
            $path = $parentFolder?->permalink;
        }

        $results = $this->folderService->createDir($data['name'], $path);

        if (! $results['success']) {
            return response()->json($results);
        }

        $data = array_merge($data, [
            'permalink' => $results['path'],
            'user_id' => $request->user()->id,
        ]);

        $created = $this->folderRepository->create($data);

        if (! $created) {
            return $this->error($results['message']);
        }

        return $this->success(null, $results['message']);
    }
}
