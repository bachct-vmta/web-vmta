<?php

namespace Packages\Core\Src\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * MediaFile Service
 *
 * Handles file upload, crop, and storage operations.
 * Supports multiple storage drivers via StorageDriverService.
 */
class MediaFileService
{
    protected MediaFolderService $folderService;

    protected MediaResizeService $resizeService;

    protected StorageDriverService $storageDriver;

    public function __construct(
        MediaFolderService $folderService,
        MediaResizeService $resizeService,
        StorageDriverService $storageDriver
    ) {
        $this->folderService = $folderService;
        $this->resizeService = $resizeService;
        $this->storageDriver = $storageDriver;
    }

    /**
     * Get storage disk instance
     *
     * @param  string|null  $driver  Optional driver override
     */
    protected function getDisk(?string $driver = null)
    {
        return $this->storageDriver->getDisk($driver);
    }

    /**
     * Get current storage driver name
     */
    public function getCurrentDriver(): string
    {
        return $this->storageDriver->getCurrentDriver();
    }

    /**
     * Crop an image
     */
    public function cropImage(string $filePath, string $cropData): array
    {
        $disk = $this->getDisk();

        if (! $disk->exists($filePath)) {
            return [
                'success' => false,
                'message' => trans('core-media::media.message.file_not_found'),
            ];
        }

        $this->resizeService->setImageData(array_merge(
            ['path' => $filePath],
            json_decode($cropData, true)
        ));

        return $this->resizeService->resize();
    }

    /**
     * Upload multiple files
     */
    public function uploadMultipleFile($files, $folder): array
    {
        if (! is_array($files)) {
            return [$this->uploadFile($files, $folder)];
        }

        if (empty($files)) {
            return [
                'success' => false,
                'message' => 'No files were uploaded.',
            ];
        }

        $limit = config('file-manager.limit_upload_files', 10);
        if (count($files) > $limit) {
            return [
                'success' => false,
                'message' => trans('core-media::media.message.file_upload_limit'),
            ];
        }

        return array_map(fn ($file) => $this->uploadFile($file, $folder), $files);
    }

    /**
     * Upload a single file
     */
    protected function uploadFile($file, $folder): array
    {
        if (! $file) {
            return [
                'success' => false,
                'message' => 'No files were uploaded.',
            ];
        }

        // Validate MIME type using server-side detection (not client-reported)
        $allowedMimes = $this->getAllowedMimeTypes();
        $serverMime = $file->getMimeType();
        $ext = strtolower($file->getClientOriginalExtension());
        $allowedExts = array_map('strtolower', array_map('trim', media_allowed_mime_types()));

        if (! in_array($serverMime, $allowedMimes) || ! in_array($ext, $allowedExts)) {
            return [
                'success' => false,
                'message' => trans('core-media::media.message.can_not_detect_file_type'),
            ];
        }

        // Validate file size using runtime config
        $maxSize = media_max_file_size();
        if ($file->getSize() > $maxSize) {
            return [
                'success' => false,
                'message' => trans('core-media::media.message.file_too_big', ['size' => $this->formatFileSize($maxSize)]),
            ];
        }

        $disk = $this->getDisk();
        $folderPath = $folder ? '/'.$folder->permalink : '';
        $fileName = $folderPath.'/'.$file->getClientOriginalName();

        // Handle duplicate filenames
        if ($disk->exists($fileName)) {
            $fileName = $folderPath.'/'.time().'_'.$file->getClientOriginalName();
        }

        $isUpload = $disk->put($fileName, $file->getContent());

        if ($isUpload) {
            // For Google Drive, we can't use File::* helpers
            // Use the uploaded file info directly
            $currentDriver = $this->getCurrentDriver();

            if ($currentDriver === 'local') {
                $fileUploaded = $disk->path($fileName);
                $fileMime = File::mimeType($fileUploaded);
                $fileSize = File::size($fileUploaded);
                $baseName = File::name($fileUploaded);
            } else {
                // For cloud storage, use server-side MIME detection
                $fileMime = $file->getMimeType();
                $fileSize = $file->getSize();
                $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            }

            return [
                'success' => true,
                'message' => 'The file has been uploaded',
                'data' => [
                    'name' => $baseName,
                    'alt' => $baseName,
                    'permalink' => $fileName,
                    'mine_type' => $fileMime,
                    'size' => $fileSize,
                    'storage_driver' => $currentDriver,
                ],
            ];
        }

        return [
            'success' => false,
            'message' => trans('core-media::media.message.file_not_uploaded'),
        ];
    }

    /**
     * Check if file exists
     */
    public function find($path): bool
    {
        return $this->getDisk()->exists($path);
    }

