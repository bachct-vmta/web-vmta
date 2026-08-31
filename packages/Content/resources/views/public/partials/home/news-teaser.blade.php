@if($posts->isNotEmpty())
<section class="vmta-bg-filter-5 vmta-bg-scale-15 relative py-[60px] md-fs:py-[80px] bg-white overflow-hidden" data-home-section="news">

    <div class="relative max-w-7xl mx-auto px-4">
        <div class="mb-6 md-fs:mb-8" style="text-align: left;">
            <h2 class="font-sharp-bo fs-vmta-80 uppercase text-[#0b7f7c] leading-[1.3] mb-0">
                {{ __('content::public.latest_posts_heading') }}
            </h2>
            <p class="font-utm-helve text-slate-600 mt-2">
                {{ __('content::public.latest_posts_subtitle') }}
            </p>
        </div>

        <div class="custom-post-grid">
            @foreach($posts->take(3) as $post)
                @php
                    $tr  = $post->translations->firstWhere('locale', $locale) ?? $post->translations->first();
                    $cat = $post->category?->translations->firstWhere('locale', $locale)
                           ?? $post->category?->translations->first();
                    $authorEmail = $post->author?->email ?? 'editor@vmta.local';
                    $gravatarHash = md5(strtolower(trim($authorEmail)));
                    $gravatarUrl = "https://secure.gravatar.com/avatar/{$gravatarHash}?s=64&d=mm&r=g";
                @endphp
                <div class="post-item">
                    <div class="post-thumb">
                        <a href="{{ route('site.' . $locale . '.content.posts.show', ['slug' => $tr?->slug]) }}">
                            @if($post->featured_image)
                                <img src="{{ $post->featured_image }}"
                                     alt="{{ $tr?->title }}" loading="lazy" decoding="async">
                            @else
                                <div style="aspect-ratio: 16 / 9; background-color: #d4eceb; border-radius: 0.75rem; width: 100%;"></div>
                            @endif
                        </a>
                    </div>

                    <div class="post-content">
                        <div class="post-author">
                            <img src="{{ $gravatarUrl }}" alt="" width="32" height="32" class="avatar avatar-32 photo" loading="lazy" decoding="async">
                            <span>{{ $post->author?->name ?? 'Biên tập viên' }}</span>
                        </div>

                        <h3 class="post-title">
                            <a href="{{ route('site.' . $locale . '.content.posts.show', ['slug' => $tr?->slug]) }}">
                                {{ $tr?->title }}
                            </a>
                        </h3>

                        <div class="post-excerpt">
                            @if($tr?->excerpt)
                                {{ $tr->excerpt }}
                            @endif
                        </div>

                        <div class="post-meta">
                            @if($cat?->name)
                                <span class="post-cat">
                                    <a href="{{ route('site.' . $locale . '.content.posts.index') }}" rel="category tag">{{ $cat->name }}</a>
                                </span>
                            @endif
                            @if($post->published_at)
                                <span class="post-date">
                                    @if($cat?->name) - @endif<time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->format('d-m') }}</time>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
