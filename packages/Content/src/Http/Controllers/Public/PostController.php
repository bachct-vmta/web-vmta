<?php

namespace Packages\Content\Src\Http\Controllers\Public;

use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Packages\Content\Src\Jobs\IncrementPostViewCount;
use Packages\Content\Src\Repositories\Interfaces\PostRepositoryInterface;
use Packages\Content\Src\Services\RecentlyViewedService;

class PostController extends Controller
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
        private readonly RecentlyViewedService $recentlyViewed,
    ) {}

    public function index(): View
    {
        $categoryId = request()->integer('category_id') ?: null;

        SEOTools::setTitle((string) __('content::public.news_meta_title'));
        SEOTools::setDescription((string) __('content::public.news_meta_description'));
        SEOTools::opengraph()->setUrl(url()->current());

        return view('content::public.posts.index', [
            'posts' => $this->posts->paginatePublished($categoryId, 12)->withQueryString(),
            'activeCategoryId' => $categoryId,
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $locale = app()->getLocale();
        $post = $this->posts->findPublishedBySlug($slug, $locale);

        abort_if($post === null, 404);

        // Queue the view-count increment so HTTP response isn't blocked + the job de-dups
        // per (post, IP) within a 1-hour window.
        // NOTE: behind a reverse-proxy/CDN, accurate per-visitor de-dup requires
        // App\Http\Middleware\TrustProxies (Phase 8 hardening). Without it all visitors
        // collapse to the proxy IP and view_count caps at 1/hour/post.
        IncrementPostViewCount::dispatch($post->id, $request->ip());

        // Track in cookie (7d TTL, max 3 IDs, dedupe — newest first)
        $this->recentlyViewed->track($post->id);

        $translation = $post->translate($locale) ?? $post->translations->first();

        SEOTools::setTitle($translation->seo_title ?: $translation->title);
        if (! empty($translation->seo_description)) {
            SEOTools::setDescription($translation->seo_description);
        } elseif (! empty($translation->excerpt)) {
            SEOTools::setDescription(Str::limit(strip_tags($translation->excerpt), 160));
        }
        SEOTools::opengraph()->setUrl(url()->current());
        if (! empty($translation->seo_og_image)) {
            SEOTools::opengraph()->addImage($translation->seo_og_image);
        }

        $relatedPosts = $this->posts
            ->latest(5)
            ->reject(fn ($relatedPost) => $relatedPost->id === $post->id)
            ->take(2)
            ->values();

        return view('content::public.posts.show', [
            'post' => $post,
            'translation' => $translation,
            'relatedPosts' => $relatedPosts,
        ]);
    }
}
