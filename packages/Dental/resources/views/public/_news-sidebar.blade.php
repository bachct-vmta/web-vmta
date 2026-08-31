{{--
    Sidebar tin mới nhất — Figma 39:36 tại x1229. Tỉ lệ 1248/1563 ≈ 0.798:
      cột     512 → 409
      tiêu đề 28px/700 teal → 22px
      item    thumb 208x139 radius 10 → 166x111 radius 8; hàng cao 143 → 114
      nhịp    tiêu đề 18/700 → 14px; "Xem thêm" cách tiêu đề 15 (→ 12);
              ngày ghim 121 từ đỉnh hàng (→ 97)

    Biến: $posts (Collection<array{title,url,thumbnail,published_at}>) từ LatestNewsProvider
--}}
@php($posts = $posts ?? collect())

@if($posts->isNotEmpty())
    <aside class="w-full xl:w-[409px]">
        <h2 class="m-0 text-[22px] font-bold leading-[27px] text-vmta-teal">
            {{ __('dental::public.news') }}
        </h2>

        <ul class="m-0 mt-[18px] flex list-none flex-col gap-[15px] p-0">
            @foreach($posts as $post)
                <li class="m-0 flex h-[114px] gap-[10px]">
                    <a href="{{ $post['url'] }}"
                       class="block h-[111px] w-[166px] shrink-0 overflow-hidden rounded-[8px] bg-vmta-cream">
                        @if($post['thumbnail'])
                            <img src="{{ $post['thumbnail'] }}" alt="{{ $post['title'] }}"
                                 class="h-full w-full object-cover" loading="lazy">
                        @endif
                    </a>

                    <div class="relative min-w-0 flex-1">
                        <h3 class="m-0 line-clamp-3 text-[14px] font-bold leading-[18px] text-[#5d5d5d]">
                            <a href="{{ $post['url'] }}" class="transition-colors hover:text-vmta-teal">{{ $post['title'] }}</a>
                        </h3>

                        <a href="{{ $post['url'] }}"
                           class="mt-[12px] inline-block text-[14px] leading-[18px] text-vmta-teal underline-offset-2 hover:underline">
                            {{ __('dental::public.read_more') }}
                        </a>

                        @if($post['published_at'])
                            <p class="absolute top-[97px] m-0 text-[14px] leading-[18px] text-[#5d5d5d]">
                                <time datetime="{{ $post['published_at']->toDateString() }}">{{ $post['published_at']->format('d/m/Y') }}</time>
                            </p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </aside>
@endif
