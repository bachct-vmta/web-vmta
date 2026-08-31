<?php

use Packages\Core\Src\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Global accessor for Core Settings.
     *
     * Usage:
     *   setting('site.hotline_number')             // read with null fallback
     *   setting('site.hotline_number', '1900-xxx') // read with default
     *   setting(['key' => 'value', ...])           // bulk write
     */
    function setting(string|array|null $key = null, mixed $default = null): mixed
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                Setting::set($k, $v);
            }

            return null;
        }

        if ($key === null) {
            return Setting::getAllCached();
        }

        return Setting::get($key, $default);
    }
}
