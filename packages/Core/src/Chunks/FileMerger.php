<?php

namespace Packages\Core\Src\Chunks;

/**
 * FileMerger
 *
 * Merges multiple chunk files into a single file.
 */
class FileMerger
{
    protected $targetFile;

    protected string $targetPath;

    public function __construct(string $targetPath)
    {
        $this->targetPath = $targetPath;
        $this->targetFile = fopen($targetPath, 'ab');
    }

    /**
     * Append content from a file to the target
     */
    public function appendFile(string $sourcePath): self
    {
        if (! file_exists($sourcePath)) {
            throw new \RuntimeException("Source file not found: {$sourcePath}");
        }

        $sourceFile = fopen($sourcePath, 'rb');

        while (! feof($sourceFile)) {
            $chunk = fread($sourceFile, 1024 * 1024); // Read 1MB at a time
            fwrite($this->targetFile, $chunk);
        }

        fclose($sourceFile);

        return $this;
    }

    /**
     * Append raw content to the target
     */
    public function appendContent(string $content): self
    {
        fwrite($this->targetFile, $content);

        return $this;
    }

    /**
     * Close the file handle
     */
    public function close(): void
    {
        if ($this->targetFile) {
            fclose($this->targetFile);
            $this->targetFile = null;
        }
    }

    /**
     * Get the target file path
     */
    public function getTargetPath(): string
    {
        return $this->targetPath;
    }

    /**
     * Get the current file size
     */
    public function getSize(): int
    {
        return file_exists($this->targetPath) ? filesize($this->targetPath) : 0;
    }

    public function __destruct()
    {
        $this->close();
    }
}
