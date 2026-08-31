<?php

namespace Packages\Core\Src\Chunks\Exceptions;

/**
 * ChunkSaveException
 *
 * Exception thrown when chunk saving fails.
 */
class ChunkSaveException extends \RuntimeException
{
    public static function cannotWriteChunk(string $path): self
    {
        return new self("Cannot write chunk to: {$path}");
    }

    public static function chunkNotFound(string $path): self
    {
        return new self("Chunk file not found: {$path}");
    }

    public static function mergeFailed(string $reason): self
    {
        return new self("Failed to merge chunks: {$reason}");
    }
}
