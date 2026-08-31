<?php

namespace Packages\Core\Src\Chunks\Storage;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Packages\Core\Src\Chunks\ChunkFile;

/**
 * ChunkStorage
 *
 * Manages the storage location and operations for chunk files.
 */
class ChunkStorage
{
    protected FilesystemAdapter $disk;

    protected string $directory;

    public function __construct(?string $diskName = null, ?string $directory = null)
    {
        $this->disk = Storage::disk($diskName ?? 'local');
        $this->directory = $directory ?? 'chunks';
    }

    /**
     * Get the storage disk
     */
    public function disk(): FilesystemAdapter
    {
        return $this->disk;
    }

    /**
     * Get the chunks directory
     */
    public function directory(): string
    {
        return $this->directory.'/';
    }

    /**
     * Get the disk path prefix
     */
    public function getDiskPathPrefix(): string
    {
        return $this->disk->path('');
    }

    /**
     * Get all chunk files in storage
     */
    public function getChunkFiles(): array
    {
        $files = [];
        $storedFiles = $this->disk->files($this->directory);

        foreach ($storedFiles as $file) {
            $files[] = new ChunkFile(
                $file,
                $this->disk->lastModified($file),
                $this
            );
        }

        return $files;
    }

    /**
     * Delete chunks older than specified hours
     */
    public function deleteChunksOlderThan(int $hours = 3): int
    {
        $deleted = 0;
        $threshold = time() - ($hours * 3600);

        foreach ($this->getChunkFiles() as $chunk) {
            if ($chunk->getModifiedTime() < $threshold) {
                $chunk->delete();
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Create chunks directory if not exists
     */
    public function ensureDirectoryExists(): void
    {
        if (! $this->disk->exists($this->directory)) {
            $this->disk->makeDirectory($this->directory);
        }
    }

    /**
     * Get chunk file path
     */
    public function getChunkPath(string $filename): string
    {
        return $this->directory().$filename;
    }

    /**
     * Check if chunk exists
     */
    public function chunkExists(string $filename): bool
    {
        return $this->disk->exists($this->getChunkPath($filename));
    }
}
