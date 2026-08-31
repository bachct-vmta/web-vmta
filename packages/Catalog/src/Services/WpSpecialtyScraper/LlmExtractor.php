<?php

namespace Packages\Catalog\Src\Services\WpSpecialtyScraper;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use JsonSchema\Validator;
use Packages\Catalog\Src\Exceptions\ExtractedShapeInvalidException;
use Symfony\Component\Process\Process;

/**
 * Calls Claude Code CLI headless (`claude --print`) with a snapshot + JSON
 * schema, expects a JSON object matching the schema. Retries up to 3 times.
 *
 * The CLI invocation is wrapped in a protected method so unit tests can swap
 * it for an in-memory stub.
 */
class LlmExtractor
{
    private const MAX_ATTEMPTS = 3;

    private const SCHEMA_PATH = __DIR__.'/extracted-schema.json';

    public function extract(array $snapshot, string $sourceUrl, string $locale): array
    {
        $schema = $this->loadSchema();
        $prompt = $this->buildPrompt($snapshot, $sourceUrl, $locale, $schema);

        $lastErrors = [];
        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $raw = $this->callClaudeCli($prompt);
            $this->logRaw($raw, $sourceUrl, $locale, $attempt);

            $parsed = $this->parseJson($raw);
            if ($parsed === null) {
                $lastErrors[] = "attempt {$attempt}: invalid JSON";

                continue;
            }

            $errors = $this->validate($parsed, $schema);
            if (empty($errors)) {
                return $parsed;
            }
            $lastErrors[] = "attempt {$attempt}: ".implode('; ', $errors);
        }

        throw new ExtractedShapeInvalidException(
            "LLM extract failed after ".self::MAX_ATTEMPTS." attempts for {$sourceUrl}: ".implode(' | ', $lastErrors),
        );
    }

    protected function callClaudeCli(string $prompt): string
    {
        $process = new Process(['claude', '--print', '--output-format', 'text'], timeout: 90, input: $prompt);
        $process->mustRun();

        return $process->getOutput();
    }

    private function loadSchema(): array
    {
        return json_decode(File::get(self::SCHEMA_PATH), true);
    }

    private function buildPrompt(array $snapshot, string $url, string $locale, array $schema): string
    {
        $schemaJson = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $snapshotJson = json_encode($snapshot, JSON_UNESCAPED_UNICODE);
        if (strlen($snapshotJson) > 80000) {
            $snapshotJson = substr($snapshotJson, 0, 80000).'... [truncated]';
        }

        return <<<PROMPT
You are extracting structured content from a WordPress landing page snapshot.

Source URL: {$url}
Locale: {$locale}

The snapshot below contains text, headings, paragraphs, image src URLs, and link href URLs from the page. Extract the structured shape and return ONLY raw JSON (no prose, no markdown fences) matching this JSON Schema:

```json
{$schemaJson}
```

EXTRACTION RULES:
- hero_h1: the main H1 heading at top (uppercase form OK).
- hero_image_url: pick from the _images list in snapshot — usually the FIRST image with width >= 1000 (hero/background banner).
- icon_url: small svg/png icon representing the specialty if present (often width < 200).
- intro_h2: first H2 below the hero — intro section heading.
- intro_lead: bold or strong subtitle paragraph right under intro_h2 (1 sentence).
- intro_body_html: body paragraphs of intro section, preserved as HTML (only <p><br><strong><em><ul><ol><li> tags).
- intro_image_url: pick from _images — portrait-ish image (height > width) appearing after hero, sits next to intro text.
- strengths: array of strength/advantage cards (image + title + bullets). Pick image_url from _images — small landscape images (~390x336) appearing in middle of page, one per strength card. If WP duplicates cards, deduplicate by title.
- hospitals: array of hospital/partner cards (name + image + bullets + 2 optional CTA buttons with label and URL). Pick image_url from remaining _images. CTA labels often "ĐẶT LỊCH" / "TÌM HIỂU THÊM" or "BOOK" / "VIEW MORE"; URL may be empty if WP renders as styled text — set url to "" when not a real link.
- lead_*: form section labels (h2 line 1, h2 line 2, subtitle, body, demand placeholder, submit button label).

If a field is missing in source, return null for it. Do NOT invent content. Image URLs must be absolute (start with http) — pick from _images list verbatim, never fabricate URLs.

SNAPSHOT JSON:
```json
{$snapshotJson}
```

Return ONLY the JSON object matching the schema. No prose, no markdown fences, no explanation.
PROMPT;
    }

    private function parseJson(string $raw): ?array
    {
        $trimmed = trim($raw);
        $trimmed = preg_replace('/^```(?:json)?\s*/i', '', $trimmed);
        $trimmed = preg_replace('/\s*```$/i', '', (string) $trimmed);
        $decoded = json_decode((string) $trimmed, true);

        return is_array($decoded) ? $decoded : null;
    }

    /** @return array<int, string> */
    private function validate(array $parsed, array $schema): array
    {
        $validator = new Validator;
        $payload = json_decode(json_encode($parsed));
        $validator->validate($payload, json_decode(json_encode($schema)));

        if ($validator->isValid()) {
            return [];
        }

        return array_map(
            fn ($e) => "{$e['property']}: {$e['message']}",
            $validator->getErrors(),
        );
    }

    private function logRaw(string $raw, string $url, string $locale, int $attempt): void
    {
        $slug = preg_replace('/[^a-z0-9]+/i', '-', parse_url($url, PHP_URL_PATH) ?? 'unknown');
        $filename = storage_path("logs/llm-extract-{$slug}-{$locale}-attempt{$attempt}-".now()->format('Ymd-His').'.txt');
        File::ensureDirectoryExists(dirname($filename));
        File::put($filename, $raw);
        Log::info('LLM extract raw response saved', ['file' => $filename, 'attempt' => $attempt]);
    }
}
