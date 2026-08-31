<?php

namespace Packages\Catalog\Src\Services\WpSpecialtyScraper;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Packages\Core\Src\Models\MediaFile;

/**
 * Download a remote URL to local storage and create / reuse a MediaFile row
 * keyed by permalink. Idempotent: re-importing the same URL+path does not
 * create duplicates.
 */
class MediaImporter
{
    public function importFromUrl(string $url, string $relativePath): ?MediaFile
    {
        // Match project upload root: media admin writes to public/uploads (see
        // StorageDriverService — `'root' => public_path('uploads')`). Keeping the
        // same root lets the existing /uploads/<permalink> URL convention serve
        // both crawler-imported and admin-uploaded files.
        $absPath = public_path('uploads/'.ltrim($relativePath, '/'));
        File::ensureDirectoryExists(dirname($absPath));

        if (! File::exists($absPath)) {
            try {
                $response = Http::retry(3, 500)->timeout(30)->get($url);
            } catch (\Throwable $e) {
                Log::warning('Media download exception', ['url' => $url, 'error' => $e->getMessage()]);

                return null;
            }

            if ($response->failed()) {
                Log::warning('Media download failed', ['url' => $url, 'status' => $response->status()]);

                return null;
            }

            File::put($absPath, $response->body());
        }

        return MediaFile::firstOrCreate(
            ['permalink' => $relativePath],
            [
                'name' => basename($relativePath),
                'alt' => '',
                'size' => File::size($absPath),
                'mine_type' => File::mimeType($absPath) ?: null,
                'storage_driver' => 'local',
            ],
        );
    }

    public function buildPath(string $slug, string $kind, ?int $index, string $url): string
    {
        $pathPart = parse_url($url, PHP_URL_PATH) ?? '';
        $ext = strtolower(pathinfo($pathPart, PATHINFO_EXTENSION) ?: 'jpg');
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'], true)) {
            $ext = 'jpg';
        }

        if ($index === null) {
            return "specialties/{$slug}/{$kind}.{$ext}";
        }

        return "specialties/{$slug}/{$kind}/{$index}.{$ext}";
    }
}
