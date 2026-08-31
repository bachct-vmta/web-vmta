<?php

namespace Packages\Core\Src\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Packages\Core\Src\Repositories\Interfaces\MediaFileRepositoryInterface;

/**
 * MediaFolder Service
 *
 * Handles folder creation and management operations.
 * Supports multiple storage drivers via StorageDriverService.
 */
class MediaFolderService
{
    protected MediaFileRepositoryInterface $fileRepository;

    protected StorageDriverService $storageDriver;

    public function __construct(
        MediaFileRepositoryInterface $fileRepository,
        StorageDriverService $storageDriver
    ) {
        $this->fileRepository = $fileRepository;
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
     * Get base path
     */
    public function getPath(): string
    {
        return '';
    }

    /**
     * Check if file exists at path
     */
    public function find($path): bool
    {
        return $this->getDisk()->exists($path);
    }

    /**
     * Check if directory exists
     */
    public function findDir($dirPath): bool
    {
        return $this->getDisk()->exists($dirPath);
    }

    /**
     * Generate slug for directory path
     */
    protected function setDirPath($name): string
    {
        return Str::slug($name, '-');
    }

    /**
     * Rename a folder and move its contents
     */
    public function renameItem($item, $newName, $parentPath): array
    {
        $dirPath = $item->permalink;
        $disk = $this->getDisk();

        $createdNew = $this->createDir($newName, $parentPath);

        if (! $createdNew['success']) {
            return $createdNew;
        }

        // Move all files to new folder
        $allFiles = $disk->files($dirPath);

        foreach ($allFiles as $file) {
            $disk->move($file, $createdNew['path'].'/'.basename($file));
            $this->fileRepository->updateFileByPermalink($file, [
                'permalink' => '/'.$createdNew['path'].'/'.basename($file),
            ]);
        }

        // Delete old directory
        $removeDir = $disk->deleteDirectory($dirPath);

        if ($removeDir) {
            return $createdNew;
        }

        return [
            'success' => false,
            'message' => trans('core-media::media.message.folder_not_renamed', ['name' => $item->name]),
        ];
    }

    /**
     * Create a new directory
     */
    public function createDir($name, $path = null): array
    {
        $disk = $this->getDisk();
        $dirPath = $this->setDirPath($name);

        if ($path) {
            $dirPath = $path.'/'.Str::slug($name, '-');
        }

        if ($disk->exists($dirPath)) {
            return [
                'success' => false,
                'message' => trans('core-media::media.message.folder_already_exists', ['name' => $name]),
            ];
        }

        $createDirResult = $disk->makeDirectory($dirPath);

        if ($createDirResult) {
            // Only set permissions for local storage
            if ($this->getCurrentDriver() === 'local') {
                $fullPath = config('file-manager.path_folder').'/'.$dirPath;
                $permission = config('file-manager.permission', 0755);

                if (file_exists(public_path($fullPath))) {
                    File::chmod(public_path($fullPath), $permission);
                }
            }

            return [
                'success' => true,
                'message' => trans('core-media::media.message.folder_created', ['name' => $name]),
                'path' => $dirPath,
            ];
        }

        return [
            'success' => false,
            'message' => trans('core-media::media.message.folder_not_created', ['name' => $name]),
        ];
    }
}
