<?php

namespace Packages\Core\Src\Chunks\Save;

use Illuminate\Http\UploadedFile;
use Packages\Core\Src\Chunks\Exceptions\ChunkSaveException;
use Packages\Core\Src\Chunks\FileMerger;
use Packages\Core\Src\Chunks\Storage\ChunkStorage;

/**
 * ChunkSave
 *
 * Handles saving and merging chunk uploads.
 */
class ChunkSave
{
    protected UploadedFile $file;

    protected ChunkStorage $storage;

    protected string $chunkFileName;

    protected int $chunkIndex;

    protected int $totalChunks;

    protected bool $isLastChunk;

    protected ?UploadedFile $fullChunkFile = null;

    public function __construct(
        UploadedFile $file,
        ChunkStorage $storage,
        string $chunkFileName,
        int $chunkIndex,
        int $totalChunks
    ) {
        $this->file = $file;
        $this->storage = $storage;
        $this->chunkFileName = $chunkFileName;
        $this->chunkIndex = $chunkIndex;
        $this->totalChunks = $totalChunks;
        $this->isLastChunk = ($chunkIndex + 1) >= $totalChunks;
    }

    /**
     * Process the chunk upload
     */
    public function handle(): array
    {
        $this->storage->ensureDirectoryExists();

        // Save the current chunk
        $this->saveChunk();

        // If this is the last chunk, merge all chunks
        if ($this->isLastChunk) {
            return $this->mergeChunks();
        }

        return [
            'success' => true,
            'finished' => false,
            'chunk_index' => $this->chunkIndex,
            'total_chunks' => $this->totalChunks,
            'message' => 'Chunk uploaded successfully',
        ];
    }

    /**
     * Save the current chunk to storage
     */
    protected function saveChunk(): void
    {
        $chunkPath = $this->getChunkPath($this->chunkIndex);

        $saved = $this->storage->disk()->put(
            $chunkPath,
            file_get_contents($this->file->getPathname())
        );

        if (! $saved) {
            throw ChunkSaveException::cannotWriteChunk($chunkPath);
        }
    }

    /**
     * Merge all chunks into the final file
     */
    protected function mergeChunks(): array
    {
        $finalPath = $this->getFinalFilePath();
        $merger = new FileMerger($finalPath);

        try {
            // Append each chunk in order
            for ($i = 0; $i < $this->totalChunks; $i++) {
                $chunkPath = $this->storage->disk()->path($this->getChunkPath($i));

                if (! file_exists($chunkPath)) {
                    throw ChunkSaveException::chunkNotFound($chunkPath);
                }

                $merger->appendFile($chunkPath);
            }

            $merger->close();

            // Clean up chunks
            $this->cleanupChunks();

            // Create UploadedFile from merged file
            $this->fullChunkFile = new UploadedFile(
                $finalPath,
                $this->file->getClientOriginalName(),
                $this->file->getClientMimeType(),
                null,
                true
            );

            return [
                'success' => true,
                'finished' => true,
                'file' => $this->fullChunkFile,
                'path' => $finalPath,
                'message' => 'File uploaded successfully',
            ];
        } catch (\Exception $e) {
            $merger->close();
            throw ChunkSaveException::mergeFailed($e->getMessage());
        }
    }

    /**
     * Get the path for a specific chunk
     */
    protected function getChunkPath(int $index): string
    {
        return $this->storage->directory().$this->chunkFileName.'.part'.$index;
    }

    /**
     * Get the final merged file path
     */
    protected function getFinalFilePath(): string
    {
        return $this->storage->disk()->path(
            $this->storage->directory().$this->chunkFileName.'.merged'
        );
    }

    /**
     * Clean up chunk files after merge
     */
    protected function cleanupChunks(): void
    {
        for ($i = 0; $i < $this->totalChunks; $i++) {
            $chunkPath = $this->getChunkPath($i);
            if ($this->storage->disk()->exists($chunkPath)) {
                $this->storage->disk()->delete($chunkPath);
            }
        }
    }

    /**
     * Check if upload is finished
     */
    public function isFinished(): bool
    {
        return $this->isLastChunk && $this->fullChunkFile !== null;
    }

    /**
     * Get the merged file
     */
    public function getFile(): ?UploadedFile
    {
        return $this->fullChunkFile;
    }
}
