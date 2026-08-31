<?php

namespace Packages\Catalog\Src\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Packages\Catalog\Src\Services\WpSpecialtyScraper\BrowserSnapshotRunner;
use Packages\Catalog\Src\Services\WpSpecialtyScraper\LlmExtractor;
use Packages\Catalog\Src\Services\WpSpecialtyScraper\MediaImporter;
use Packages\Catalog\Src\Services\WpSpecialtyScraper\SpecialtyImporter;

/**
 * Crawl a WordPress specialty landing page (VI + EN), LLM-extract the structured
 * content, import images into media_files, and upsert Specialty + 2 translations.
 *
 * Pipeline:
 *   for each locale (vi, en):
 *     snapshot URL via agent-browser
 *     LLM-extract structured shape (claude --print + JSON schema)
 *     download images into storage/app/public/specialties/{vi-slug}/...
 *     swap image_url → image_media_id inside repeater JSON
 *   resolve shared media (hero/intro/icon — VI takes precedence)
 *   transactional upsert (or dry-run dump)
 *
 * POC: 1 specialty pair (nha-khoa + dentistry). Batch all-12 left for future plan.
 */
class CrawlWpSpecialtyCommand extends Command
{
    protected $signature = 'specialty:crawl-wp
        {--vi-url= : URL trang VI nguồn (vd https://vmta.test/nha-khoa/)}
        {--en-url= : URL trang EN nguồn (vd https://vmta.test/en/dentistry/)}
        {--vi-slug= : Slug VI để lưu DB (vd nha-khoa)}
        {--en-slug= : Slug EN để lưu DB (vd dentistry)}
        {--dry-run : Parse + log, không touch DB/file}
        {--reuse-snapshot : Skip agent-browser, reuse cache /tmp/wp-snapshot-*.json}
    ';

    protected $description = 'Crawl 1 WP specialty landing page (VI + EN) → DB + media';

    public function __construct(
        private readonly BrowserSnapshotRunner $snapshotRunner,
        private readonly LlmExtractor $llmExtractor,
        private readonly MediaImporter $mediaImporter,
        private readonly SpecialtyImporter $specialtyImporter,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $viUrl = (string) $this->option('vi-url');
        $enUrl = (string) $this->option('en-url');
        $viSlug = (string) $this->option('vi-slug');
        $enSlug = (string) $this->option('en-slug');
        $dryRun = (bool) $this->option('dry-run');
        $reuseSnapshot = (bool) $this->option('reuse-snapshot');

        if (! $viUrl || ! $enUrl || ! $viSlug || ! $enSlug) {
            $this->error('Required: --vi-url, --en-url, --vi-slug, --en-slug');

            return self::INVALID;
        }

        $this->info("Crawling {$viSlug} ({$viUrl}) + {$enSlug} ({$enUrl})");
        $this->line('Dry-run: '.($dryRun ? 'YES' : 'NO').'  |  Reuse snapshot: '.($reuseSnapshot ? 'YES' : 'NO'));

        if (! $this->prereqCheck($viUrl, $enUrl)) {
            return self::FAILURE;
        }

        $results = [];
        foreach (['vi' => $viUrl, 'en' => $enUrl] as $locale => $url) {
            $this->info("[{$locale}] snapshot + extract...");
            $snapshot = $this->snapshotRunner->snapshot($url, "/tmp/wp-snapshot-{$viSlug}-{$locale}.json", $reuseSnapshot);
            $extracted = $this->llmExtractor->extract($snapshot, $url, $locale);
            $this->info("[{$locale}] extracted: ".count($extracted['strengths'] ?? []).' strengths, '.count($extracted['hospitals'] ?? []).' hospitals');
            $results[$locale] = $extracted;
        }

        $sharedMedia = $this->importAllMedia($results, $viSlug, $dryRun);

        if ($dryRun) {
            $this->warn('--dry-run mode: not writing DB');
            $this->line(json_encode([
                'vi' => $results['vi'],
                'en' => $results['en'],
                'shared_media' => $sharedMedia,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return self::SUCCESS;
        }

        $specialty = $this->specialtyImporter->import(
            $viSlug,
            $enSlug,
            $results['vi'],
            $results['en'],
            $sharedMedia,
        );

        $this->info("✓ Specialty #{$specialty->id} imported");
        $this->line("  hero_media_id: {$specialty->hero_media_id}");
        $this->line("  intro_image_media_id: {$specialty->intro_image_media_id}");
        $this->line("  icon: {$specialty->icon}");

        return self::SUCCESS;
    }

    private function prereqCheck(string $viUrl, string $enUrl): bool
    {
        foreach ([$viUrl, $enUrl] as $u) {
            $host = parse_url($u, PHP_URL_SCHEME).'://'.parse_url($u, PHP_URL_HOST);
            try {
                $resp = Http::timeout(10)->withOptions(['verify' => false])->head($u);
                if (! $resp->successful()) {
                    $this->error("Source URL {$u} returned status {$resp->status()}");

                    return false;
                }
            } catch (\Throwable $e) {
                $this->error("Source URL {$u} unreachable: {$e->getMessage()}");

                return false;
            }
        }

        return true;
    }

    /**
     * Download every image URL inside extracted shape (per locale) + replace
     * image_url with image_media_id inside repeater rows. Shared media (hero,
     * intro, icon) returned for caller to attach to Specialty model.
     *
     * VI locale takes precedence for shared media (proper-noun convention).
     */
    private function importAllMedia(array &$results, string $viSlug, bool $dryRun): array
    {
        $shared = [];

        foreach (['vi', 'en'] as $loc) {
            $extracted = &$results[$loc];

            if ($loc === 'vi') {
                $shared = array_merge($shared, $this->importSingleMedia($extracted, $viSlug, $dryRun));
            }

            foreach (['strengths', 'hospitals'] as $repeaterKey) {
                if (empty($extracted[$repeaterKey]) || ! is_array($extracted[$repeaterKey])) {
                    continue;
                }
                foreach ($extracted[$repeaterKey] as $i => &$item) {
                    if (! empty($item['image_url'])) {
                        $kind = $loc === 'vi' ? $repeaterKey : "{$repeaterKey}-en";
                        $path = $this->mediaImporter->buildPath($viSlug, $kind, $i + 1, $item['image_url']);
                        $media = $dryRun ? null : $this->mediaImporter->importFromUrl($item['image_url'], $path);
                        if ($media) {
                            $item['image_media_id'] = $media->id;
                        }
                        unset($item['image_url']);
                    }
                }
                unset($item);
            }
            unset($extracted);
        }

        return $shared;
    }

    private function importSingleMedia(array $extracted, string $viSlug, bool $dryRun): array
    {
        $out = [];

        if (! empty($extracted['hero_image_url'])) {
            $path = $this->mediaImporter->buildPath($viSlug, 'hero', null, $extracted['hero_image_url']);
            $media = $dryRun ? null : $this->mediaImporter->importFromUrl($extracted['hero_image_url'], $path);
            if ($media) {
                $out['hero_media_id'] = $media->id;
            }
        }

        if (! empty($extracted['intro_image_url'])) {
            $path = $this->mediaImporter->buildPath($viSlug, 'intro', null, $extracted['intro_image_url']);
            $media = $dryRun ? null : $this->mediaImporter->importFromUrl($extracted['intro_image_url'], $path);
            if ($media) {
                $out['intro_media_id'] = $media->id;
            }
        }

        if (! empty($extracted['icon_url'])) {
            $path = $this->mediaImporter->buildPath($viSlug, 'icon', null, $extracted['icon_url']);
            $media = $dryRun ? null : $this->mediaImporter->importFromUrl($extracted['icon_url'], $path);
            if ($media) {
                $out['icon_permalink'] = $media->permalink;
            }
        }

        return $out;
    }
}
