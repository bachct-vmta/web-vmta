<?php

namespace Packages\Content\Src\Http\Controllers\Public;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Packages\Content\Src\Models\Page;
use Packages\Content\Src\Models\Post;

/**
 * Serves /sitemap.xml — single multi-locale sitemap with hreflang alternates per URL.
 * Output is cached for an hour to avoid hitting the DB on every crawler request.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $locales = array_keys(config('laravellocalization.supportedLocales', ['vi' => [], 'en' => []]));

        $xml = Cache::remember(
            'content:sitemap:'.implode(',', $locales),
            (int) config('content.sitemap_ttl', 3600),
            fn () => view('content::sitemap.index', [
                'urls' => $this->buildUrls($locales),
            ])->render(),
        );

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * @param  array<int, string>  $locales
     * @return array<int, array{loc: string, lastmod: ?string, alternates: array<string, string>}>
     */
    private function buildUrls(array $locales): array
    {
        $urls = [];

        // Home + News list per locale. `<lastmod>` intentionally omitted: emitting now() would
        // falsely claim the URL changed on every cache rebuild, training crawlers to revisit
        // unchanged pages too often.
        foreach ($locales as $locale) {
            $urls[] = [
                'loc' => url("/{$locale}"),
                'lastmod' => null,
                'alternates' => $this->buildAlternates($locales, fn ($l) => "/{$l}"),
            ];
            $urls[] = [
                'loc' => url("/{$locale}/tin-tuc"),
                'lastmod' => null,
                'alternates' => $this->buildAlternates($locales, fn ($l) => "/{$l}/tin-tuc"),
            ];
        }

        // Published pages (one row per translation locale).
        Page::with('translations')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->chunk(200, function ($pages) use (&$urls, $locales) {
                foreach ($pages as $page) {
                    foreach ($page->translations as $tr) {
                        if (! in_array($tr->locale, $locales, true)) {
                            continue;
                        }
                        $urls[] = [
                            'loc' => url("/{$tr->locale}/{$tr->slug}"),
                            'lastmod' => ($tr->updated_at ?? $page->updated_at)->toAtomString(),
                            'alternates' => $this->buildPageAlternates($page, $locales),
                        ];
                    }
                }
            });

        // Published posts (one row per translation locale).
        Post::with('translations')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->chunk(200, function ($posts) use (&$urls, $locales) {
                foreach ($posts as $post) {
                    foreach ($post->translations as $tr) {
                        if (! in_array($tr->locale, $locales, true)) {
                            continue;
                        }
                        $urls[] = [
                            'loc' => url("/{$tr->locale}/tin-tuc/{$tr->slug}"),
                            'lastmod' => ($tr->updated_at ?? $post->updated_at)->toAtomString(),
                            'alternates' => $this->buildPostAlternates($post, $locales),
                        ];
                    }
                }
            });

        return $urls;
    }

    /**
     * @param  array<int, string>  $locales
     * @param  callable(string): string  $pathBuilder
     * @return array<string, string>
     */
    private function buildAlternates(array $locales, callable $pathBuilder): array
    {
        $alt = [];
        foreach ($locales as $l) {
            $alt[$l] = url($pathBuilder($l));
        }

        return $alt;
    }

    /**
     * @param  array<int, string>  $locales
     * @return array<string, string>
     */
    private function buildPageAlternates(Page $page, array $locales): array
    {
        $alt = [];
        foreach ($page->translations as $tr) {
            if (in_array($tr->locale, $locales, true)) {
                $alt[$tr->locale] = url("/{$tr->locale}/{$tr->slug}");
            }
        }

        return $alt;
    }

    /**
     * @param  array<int, string>  $locales
     * @return array<string, string>
     */
    private function buildPostAlternates(Post $post, array $locales): array
    {
        $alt = [];
        foreach ($post->translations as $tr) {
            if (in_array($tr->locale, $locales, true)) {
                $alt[$tr->locale] = url("/{$tr->locale}/tin-tuc/{$tr->slug}");
            }
        }

        return $alt;
    }
}