    /**
     * Rename a file
     */
    public function renameItem($item, $name, $pathFolder): array
    {
        // Use the file's storage driver
        $driver = $item->storage_driver ?? 'local';
        $disk = $this->getDisk($driver);

        if (! $disk->exists($item->permalink)) {
            return [
                'success' => false,
                'message' => trans('core-media::media.message.file_not_found'),
            ];
        }

        $extension = File::extension($item->permalink);
        $newPath = $pathFolder.'/'.Str::slug($name).'.'.$extension;

        $disk->move($item->permalink, $newPath);

        return [
            'success' => true,
            'message' => trans('core-media::media.message.file_renamed', ['name' => $name]),
            'path' => $newPath,
            'alt' => $name,
        ];
    }

    /**
     * Upload files from URL(s)
     */
    public function uploadByURL($url, $folderPath = ''): array
    {
        $dir = $this->folderService->getPath();

        if ($folderPath && $this->folderService->findDir($folderPath)) {
            $dir = $folderPath;
        }

        $allURL = $this->parseURL($url);

        if (empty($allURL)) {
            return [
                'success' => false,
                'message' => trans('core-media::media.message.file_not_found'),
            ];
        }

        $allUploaded = [];
        foreach ($allURL as $fileUrl) {
            $uploaded = $this->parseImageByURL($fileUrl, $dir);
            if (! empty($uploaded['data'])) {
                $allUploaded[] = $uploaded;
            }
        }

        return $allUploaded;
    }

    /**
     * Parse URL string to array
     */
    protected function parseURL($url): array
    {
        if (! $url) {
            return [];
        }

        return explode("\r\n", $url);
    }

    /**
     * Download and save image from URL
     */
    protected function parseImageByURL($url, $folder): array
    {
        if (! $url) {
            return [];
        }

        // SECURITY: Validate URL scheme (prevent file://, ftp://, etc.)
        $parsed = parse_url($url);
        if (! $parsed || ! in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
            return [];
        }

        // SECURITY: Block private/loopback IPs (SSRF prevention)
        $host = $parsed['host'] ?? '';
        $ip = gethostbyname($host);
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return [];
        }

        $response = Http::timeout(10)->get($url);

        if (! $response->successful()) {
            return [
                'success' => true,
                'message' => trans('core-media::media.message.file_url_cannot_upload_with_error', [
                    'url' => $url,
                    'error' => error_get_last(),
                ]),
            ];
        }

        $filePath = pathinfo($url);
        $mimetype = $response->header('Content-type');

        if (! $mimetype || ! in_array($mimetype, $this->getAllowedMimeTypes())) {
            return [];
        }

        $disk = $this->getDisk();
        $dirFolder = $folder ?: $this->folderService->getPath();
        $fileName = $dirFolder.'/'.$filePath['basename'];

        if ($disk->exists($fileName)) {
            $fileName = $dirFolder.'/'.time().'_'.$filePath['basename'];
        }

        $isFile = $disk->put($fileName, $response->body());

        if ($isFile) {
            return [
                'success' => true,
                'message' => trans('core-media::media.message.file_url_uploaded', ['url' => $url]),
                'data' => [
                    'name' => $filePath['filename'],
                    'alt' => $filePath['filename'],
                    'permalink' => $fileName,
                    'mine_type' => $mimetype,
                    'size' => strlen($response->body()),
                    'storage_driver' => $this->getCurrentDriver(),
                ],
            ];
        }

        return [
            'success' => true,
            'message' => trans('core-media::media.message.file_url_cannot_upload', ['url' => $url]),
        ];
    }

    /**
     * Get allowed MIME types from runtime config
     * Converts extension list to MIME types
     */
    protected function getAllowedMimeTypes(): array
    {
        $extensions = media_allowed_mime_types();

        // Extension to MIME type mapping
        $mimeMap = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'zip' => 'application/zip',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
        ];

        $mimes = [];
        foreach ($extensions as $ext) {
            $ext = strtolower(trim($ext));
            if (isset($mimeMap[$ext])) {
                $mimes[] = $mimeMap[$ext];
            }
        }

        // Also check config fallback for backwards compatibility
        if (empty($mimes)) {
            return config('file-manager.mime_types', []);
        }

        return array_unique($mimes);
    }

    /**
     * Format file size for display
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Check if chunk upload is enabled
     */
    public function isChunkUploadEnabled(): bool
    {
        return is_chunk_upload_enabled();
    }

    /**
     * Get chunk size for upload
     */
    public function getChunkSize(): int
    {
        return media_chunk_size();
    }
}
