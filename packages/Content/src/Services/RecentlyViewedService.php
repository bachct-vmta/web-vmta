<?php

namespace Packages\Content\Src\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Packages\Content\Src\Repositories\Interfaces\PostRepositoryInterface;

/**
 * Cookie-backed "recently viewed posts" tracker.
 *
 * Stores up to MAX_ITEMS post IDs in a JSON-encoded cookie with a TTL of TTL_DAYS days.
 * Newest first; visiting the same post again moves it to the front (dedupe).
 */
class RecentlyViewedService
{
    private const COOKIE_NAME = 'vmta_recent_posts';
    private const MAX_ITEMS = 3;
    private const TTL_DAYS = 7;

    public function __construct(private readonly PostRepositoryInterface $posts) {}

    /**
     * Record a post view → push to front of the cookie list (dedupe, cap at MAX_ITEMS).
     * The cookie is queued on the outgoing response.
     */
    public function track(int $postId): void
    {
        if ($postId <= 0) {
            return;
        }

        $ids = $this->readIds();

        // Move to front, dedupe, cap
        $ids = array_values(array_filter($ids, fn ($id) => $id !== $postId));
        array_unshift($ids, $postId);
        $ids = array_slice($ids, 0, self::MAX_ITEMS);

        Cookie::queue(
            Cookie::make(
                self::COOKIE_NAME,
                json_encode($ids, JSON_THROW_ON_ERROR),
                self::TTL_DAYS * 24 * 60, // minutes
                '/',
                null,
                config('app.env') === 'production',
                true,  // httpOnly
                false,
                'Lax'
            )
        );
    }

    /**
     * Return up to MAX_ITEMS published Post models for the given locale, ordered
     * by recency (matching cookie order). Posts that no longer exist or are
     * unpublished are silently skipped.
     */
    public function getRecent(?string $locale = null): Collection
    {
        $locale ??= app()->getLocale();
        $ids = $this->readIds();

        if ($ids === []) {
            return new Collection();
        }

        return $this->posts->findPublishedByIds($ids, $locale);
    }

    /**
     * @return array<int, int>
     */
    private function readIds(): array
    {
        /** @var Request $request */
        $request = app(Request::class);
        $raw = $request->cookie(self::COOKIE_NAME);

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        if (! is_array($decoded)) {
            return [];
        }

        // Sanitize: keep positive integers only
        $ids = [];
        foreach ($decoded as $value) {
            $id = is_numeric($value) ? (int) $value : 0;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return array_slice($ids, 0, self::MAX_ITEMS);
    }
}
