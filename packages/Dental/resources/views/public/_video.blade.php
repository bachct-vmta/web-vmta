{{--
    Khối video trên trang dịch vụ — Figma 39:36.

    Thiết kế vẽ 1018x611 cạnh sidebar 512; tỉ lệ 1248/1563 ≈ 0.798 nên sidebar còn 409,
    video chiếm phần còn lại và giữ tỉ lệ 1018:611. Caption 36px/700 inset 52,53 → 29px, 42.

    Player chọn theo đuôi URL: .m3u8 qua hls-bootstrap, mp4/webm dùng <video>, còn lại
    (YouTube/Vimeo) là iframe bấm mới tải để embed không ăn vào LCP.

    Biến: $videoUrl, $poster, $caption
--}}
@php
    $videoUrl = $videoUrl ?? null;
    $poster = $poster ?? null;
    $caption = $caption ?? null;

    $path = parse_url((string) $videoUrl, PHP_URL_PATH) ?: '';
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    $isHls = $extension === 'm3u8';
    $isFile = in_array($extension, ['mp4', 'webm', 'ogg'], true);
    $isEmbed = ! $isHls && ! $isFile && $videoUrl;

    $embedUrl = null;
    if ($isEmbed) {
        if (preg_match('#youtu\.be/([\w-]+)#', $videoUrl, $m) || preg_match('#youtube\.com/watch\?v=([\w-]+)#', $videoUrl, $m)) {
            $embedUrl = 'https://www.youtube-nocookie.com/embed/'.$m[1].'?autoplay=1';
        } elseif (preg_match('#vimeo\.com/(\d+)#', $videoUrl, $m)) {
            $embedUrl = 'https://player.vimeo.com/video/'.$m[1].'?autoplay=1';
        } else {
            $embedUrl = $videoUrl;
        }
    }
@endphp

@if($isHls)
    @pushOnce('scripts')
        @vite('resources/js/hls-bootstrap.js')
    @endPushOnce
@endif

@if($videoUrl)
    <figure class="m-0 w-full min-w-0 flex-1" x-data="{ playing: false }">
        <div class="relative aspect-[1018/611] w-full overflow-hidden rounded-[10px] bg-[#111]">
            @if($isHls || $isFile)
                <video class="h-full w-full object-cover"
                       controls playsinline
                       @if($poster) poster="{{ $poster }}" @endif
                       @if($isHls) data-hls-src="{{ $videoUrl }}" @else src="{{ $videoUrl }}" @endif></video>
            @else
                <template x-if="playing">
                    <iframe class="absolute inset-0 h-full w-full" src="{{ $embedUrl }}"
                            title="{{ $caption ?? '' }}" loading="lazy" allowfullscreen
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; picture-in-picture"></iframe>
                </template>

                <button type="button" x-show="! playing" @click="playing = true"
                        class="group absolute inset-0 flex h-full w-full items-center justify-center">
                    @if($poster)
                        <img src="{{ $poster }}" alt="" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                    @endif
                    <span class="absolute inset-0 bg-black/25 transition group-hover:bg-black/35"></span>
                    <span class="relative flex h-[51px] w-[51px] items-center justify-center rounded-full bg-vmta-teal/90 text-white transition group-hover:scale-105">
                        <svg class="ml-1 h-[22px] w-[22px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M8 5.5v13l11-6.5z"/>
                        </svg>
                        <span class="sr-only">{{ $caption ?? __('dental::public.detail') }}</span>
                    </span>
                </button>
            @endif

            @if($caption)
                <figcaption class="pointer-events-none absolute left-[42px] right-[42px] top-[42px] text-[clamp(1rem,1.6vw,29px)] font-bold uppercase leading-[1.22] text-white drop-shadow">
                    {{ $caption }}
                </figcaption>
            @endif
        </div>
    </figure>
@endif
