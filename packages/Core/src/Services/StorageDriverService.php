<?php

namespace Packages\Core\Src\Services;

use Google\Service\Drive as Google_Service_Drive;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem as Flysystem;
use Masbug\Flysystem\GoogleDriveAdapter;

/**
 * StorageDriver Service
 *
 * Factory for creating storage disk instances based on configured driver.
 */
class StorageDriverService
{
    public function __construct(
        private GoogleDriveService $googleDriveService
    ) {}

    /**
     * Get storage disk based on driver
     *
     * @param  string|null  $driver  Driver name: 'local' or 'google'
     */
    public function getDisk(?string $driver = null): Filesystem
    {
        $driver = $driver ?? $this->getCurrentDriver();

        return match ($driver) {
            'google' => $this->getGoogleDriveDisk(),
            default => $this->getLocalDisk(),
        };
    }

    /**
     * Get local disk
     */
    public function getLocalDisk(): Filesystem
    {
        return Storage::build([
            'driver' => 'local',
            'root' => public_path(media_setting('default_upload_folder', 'uploads')),
        ]);
    }

    /**
     * Get Google Drive disk
     */
    public function getGoogleDriveDisk(): Filesystem
    {
        $client = $this->googleDriveService->getClient();

        if (! $client) {
            // Fallback to local if not connected
            \Log::warning('StorageDriverService: Google Drive not connected, falling back to local');

            return $this->getLocalDisk();
        }

        try {
            $service = new Google_Service_Drive($client);
            $folderId = $this->googleDriveService->getFolderId() ?: 'root';

            $adapter = new GoogleDriveAdapter($service, $folderId);
            $filesystem = new Flysystem($adapter);

            return new FilesystemAdapter($filesystem, $adapter);
        } catch (\Exception $e) {
            \Log::error('StorageDriverService: Failed to create Google Drive disk', [
                'error' => $e->getMessage(),
            ]);

            return $this->getLocalDisk();
        }
    }

    /**
     * Get current driver name from config
     */
    public function getCurrentDriver(): string
    {
        return config('file-manager.default_driver', 'local');
    }

    /**
     * Check if Google Drive is available and connected
     */
    public function isGoogleDriveAvailable(): bool
    {
        return $this->googleDriveService->isConnected();
    }

    /**
     * Get storage info for display
     */
    public function getStorageInfo(): array
    {
        return [
            'current_driver' => $this->getCurrentDriver(),
            'google_drive_connected' => $this->isGoogleDriveAvailable(),
            'google_drive_email' => $this->googleDriveService->getConnectedEmail(),
        ];
    }
}
