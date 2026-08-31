<?php

namespace Packages\Core\Src\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\Core\Src\Chunks\Save\ChunkSave;
use Packages\Core\Src\Chunks\Storage\ChunkStorage;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Http\Requests\Media\FileCropRequest;
use Packages\Core\Src\Http\Requests\Media\FileRequest;
use Packages\Core\Src\Http\Requests\Media\FileUpdateRequest;
use Packages\Core\Src\Repositories\Interfaces\MediaFileRepositoryInterface;
use Packages\Core\Src\Repositories\Interfaces\MediaFolderRepositoryInterface;
use Packages\Core\Src\Services\MediaFileService;

/**
 * MediaFile Controller
 *
 * Handles file upload, crop, and update operations.
 */
class MediaFileController extends BaseController
{
    public function __construct(
        private MediaFolderRepositoryInterface $folderRepository,
        private MediaFileRepositoryInterface $fileRepository,
        private MediaFileService $fileService,
    ) {}

    /**
     * Upload file from URL
     */
    public function uploadFileUrl(Request $request): JsonResponse
    {
        $validator = Validator::make($request->input(), [
            'url' => 'required|url',
            'folderId' => 'nullable|integer',
        ], [
            'url.required' => trans('core-media::media.message.url'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $folder = $this->folderRepository->find($request->get('folderId'));
        $folderPath = $folder?->permalink ?? '';

        $filePath = $this->fileService->uploadByURL(trim($request->get('url')), $folderPath);

        return response()->json($this->fileRepository->updateFile($filePath, $request));
    }

    /**
     * Upload files from form
     */
    public function uploadFile(FileRequest $request): JsonResponse
    {
        $data = $request->validated();

        $folder = null;
        if ($request->get('folderId')) {
            $folder = $this->folderRepository->find($request->get('folderId'));
            if (! $folder) {
                return $this->error(trans('core-media::media.message.folder_not_found'), 404);
            }
        }

        $files = $this->fileService->uploadMultipleFile($data['files'], $folder);

        $errors = collect($files)->filter(fn ($file) => ! $file['success']);

        if ($errors->isEmpty()) {
            return response()->json($this->fileRepository->updateFile($files, $request));
        }

        return response()->json($errors->first());
    }

    /**
     * Save cropped image
     */
    public function saveCropImage(FileCropRequest $request): JsonResponse
    {
        $data = $request->validated();

        $file = $this->fileRepository->find($data['image_id']);

        if (! $file) {
            return $this->error(trans('core-media::media.message.file_not_found'), 404);
        }

        $cropResult = $this->fileService->cropImage($file->permalink, $data['crop_data']);

        if (! $cropResult['success']) {
            return response()->json($cropResult);
        }

        return response()->json($this->fileRepository->updateSize($data['image_id'], $cropResult['data']));
    }

    /**
     * Update file metadata (alt, name, etc.)
     */
    public function updateData(FileUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $file = $this->fileRepository->find($data['id']);

        if (! $file) {
            return $this->error(trans('core-media::media.message.file_not_found'), 404);
        }

        $updated = $this->fileRepository->update($data['id'], $data);

        if (! $updated) {
            return $this->error(trans('core-media::media.message.file_not_found'));
        }

        return $this->success(null, trans('core-media::media.message.update_success'));
    }

    /**
     * Upload file chunk (for large file uploads)
     */
    public function uploadChunk(Request $request): JsonResponse
    {
        // Check if chunk upload is enabled
        if (! is_chunk_upload_enabled()) {
            return $this->error('Chunk upload is disabled', 400);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
            'chunk_index' => 'required|integer|min:0',
            'total_chunks' => 'required|integer|min:1',
            'file_name' => 'required|string',
            'folder_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        try {
            $storage = new ChunkStorage;

            $chunkSave = new ChunkSave(
                $request->file('file'),
                $storage,
                md5($request->input('file_name')),
                $request->input('chunk_index'),
                $request->input('total_chunks')
            );

            $result = $chunkSave->handle();

            // If upload is complete, save to database
            if ($result['finished'] ?? false) {
                $folder = $request->input('folder_id')
                    ? $this->folderRepository->find($request->input('folder_id'))
                    : null;

                // Use the merged file for database storage
                $uploadResult = $this->fileService->uploadMultipleFile(
                    [$result['file']],
                    $folder
                );

                if (! empty($uploadResult[0]['success'])) {
                    return response()->json(
                        $this->fileRepository->updateFile($uploadResult, $request)
                    );
                }

                return $this->error($uploadResult[0]['message'] ?? 'Upload failed');
            }

            return response()->json($result);
        } catch (\Exception $e) {
            \Log::error('Chunk upload failed', ['error' => $e->getMessage()]);

            return $this->error(trans('core-media::media.message.file_not_uploaded'));
        }
    }
}
