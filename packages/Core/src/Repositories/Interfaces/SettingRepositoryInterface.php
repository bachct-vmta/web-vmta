<?php

namespace Packages\Core\Src\Repositories\Interfaces;

use Illuminate\Support\Collection;

interface SettingRepositoryInterface
{
    /**
     * Get setting value by key
     */
    public function getValue(string $key, mixed $default = null): mixed;

    /**
     * Set setting value
     */
    public function setValue(string $key, mixed $value, string $group = 'general'): void;

    /**
     * Get all settings by group
     */
    public function getByGroup(string $group): array;

    /**
     * Get all settings cached
     */
    public function getAllCached(): array;

    /**
     * Clear settings cache
     */
    public function clearCache(): void;

    /**
     * Get masked value for display
     */
    public function getMasked(string $key, int $showChars = 4): string;

    /**
     * Get all settings as collection
     */
    public function getAllSettings(): Collection;

    /**
     * Delete setting by key
     */
    public function deleteByKey(string $key): bool;
}
