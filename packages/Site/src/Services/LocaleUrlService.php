<?php

namespace Packages\Site\Src\Services;

use Illuminate\Support\Facades\Route as RouteFacade;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

/**
 * Resolves the URL for the current page in a target locale, honoring per-locale
 * URL slug maps (e.g. /vi/tin-tuc ↔ /en/news) and DB-translated post slugs.
 *
 * Used by the language switcher and hreflang alternates so they generate the
 * correct localized URL instead of just swapping the locale prefix.
 */
class LocaleUrlService
{
    /**
     * Return the URL for the current request in the given target locale.
     * Falls back to LaravelLocalization::getLocalizedURL() when the route
     * cannot be resolved (e.g. no name, no per-locale registration).
     */
    public function urlFor(string $targetLocale): string
    {
        $currentRoute = RouteFacade::current();
        $name = $currentRoute?->getName();

        if (! $name) {
            return $this->fallback($targetLocale);
        }

        $currentLocale = app()->getLocale();
        $targetName = $this->swapLocaleSegment($name, $currentLocale, $targetLocale);

        if ($targetName === null || ! RouteFacade::has($targetName)) {
            return $this->fallback($targetLocale);
        }

        $params = $this->collectParams($currentRoute);

        // Post detail needs DB lookup to translate the slug to the target locale's value.
        if (str_ends_with($name, 'content.posts.show')) {
            $translated = $this->translatePostSlug($params['slug'] ?? null, $currentLocale, $targetLocale);
            if ($translated === null) {
                return $this->fallback($targetLocale);
            }
            $params['slug'] = $translated;
        }

        try {
            return route($targetName, $params);
        } catch (\Throwable) {
            return $this->fallback($targetLocale);
        }
    }

    /**
     * Swap the locale segment inside per-locale route names like
     * `site.vi.content.posts.show` → `site.en.content.posts.show`.
     * Returns null if the name doesn't follow the per-locale convention.
     */
    private function swapLocaleSegment(string $name, string $from, string $to): ?string
    {
        $supportedLocales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));
        $localeAlt = implode('|', array_map('preg_quote', $supportedLocales));

        $pattern = '/^([^.]+)\.('.$localeAlt.')\./';
        if (! preg_match($pattern, $name, $m)) {
            return null;
        }

        return preg_replace($pattern, $m[1].'.'.$to.'.', $name, 1);
    }

    /**
     * Extract scalar route params (drop model instances → keep route keys).
     *
     * @return array<string, mixed>
     */
    private function collectParams(\Illuminate\Routing\Route $route): array
    {
        $params = [];
        foreach ($route->parameters() as $key => $value) {
            if (is_scalar($value)) {
                $params[$key] = $value;
            } elseif (is_object($value) && method_exists($value, 'getRouteKey')) {
                $params[$key] = $value->getRouteKey();
            }
        }
        return $params;
    }

    /**
     * Translate a post slug from $fromLocale to $toLocale via DB lookup.
     * Returns null when no matching post or no target translation exists.
     */
    private function translatePostSlug(?string $slug, string $fromLocale, string $toLocale): ?string
    {
        if (! is_string($slug) || $slug === '') {
            return null;
        }

        if (! interface_exists('Packages\\Content\\Src\\Repositories\\Interfaces\\PostRepositoryInterface')) {
            return null;
        }

        try {
            $repo = app('Packages\\Content\\Src\\Repositories\\Interfaces\\PostRepositoryInterface');
            $post = $repo->findPublishedBySlug($slug, $fromLocale);
            if ($post === null) {
                return null;
            }
            $targetTr = $post->translations->firstWhere('locale', $toLocale);
            return $targetTr?->slug ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function fallback(string $targetLocale): string
    {
        return LaravelLocalization::getLocalizedURL($targetLocale) ?: url('/'.$targetLocale);
    }
}
