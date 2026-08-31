<?php

namespace Packages\Core\Src\Repositories\Eloquent;

use Packages\Core\Src\Models\GoogleDriveCredential;
use Packages\Core\Src\Repositories\Interfaces\GoogleDriveCredentialRepositoryInterface;

/**
 * GoogleDriveCredential Repository
 */
class GoogleDriveCredentialRepository extends BaseRepository implements GoogleDriveCredentialRepositoryInterface
{
    public function getModel(): string
    {
        return GoogleDriveCredential::class;
    }

    /**
     * Get the active Google Drive credential
     */
    public function getActive(): ?GoogleDriveCredential
    {
        return $this->model->where('is_active', true)->first();
    }

    /**
     * Deactivate all credentials
     */
    public function deactivateAll(): int
    {
        return $this->model->where('is_active', true)->update(['is_active' => false]);
    }
}
