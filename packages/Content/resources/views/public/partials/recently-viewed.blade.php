{{--
    "Nội Dung Bạn Đã Xem" — 3 posts gần nhất từ cookie vmta_recent_posts (TTL 7d).
    Reads RecentlyViewedService directly so any page can @include this without
    passing data via controller.

    Variables (optional):
      - $recentPosts: Collection<Post> — pre-fetched list. If omitted, service is called.
      - $heading: string — section title. Defaults to locale-aware text.
--}}
@php
    $locale = app()->getLocale();
    $recentPosts = $recentPosts ?? app(\Packages\Content\Src\Services\RecentlyViewedService::class)->getRecent($locale);
    $heading = $heading ?? mb_strtoupper(__('content::public.recently_viewed_heading'));
@endphp

@if($recentPosts->isNotEmpty())
    @php
        $postTr = fn ($post) => $post->translations->firstWhere('locale', $locale) ?? $post->translations->first();
        $postUrl = fn ($tr) => route('site.'.$locale.'.content.posts.show', ['slug' => $tr?->slug]);

        // MediaFile permalinks are stored relative (e.g. "/TT-Thuan.png") — the
        // public URL needs the file-manager base prefix (default /uploads).
        // Bare $coverMedia->permalink resolves to http://host/TT-Thuan.png → 404.
        $mediaUrl = function ($media) {
            if (! $media?->permalink) return null;
            if (preg_match('#^https?://#', $media->permalink)) return $media->permalink;
            $base = rtrim(config('file-manager.base_path', '/uploads'), '/');
            return url($base.'/'.ltrim($media->permalink, '/'));
        };
        $postImage = fn ($post) => $mediaUrl($post->coverMedia)
            ?: asset('images/news/making-vietnam-medical-tourism.webp');
    @endphp

    <section class="bg-white py-12 md:py-16">
        <div class="mx-auto max-w-7xl px-4">
            <h2 class="font-sharp-bo mb-6 text-[28px] font-bold uppercase leading-tight text-vmta-teal md:text-[34px] mb-4">
                {{ $heading }}
            </h2>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($recentPosts as $post)
                    @php $tr = $postTr($post); @endphp
                    @continue($tr === null)
                    <article class="group overflow-hidden rounded-lg bg-white shadow-sm transition hover:shadow-md">
                        <a href="{{ $postUrl($tr) }}" class="block">
                            <div class="aspect-[16/10] overflow-hidden bg-slate-100">
                                <img src="{{ $postImage($post) }}"
                                     alt="{{ $tr->title }}"
                                     class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                     loading="lazy" decoding="async">
                            </div>
                            <div class="p-4">
                                @if($post->category && ($catTr = $post->category->translations->firstWhere('locale', $locale)))
                                    <span class="text-xs font-medium uppercase tracking-wide text-vmta-teal">
                                        {{ $catTr->name }}
                                    </span>
                                @endif
                                <h3 class="mt-1 line-clamp-2 text-[16px] font-semibold leading-snug text-slate-900 group-hover:text-vmta-teal">
                                    {{ $tr->title }}
                                </h3>
                                @if(! empty($tr->excerpt))
                                    <p class="mt-2 line-clamp-2 text-sm text-slate-600">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($tr->excerpt), 100) }}
                                    </p>
                                @endif
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
