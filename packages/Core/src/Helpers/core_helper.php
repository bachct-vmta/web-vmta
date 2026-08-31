<?php

/**
 * Core Helper Functions
 *
 * This file contains core helper functions that can be used across all packages.
 */
if (! function_exists('admin_prefix')) {
    /**
     * Get admin route prefix from config
     */
    function admin_prefix(): string
    {
        return config('core.admin_prefix', 'admin');
    }
}

if (! function_exists('admin_route_prefix')) {
    /**
     * Get admin route prefix with optional suffix
     * Useful for building route prefixes like 'admin/media' or 'admin/users'
     *
     * @param  string|null  $suffix  Optional suffix to append (e.g., 'media', 'users')
     */
    function admin_route_prefix(?string $suffix = null): string
    {
        $prefix = admin_prefix();

        if ($suffix) {
            return trim($prefix, '/').'/'.trim($suffix, '/');
        }

        return $prefix;
    }
}

if (! function_exists('admin_route_name')) {
    /**
     * Get admin route name prefix
     *
     * @param  string|null  $suffix  Optional suffix to append
     */
    function admin_route_name(?string $suffix = null): string
    {
        $prefix = config('core.admin_route_name', 'admin');

        if ($suffix) {
            return rtrim($prefix, '.').'.'.ltrim($suffix, '.');
        }

        return $prefix.'.';
    }
}
