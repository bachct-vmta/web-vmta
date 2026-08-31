<?php

namespace Packages\Core\Src\Repositories\Eloquent;

use Packages\Core\Src\Models\MediaSetting;
use Packages\Core\Src\Repositories\Interfaces\MediaSettingRepositoryInterface;

/**
 * MediaSetting Repository
 */
class MediaSettingRepository extends BaseRepository implements MediaSettingRepositoryInterface
{
    public function getModel(): string
    {
        return MediaSetting::class;
    }

    /**
     * Get setting by key
     */
    public function getByKey(string $key)
    {
        return $this->model->where('key', $key)->first();
    }

    /**
     * Get count of settings
     */
    public function getCount(array $data)
    {
        return $this->model->count();
    }

    /**
     * Set or update a setting value
     */
    public function setValue(string $key, $value): MediaSetting
    {
        return $this->model->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
