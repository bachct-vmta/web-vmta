<?php

namespace Packages\Core\Src\Models;

use Illuminate\Support\Facades\Cache;

/**
 * MediaSetting Model
 *
 * Stores media-related settings as key-value pairs with caching support.
 */
class MediaSetting extends BaseModel
{
    protected $table = 'media_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    protected array $searchable = ['key'];

    /**
     * Cache key prefix for media settings
     */
    protected static string $cachePrefix = 'media_setting_';

    /**
     * Cache TTL in seconds (1 hour)
     */
    protected static int $cacheTtl = 3600;

    /**
     * Default media settings
     */
    public static array $defaults = [
        'media_max_file_size' => 10485760, // 10MB in bytes
        'media_allowed_mime_types' => 'jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,mp4,mp3,zip',
        'media_chunk_enabled' => false,
        'media_chunk_size' => 1048576, // 1MB in bytes
        'media_document_preview_enabled' => true,
        'media_document_preview_provider' => 'microsoft', // 'google' or 'microsoft'
        'media_default_upload_folder' => 'uploads',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Clear specific cache when setting changes
        static::saved(function ($model) {
            static::forgetCache($model->key);
        });

        static::deleted(function ($model) {
            static::forgetCache($model->key);
        });
    }

    /**
     * Get a setting value by key with caching.
     */
    public static function getValue(string $key, $default = null): mixed
    {
        $cacheKey = static::$cachePrefix.$key;

        return Cache::remember($cacheKey, static::$cacheTtl, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if ($setting) {
                return $setting->value;
            }

            // Return default from static defaults array
            return static::$defaults[$key] ?? $default;
        });
    }

    /**
     * Set a setting value by key.
     */
    public static function setValue(string $key, $value): self
    {
        static::forgetCache($key);

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get multiple settings by keys.
     */
    public static function getValues(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = static::getValue($key);
        }

        return $result;
    }

    /**
     * Set multiple settings at once.
     */
    public static function setValues(array $data): void
    {
        foreach ($data as $key => $value) {
            static::setValue($key, $value);
        }
    }

    /**
     * Forget cache for a specific key.
     */
    public static function forgetCache(string $key): void
    {
        Cache::forget(static::$cachePrefix.$key);
    }

    /**
     * Clear all media settings cache.
     */
    public static function clearAllCache(): void
    {
        foreach (array_keys(static::$defaults) as $key) {
            static::forgetCache($key);
        }
    }

    /**
     * Get all settings with defaults merged.
     */
    public static function getAllWithDefaults(): array
    {
        $stored = static::pluck('value', 'key')->toArray();

        return array_merge(static::$defaults, $stored);
    }
}
