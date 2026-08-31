<?php

namespace Packages\Core\Src\Enums;

/**
 * Storage driver types for media files.
 */
enum StorageDriver: string
{
    case Local = 'local';
    case Google = 'google';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Local => 'Local Storage',
            self::Google => 'Google Drive',
        };
    }
}
