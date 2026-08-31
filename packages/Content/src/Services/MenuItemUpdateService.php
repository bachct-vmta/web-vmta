<?php

namespace Packages\Content\Src\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Packages\Content\Src\Models\MenuItem;

/**
 * Applies partial updates to a MenuItem, including per-locale label/url upsert/prune.
 * Used by both the AJAX inline edit and the legacy form-based update path.
 */
class MenuItemUpdateService
{
    public function __construct(private readonly MenuService $cache) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function apply(MenuItem $item, array $data): MenuItem
    {
        DB::transaction(function () use ($item, $data) {
            $scalar = Arr::only($data, [
                'parent_id',
                'link_type',
                'target_type',
                'target_id',
                'icon',
                'css_class',
                'open_new_tab',
                'sort_order',
                'is_active',
            ]);

            if ($scalar !== []) {
                $item->fill($scalar)->save();
            }

            if (isset($data['translations']) && is_array($data['translations'])) {
                $this->syncTranslations($item, $data['translations']);
            }

            $menu = $item->menu;
            if ($menu) {
                DB::afterCommit(fn () => $this->cache->forgetMenu($menu->location));
            }
        });

        $item->load('translations');

        return $item;
    }

    /**
     * @param  array<int, array{locale:string, label?:?string, url?:?string}>  $rows
     */
    private function syncTranslations(MenuItem $item, array $rows): void
    {
        foreach ($rows as $row) {
            $locale = $row['locale'] ?? null;
            if (! $locale) {
                continue;
            }

            $labelGiven = array_key_exists('label', $row);
            $urlGiven = array_key_exists('url', $row);
            $label = $labelGiven ? trim((string) ($row['label'] ?? '')) : null;
            $url = $urlGiven ? ($row['url'] === null ? null : trim((string) $row['url'])) : null;

            // Empty label means "remove this translation row" only when label was explicitly provided
            // (and the URL stays empty/absent). Avoids accidental wipes from partial PATCH payloads.
            if ($labelGiven && $label === '' && ($urlGiven ? ($url === '' || $url === null) : true)) {
                $existing = $item->translations()->where('locale', $locale)->first();

                if ($existing && $item->translations()->count() > 1) {
                    $existing->delete();
                }

                continue;
            }

            $translation = $item->translateOrNew($locale);
            if ($labelGiven && $label !== '') {
                $translation->label = $label;
            }
            if ($urlGiven) {
                $translation->url = $url === '' ? null : $url;
            }
            $translation->save();
        }
    }
}
