<?php

namespace Packages\Core\Src\Services;

use Imagick;
use ImagickException;

/**
 * MediaResize Service
 *
 * Handles image cropping and resizing operations using Imagick.
 * Supports multiple storage drivers via StorageDriverService.
 *
 * Note: Image cropping is only supported for local storage.
 * For cloud storage, images need to be downloaded, processed, then re-uploaded.
 */
class MediaResizeService
{
    protected StorageDriverService $storageDriver;

    public $image;

    public string $path;

    public int $width;

    public int $height;

    public int $x;

    public int $y;

    public function __construct(StorageDriverService $storageDriver)
    {
        $this->storageDriver = $storageDriver;
    }

    /**
     * Get storage disk instance
     *
     * @param  string|null  $driver  Optional driver override
     */
    protected function getDisk(?string $driver = null)
    {
        return $this->storageDriver->getDisk($driver);
    }

    /**
     * Get current storage driver name
     */
    public function getCurrentDriver(): string
    {
        return $this->storageDriver->getCurrentDriver();
    }

    /**
     * Set image data for cropping
     *
     * @throws ImagickException
     */
    public function setImageData(array $data): static
    {
        $this->path = $data['path'];
        $this->width = $data['width'];
        $this->height = $data['height'];
        $this->x = $data['x'];
        $this->y = $data['y'];

        $disk = $this->getDisk();
        $currentDriver = $this->getCurrentDriver();

        if ($currentDriver === 'local') {
            // For local storage, use file path directly
            $this->image = new Imagick($disk->path($data['path']));
        } else {
            // For cloud storage, download content first
            $content = $disk->get($data['path']);
            $this->image = new Imagick;
            $this->image->readImageBlob($content);
        }

        return $this;
    }

    /**
     * Perform the crop operation
     */
    public function resize(): array
    {
        $this->image->cropImage(
            $this->width,
            $this->height,
            $this->x,
            $this->y
        );

        return $this->saveImage($this->path, $this->image->getImageBlob());
    }

    /**
     * Save the cropped image
     */
    public function saveImage(string $path, string $data): array
    {
        if (! $data) {
            return [
                'success' => false,
                'message' => trans('core-media::media.message.file_crop_error'),
            ];
        }

        $disk = $this->getDisk();
        $isPut = $disk->put($path, $data);

        if ($isPut) {
            return [
                'success' => true,
                'data' => [
                    'size' => strlen($data), // Use blob size for cloud storage compatibility
                ],
            ];
        }

        return [
            'success' => false,
            'message' => trans('core-media::media.message.file_crop_error'),
        ];
    }
}
