<?php

namespace Packages\Core\Src\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Repositories\Interfaces\MediaFileRepositoryInterface;
use Packages\Core\Src\Services\MediaFileService;

/**
 * CKEditor Controller
 *
 * Handles CKEditor image upload integration.
 */
class CKEditorController extends BaseController
{
    public function __construct(
        private MediaFileRepositoryInterface $fileRepository,
        private MediaFileService $fileService,
    ) {}

    /**
     * Upload image for CKEditor
     */
    public function uploadCKEditor(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'upload' => [
                'required',
                'file',
                'mimetypes:'.implode(',', config('file-manager.mime_types', [])),
                'extensions:'.implode(',', config('file-manager.extensions', [])),
                'max:'.(config('file-manager.max_file_size', 10240) / 1024),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'message' => $validator->errors()->first(),
                ],
            ]);
        }

        $files = $this->fileService->uploadMultipleFile($request->file('upload'), null);

        $request->merge(['folderId' => 0]);

        $uploadedFiles = $this->fileRepository->updateFile($files, $request);

        if (! $uploadedFiles['success']) {
            return response()->json([
                'error' => [
                    'message' => $uploadedFiles['message'],
                ],
            ]);
        }

        return response()->json([
            'uploaded' => 1,
            'fileName' => $files[0]['data']['name'],
            'url' => media_permalink_url($files[0]['data']['permalink']),
        ]);
    }
}
