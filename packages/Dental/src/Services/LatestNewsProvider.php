<?php

namespace Packages\Dental\Src\Services;

use Illuminate\Support\Collection;
use Packages\Content\Src\Repositories\Interfaces\PostRepositoryInterface;

/**
 * Điểm phụ thuộc duy nhất của package này vào packages/Content.
 * Muốn gỡ sidebar tin thì sửa đúng file này để trả về collection rỗng.
 */
final class LatestNewsProvider
{
    public function __construct(private readonly PostRepositoryInterface $posts) {}

    /**
     * @return Collection<int, array{title:string,url:string,thumbnail:?string,published_at:?\Illuminate\Support\Carbon}>
     */
    public function forSidebar(string $locale, ?int $limit = null): Collection
    {
        $limit ??= (int) config('dental.news_sidebar_limit', 5);

        return $this->posts->latest($limit)
            ->map(function ($post) use ($locale) {
                $translation = $post->translate($locale) ?? $post->translations->first();

                if ($translation === null) {
                    return null;
                }

                $permalink = $post->coverMedia?->permalink;

                return [
                    'title' => $translation->title,
                    'url' => route("site.{$locale}.content.posts.show", ['slug' => $translation->slug]),
                    'thumbnail' => $permalink ? media_permalink_url($permalink) : null,
                    'published_at' => $post->published_at,
                ];
            })
            ->filter()
            ->values();
    }
}
