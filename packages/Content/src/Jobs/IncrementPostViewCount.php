<?php

namespace Packages\Content\Src\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Packages\Content\Src\Repositories\Interfaces\PostRepositoryInterface;

/**
 * Increment Post.view_count asynchronously with a 1-hour per-(post, IP) de-dup window.
 *
 * The de-dup cache key uses both `post_id` and a hashed IP so a single visitor refreshing
 * a post within the hour only counts once. Cache TTL configurable via env CONTENT_VIEW_COUNT_TTL.
 */
class IncrementPostViewCount implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $postId,
        public readonly ?string $clientIp,
    ) {}

    public function handle(PostRepositoryInterface $posts): void
    {
        $ttl = (int) config('content.view_count_dedup_ttl', 3600);
        $ipFingerprint = $this->clientIp !== null
            ? substr(hash('sha256', $this->clientIp), 0, 16)
            : 'anonymous';
        $key = "content:viewcount:post:{$this->postId}:ip:{$ipFingerprint}";

        // Cache::add returns true only if the key did NOT previously exist → de-dup primitive.
        if (! Cache::add($key, 1, $ttl)) {
            return;
        }

        $posts->incrementViewCount($this->postId);
    }
}
