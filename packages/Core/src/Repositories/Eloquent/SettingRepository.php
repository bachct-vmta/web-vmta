<?php

namespace Packages\Core\Src\Repositories\Eloquent;

use Illuminate\Support\Collection;
use Packages\Core\Src\Models\Setting;
use Packages\Core\Src\Repositories\Interfaces\SettingRepositoryInterface;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function getModel(): string
    {
        return Setting::class;
    }

    public function getValue(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }

    public function setValue(string $key, mixed $value, string $group = 'general'): void
    {
        Setting::set($key, $value, $group);
    }

    public function getByGroup(string $group): array
    {
        return Setting::getByGroup($group);
    }

    public function getAllCached(): array
    {
        return Setting::getAllCached();
    }

    public function clearCache(): void
    {
        Setting::clearCache();
    }

    public function getMasked(string $key, int $showChars = 4): string
    {
        return Setting::getMasked($key, $showChars);
    }

    public function getAllSettings(): Collection
    {
        return $this->model->orderBy('group')->orderBy('key')->get();
    }

    public function deleteByKey(string $key): bool
    {
        return $this->model->where('key', $key)->delete() > 0;
    }
}
