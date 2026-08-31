<?php

namespace Packages\Core\Src\Chunks;

use Packages\Core\Src\Chunks\Storage\ChunkStorage;

/**
 * ChunkFile
 *
 * Represents a single chunk file during chunked upload process.
 */
class ChunkFile
{
    protected string $path;

    protected int $modifiedTime;

    protected ChunkStorage $storage;

    public function __construct(string $path, int $modifiedTime, ChunkStorage $storage)
    {
        $this->path = $path;
        $this->modifiedTime = $modifiedTime;
        $this->storage = $storage;
    }

    /**
     * Get the absolute path of the chunk file
     */
    public function getAbsolutePath(): string
    {
        return $this->storage->disk()->path($this->path ?: '');
    }

    /**
     * Get the relative path of the chunk file
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get the modified time of the chunk file
     */
    public function getModifiedTime(): int
    {
        return $this->modifiedTime;
    }

    /**
     * Move the chunk file to a new path
     */
    public function move(string $pathTo): bool
    {
        return $this->storage->disk()->move($this->path, $pathTo);
    }

    /**
     * Delete the chunk file
     */
    public function delete(): bool
    {
        return $this->storage->disk()->delete($this->path);
    }

    /**
     * Check if the chunk file exists
     */
    public function exists(): bool
    {
        return $this->storage->disk()->exists($this->path);
    }

    /**
     * Get the size of the chunk file
     */
    public function size(): int
    {
        return $this->storage->disk()->size($this->path);
    }

    public function __toString(): string
    {
        return sprintf('ChunkFile %s uploaded at %s', $this->getPath(), date('Y-m-d H:i:s', $this->getModifiedTime()));
    }
}
