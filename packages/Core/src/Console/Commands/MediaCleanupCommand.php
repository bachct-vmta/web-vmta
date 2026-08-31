<?php

namespace Packages\Core\Src\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Packages\Core\Src\Repositories\Interfaces\MediaFileRepositoryInterface;
use Packages\Core\Src\Repositories\Interfaces\MediaFolderRepositoryInterface;

/**
 * Media Cleanup Command
 *
 * Permanently deletes expired trash items (files and folders).
 */
class MediaCleanupCommand extends Command
{
    protected $signature = 'media:cleanup';

    protected $description = 'Clean up expired trash items in media library';

    public function __construct(
        private MediaFileRepositoryInterface $fileRepository,
        private MediaFolderRepositoryInterface $folderRepository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $basePath = public_path(config('file-manager.path_folder'));

        $deletedFiles = $this->cleanupFiles($basePath);
        $deletedFolders = $this->cleanupFolders($basePath);

        $this->info("Cleaned up {$deletedFiles} files and {$deletedFolders} folders.");

        return self::SUCCESS;
    }

    private function cleanupFiles(string $basePath): int
    {
        $count = 0;
        $files = $this->fileRepository->getDelete();

        foreach ($files as $file) {
            if (Carbon::parse($file->deleted_at)->lt(Carbon::now())) {
                $filePath = $basePath.$file->permalink;
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }
                $file->forceDelete();
                $count++;
            }
        }

        return $count;
    }

    private function cleanupFolders(string $basePath): int
    {
        $count = 0;
        $folders = $this->folderRepository->getDelete();

        foreach ($folders as $folder) {
            if (Carbon::parse($folder->deleted_at)->lt(Carbon::now())) {
                $folderPath = $basePath.$folder->permalink;
                if (File::exists($folderPath)) {
                    File::deleteDirectory($folderPath);
                }
                $folder->forceDelete();
                $count++;
            }
        }

        return $count;
    }
}
