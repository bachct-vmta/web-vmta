<?php

namespace Packages\Core\Src\Repositories\Interfaces;

/**
 * GoogleDriveCredential Repository Interface
 */
interface GoogleDriveCredentialRepositoryInterface extends RepositoryInterface
{
    /**
     * Get the active Google Drive credential
     */
    public function getActive(): ?object;

    /**
     * Deactivate all credentials
     */
    public function deactivateAll(): int;
}
