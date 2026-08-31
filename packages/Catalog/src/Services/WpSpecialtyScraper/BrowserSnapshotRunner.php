<?php

namespace Packages\Catalog\Src\Services\WpSpecialtyScraper;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Wraps the `agent-browser snapshot` CLI to fetch a structured DOM snapshot
 * of a public WP page. Caches the parsed JSON to /tmp so subsequent runs with
 * --reuse-snapshot skip the browser.
 */
class BrowserSnapshotRunner
{
    private const RETRY_DELAYS_MS = [2000, 4000, 8000];

    public function snapshot(string $url, string $cachePath, bool $reuse): array
    {
        if ($reuse && File::exists($cachePath)) {
            $cached = json_decode(File::get($cachePath), true);
            if (is_array($cached)) {
                return $cached;
            }
        }

        $lastError = null;
        foreach (self::RETRY_DELAYS_MS as $attemptIdx => $delayMs) {
            try {
                $output = $this->runProcess($url);
                $parsed = $this->parseOutput($output);
                File::ensureDirectoryExists(dirname($cachePath));
                File::put($cachePath, json_encode($parsed, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

                return $parsed;
            } catch (\Throwable $e) {
                $lastError = $e;
                if ($attemptIdx < count(self::RETRY_DELAYS_MS) - 1) {
                    usleep($delayMs * 1000);
                }
            }
        }

        throw new RuntimeException(
            "agent-browser snapshot failed after retries for {$url}: ".($lastError?->getMessage() ?? 'unknown'),
            previous: $lastError,
        );
    }

    private function runProcess(string $url): string
    {
        $session = 'wp-scrape-'.substr(md5($url), 0, 8);

        // Navigate first (separate command — `snapshot` only captures current page).
        $openProc = new Process(['agent-browser', '--session', $session, 'open', $url], timeout: 60);
        $openProc->mustRun();

        // Tiny wait so client-side hydration finishes before snapshot.
        $waitProc = new Process(['agent-browser', '--session', $session, 'wait', '1500'], timeout: 10);
        $waitProc->run();

        // Capture interactive tree + URLs as JSON.
        $snapProc = new Process(['agent-browser', '--session', $session, 'snapshot', '-u', '--json'], timeout: 60);
        $snapProc->mustRun();
        $snapshotOutput = $snapProc->getOutput();

        // Enrich snapshot with <img src> list (accessibility tree drops img URLs).
        $imagesScript = <<<'JS'
            const seen = new Set();
            const imgs = Array.from(document.querySelectorAll('main img, header img, [class*=hero] img'))
                .map(i => ({ src: i.currentSrc || i.src || '', alt: i.alt || '', w: i.naturalWidth || 0, h: i.naturalHeight || 0 }))
                .filter(i => i.src && !i.src.startsWith('data:') && !seen.has(i.src) && seen.add(i.src));
            JSON.stringify(imgs);
            JS;
        $evalProc = new Process(['agent-browser', '--session', $session, 'eval', '--stdin', '--json'],
            timeout: 30, input: $imagesScript);
        $evalProc->run();
        $imagesJson = $this->extractEvalResult($evalProc->getOutput());

        // Cleanup session to avoid sticky state across multiple crawl runs.
        $closeProc = new Process(['agent-browser', '--session', $session, 'close'], timeout: 10);
        $closeProc->run();

        return $this->mergeImagesIntoSnapshot($snapshotOutput, $imagesJson);
    }

    private function extractEvalResult(string $rawOutput): string
    {
        $decoded = json_decode(trim($rawOutput), true);
        if (! is_array($decoded) || ($decoded['success'] ?? false) !== true) {
            return '[]';
        }
        $inner = $decoded['data']['result'] ?? '[]';

        return is_string($inner) ? $inner : '[]';
    }

    private function mergeImagesIntoSnapshot(string $snapshotJson, string $imagesJson): string
    {
        $snapshot = json_decode(trim($snapshotJson), true);
        if (! is_array($snapshot)) {
            return $snapshotJson;
        }

        $images = json_decode($imagesJson, true);
        if (! is_array($images)) {
            $images = [];
        }

        if (! isset($snapshot['data']) || ! is_array($snapshot['data'])) {
            $snapshot['data'] = [];
        }
        $snapshot['data']['_images'] = $images;

        return json_encode($snapshot, JSON_UNESCAPED_UNICODE);
    }

    private function parseOutput(string $output): array
    {
        $decoded = json_decode(trim($output), true);
        if (! is_array($decoded)) {
            throw new RuntimeException('agent-browser output is not valid JSON');
        }

        return $decoded;
    }
}
