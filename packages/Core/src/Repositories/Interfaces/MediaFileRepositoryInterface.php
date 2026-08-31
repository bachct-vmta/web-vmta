<?php

namespace Packages\Core\Src\Repositories\Interfaces;

/**
 * MediaFile Repository Interface
 */
interface MediaFileRepositoryInterface extends RepositoryInterface
{
    /**
     * Filter files by criteria
     */
    public function filter(array $data);

    /**
     * Find files by folder ID
     */
    public function findByFolderId($folderId);

    /**
     * Update file by permalink
     */
    public function updateFileByPermalink($permalink, array $data);

    /**
     * Update file data from upload request
     */
    public function updateFile(array $data, $request): array;

    /**
     * Update file size after cropping
     */
    public function updateSize($id, array $size): array;

    /**
     * Get count of files matching criteria
     */
    public function getCount(array $data);

    /**
     * Get soft-deleted files
     */
    public function getDelete();

    /**
     * Find a file by ID including trashed rows
     */
    public function findWithTrashed($id);
}
