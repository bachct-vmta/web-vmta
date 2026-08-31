<?php

use Packages\Core\Src\Models\MediaFile;
use Packages\Core\Src\Models\MediaSetting;

if (! function_exists('file_manager_setting')) {
    /**
     * Get file manager setting by key
     *
     * @deprecated Use media_setting() instead
     */
    function file_manager_setting(string $setting = '', $default = 'local')
    {
        return media_setting($setting, $default);
    }
}

if (! function_exists('media_permalink_url')) {
    /**
     * Build a public URL from a media permalink.
     * Absolute permalinks (Google Drive etc.) pass through unchanged.
     * Relative permalinks resolve against MEDIA_URL when set, otherwise APP_URL.
     */
    function media_permalink_url(string $permalink): string
    {
        if (preg_match('#^https?://#i', $permalink)) {
            return $permalink;
        }

        $base = rtrim((string) config('file-manager.base_path', '/uploads'), '/');
        $path = $base.'/'.ltrim($permalink, '/');
        $mediaBase = rtrim((string) config('file-manager.media_url', ''), '/');

        return $mediaBase ? $mediaBase.$path : url($path);
    }
}

if (! function_exists('media_url')) {
    /**
     * Resolve a MediaFile id to a public URL. Returns $default when missing.
     * Result is request-cached to avoid duplicate lookups.
     */
    function media_url(?int $id, ?string $default = null): ?string
    {
        static $cache = [];
        if (! $id) {
            return $default;
        }
        if (array_key_exists($id, $cache)) {
            return $cache[$id] ?? $default;
        }

        try {
            $media = MediaFile::find($id);
            $permalink = $media?->permalink;
            if (! $permalink) {
                $cache[$id] = null;

                return $default;
            }

            return $cache[$id] = media_permalink_url($permalink);
        } catch (\Throwable) {
            return $default;
        }
    }
}

if (! function_exists('media_setting')) {
    /**
     * Get media setting value with fallback to config then default
     *
     * @param  string  $key  Setting key (with or without 'media_' prefix)
     * @param  mixed  $default  Default value if not found
     */
    function media_setting(string $key, mixed $default = null): mixed
    {
        // Normalize key - ensure it has 'media_' prefix
        $normalizedKey = str_starts_with($key, 'media_') ? $key : "media_{$key}";

        try {
            // First try database (cached)
            $value = MediaSetting::getValue($normalizedKey);

            if ($value !== null) {
                return $value;
            }

            // Fallback to config file
            $configKey = str_replace('media_', '', $normalizedKey);
            $configValue = config("file-manager.{$configKey}");

            if ($configValue !== null) {
                return $configValue;
            }

            return $default;
        } catch (Exception $e) {
            // If database not available, try config
            $configKey = str_replace('media_', '', $normalizedKey);

            return config("file-manager.{$configKey}", $default);
        }
    }
}

if (! function_exists('set_media_setting')) {
    /**
     * Set media setting value
     *
     * @param  string  $key  Setting key
     * @param  mixed  $value  Value to set
     */
    function set_media_setting(string $key, mixed $value): void
    {
        $normalizedKey = str_starts_with($key, 'media_') ? $key : "media_{$key}";
        MediaSetting::setValue($normalizedKey, $value);
    }
}

if (! function_exists('media_max_file_size')) {
    /**
     * Get max file size in bytes
     */
    function media_max_file_size(): int
    {
        return (int) media_setting('max_file_size', 10485760);
    }
}

if (! function_exists('media_allowed_mime_types')) {
    /**
     * Get allowed mime types as array
     */
    function media_allowed_mime_types(): array
    {
        $types = media_setting('allowed_mime_types', 'jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,mp4,mp3,zip');

        if (is_string($types)) {
            return array_map('trim', explode(',', $types));
        }

        return (array) $types;
    }
}

if (! function_exists('is_chunk_upload_enabled')) {
    /**
     * Check if chunk upload is enabled
     */
    function is_chunk_upload_enabled(): bool
    {
        return (bool) media_setting('chunk_enabled', false);
    }
}

if (! function_exists('media_chunk_size')) {
    /**
     * Get chunk size in bytes
     */
    function media_chunk_size(): int
    {
        return (int) media_setting('chunk_size', 1048576);
    }
}

if (! function_exists('is_document_preview_enabled')) {
    /**
     * Check if document preview is enabled
     */
    function is_document_preview_enabled(): bool
    {
        return (bool) media_setting('document_preview_enabled', true);
    }
}

if (! function_exists('document_preview_provider')) {
    /**
     * Get document preview provider (google or microsoft)
     */
    function document_preview_provider(): string
    {
        return (string) media_setting('document_preview_provider', 'microsoft');
    }
}
