<?php

namespace Packages\Core\Src\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends BaseModel
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'is_encrypted',
        'group',
        'description',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    /**
     * Cache key for full key→record map.
     */
    protected static string $cacheKey = 'app_settings_v2';

    /**
     * Cache TTL in seconds (1 hour)
     */
    protected static int $cacheTtl = 3600;

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    /**
     * Get typed setting value by key (decrypts when needed).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $records = static::getAllCached();

        if (! isset($records[$key])) {
            return $default;
        }

        $row = $records[$key];
        $raw = $row['value'];

        if ($raw === null) {
            return $default;
        }

        if (! empty($row['is_encrypted'])) {
            try {
                $raw = Crypt::decryptString($raw);
            } catch (\Throwable) {
                return $default;
            }
        }

        return static::castFromString($raw, $row['type'] ?? 'string');
    }

    /**
     * Set / upsert setting value. Type + group preserved when row exists.
     */
    public static function set(string $key, mixed $value, ?string $group = null, ?string $type = null, ?bool $encrypted = null): void
    {
        $existing = static::where('key', $key)->first();

        $effectiveType = $type ?? $existing?->type ?? static::inferType($value);
        $effectiveGroup = $group ?? $existing?->group ?? 'general';
        $effectiveEnc = $encrypted ?? (bool) ($existing?->is_encrypted ?? false);

        $stringValue = static::castToString($value, $effectiveType);
        if ($stringValue !== null && $effectiveEnc) {
            $stringValue = Crypt::encryptString($stringValue);
        }

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $stringValue,
                'type' => $effectiveType,
                'group' => $effectiveGroup,
                'is_encrypted' => $effectiveEnc,
            ],
        );
    }

    /**
     * Return all decoded values inside a group (decrypts encrypted entries).
     */
    public static function getByGroup(string $group): array
    {
        $records = static::getAllCached();
        $out = [];

        foreach ($records as $key => $row) {
            if (($row['group'] ?? 'general') !== $group) {
                continue;
            }
            $out[$key] = static::get($key);
        }

        return $out;
    }

    /**
     * Cache lookup: key → ['value', 'type', 'group', 'is_encrypted', 'description'].
     */
    public static function getAllCached(): array
    {
        return Cache::remember(static::$cacheKey, static::$cacheTtl, function () {
            return static::all()->keyBy('key')->map(fn ($s) => [
                'value' => $s->value,
                'type' => $s->type,
                'group' => $s->group,
                'is_encrypted' => (bool) $s->is_encrypted,
                'description' => $s->description,
            ])->toArray();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(static::$cacheKey);
    }

    /**
     * Mask helper for secret display in admin UI.
     */
    public static function getMasked(string $key, int $showChars = 4): string
    {
        $value = static::get($key);
        if ($value === null || $value === '') {
            return '';
        }
        $value = (string) $value;
        $length = strlen($value);
        if ($length <= $showChars * 2) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, $showChars).str_repeat('*', $length - $showChars * 2).substr($value, -$showChars);
    }

    protected static function castFromString(string $raw, string $type): mixed
    {
        return match ($type) {
            'int' => (int) $raw,
            'bool' => filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'json' => json_decode($raw, true),
            default => $raw,
        };
    }

    protected static function castToString(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'int' => (string) (int) $value,
            'bool' => $value ? '1' : '0',
            'json' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            default => (string) $value,
        };
    }

    protected static function inferType(mixed $value): string
    {
        if (is_bool($value)) {
            return 'bool';
        }
        if (is_int($value)) {
            return 'int';
        }
        if (is_array($value)) {
            return 'json';
        }

        return 'string';
    }
}
