<?php

namespace Packages\Core\Src\Services;

use Packages\Core\Src\Events\SettingChanged;
use Packages\Core\Src\Models\Setting;
use Packages\Core\Src\Repositories\Interfaces\SettingRepositoryInterface;

class SettingService
{
    public function __construct(private readonly SettingRepositoryInterface $repository) {}

    /**
     * Groups owned by other admin pages and excluded from /admin/settings.
     * Chatbot has its own UI at /admin/chatbot/settings.
     */
    protected array $hiddenGroups = ['chatbot'];

    /**
     * Group records by their `group` column for admin tabbed UI.
     * Returns Collection<string, Collection<int, Setting>> so view can use $setting->key.
     */
    public function allGrouped(): \Illuminate\Support\Collection
    {
        return $this->repository->getAllSettings()
            ->reject(fn (Setting $s) => in_array($s->group, $this->hiddenGroups, true))
            ->sortBy('key')
            ->groupBy(fn (Setting $s) => $s->group ?: 'general')
            ->map(fn ($items) => $items->values());
    }

    /**
     * Bulk update from admin form. $input = ['site.hotline_number' => '1900-xxx', ...].
     * Empty string for non-encrypted = clear; encrypted empty = keep current (so admin
     * doesn't have to retype secrets just to edit a sibling key).
     */
    public function updateMany(array $input): void
    {
        $existing = Setting::all()->keyBy('key');

        foreach ($input as $key => $value) {
            $row = $existing->get($key);
            if (! $row) {
                continue;
            }

            if ($row->is_encrypted && ($value === null || $value === '')) {
                continue;
            }

            $oldValue = Setting::get($key);
            Setting::set($key, $value);
            event(new SettingChanged($key, $value, $oldValue));
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}
