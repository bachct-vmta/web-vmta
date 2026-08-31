<?php

namespace Packages\Core\Src\Repositories\Interfaces;

/**
 * MediaSetting Repository Interface
 */
interface MediaSettingRepositoryInterface extends RepositoryInterface
{
    /**
     * Get setting by key
     */
    public function getByKey(string $key);

    /**
     * Get count of settings
     */
    public function getCount(array $data);
}
