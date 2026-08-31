<?php

namespace Packages\Core\Src\Repositories\Interfaces;

/**
 * MediaFolder Repository Interface
 */
interface MediaFolderRepositoryInterface extends RepositoryInterface
{
    /**
     * Filter folders by criteria
     */
    public function filter(array $data);

    /**
     * Get count of folders matching criteria
     */
    public function getCount(array $data);

    /**
     * Get soft-deleted folders
     */
    public function getDelete();

    /**
     * Find a folder by ID including trashed rows
     */
    public function findWithTrashed($id);
}
