{{--
    Hero dùng chung cho 3 trang — Figma 23:309.

    Tỉ lệ 1248/1563 ≈ 0.798 so với khung 1920 của Figma:
      dải       348 → 278; ảnh tràn từ 39.3% sang phải (60.7% rộng)
      breadcrumb y148 (60 trong dải) 18px → y48, 14px
      H1        y227 65px/700 lh 84 → y111, 52px lh 67, teal; xuống dòng giữ nguyên
                như trong chuỗi vì thiết kế ngắt "Bệnh viện / phòng khám" thành 2 dòng
      ô tìm kiếm y320 492x57 radius 10 → y185, 393x46 radius 8

    Toạ độ tuyệt đối và chiều cao 278 chỉ áp dụng từ lg; dưới đó dải cao theo nội dung,
    ô tìm kiếm xuống dưới tiêu đề và ảnh nền phủ trắng đậm hơn cho dễ đọc.

    Biến: $breadcrumbs, $heroTitle, $heroImage (?string), $filterModel (?string),
          $searchUrl (?string — có thì Enter chuyển sang trang đó kèm ?q=, dùng cho trang
          không có gì để lọc tại chỗ)
--}}
@php
    $heroImage = $heroImage ?? null;
    $filterModel = $filterModel ?? null;
    $searchUrl = $searchUrl ?? null;
@endphp

<section class="relative overflow-hidden bg-white">
    @if($heroImage)
        {{-- Thiết kế đặt ảnh cách mép trái 754px trên khung 1920: chiếm 60.7% --}}
        <div class="pointer-events-none absolute inset-y-0 right-0 w-full lg:w-[60.73%]" aria-hidden="true">
            <img src="{{ $heroImage }}" alt="" class="h-full w-full object-cover"
                 loading="eager" fetchpriority="high">
            <div class="absolute inset-0 bg-white/85 lg:bg-gradient-to-r lg:from-white lg:via-white/50 lg:to-transparent"></div>
        </div>
    @endif

    <div class="relative mx-auto w-full max-w-7xl px-4 pb-[40px] lg:h-[278px] lg:pb-0">
        <div class="pt-[48px]">
            @include('dental::public._breadcrumb', ['breadcrumbs' => $breadcrumbs ?? []])
        </div>

        {{-- Giữ trên một dòng nguồn: whitespace-pre-line sẽ biến thụt lề của template thành dòng thừa --}}
        <h1 class="m-0 mt-[14px] max-w-[712px] whitespace-pre-line text-[clamp(1.25rem,4vw,3.125rem)] font-bold uppercase leading-[1.29] tracking-[0.01em] text-vmta-teal ">{{ $heroTitle }}</h1>

        @if($filterModel)
            {{-- Dưới lg ô tìm kiếm nằm trong dòng chảy; từ lg mới ghim theo toạ độ Figma
                 (inset-x-4 thay vì w-full: containing block đã gồm padding của container) --}}
            <div class="mt-[24px] w-full max-w-[393px] lg:absolute lg:inset-x-4 lg:top-[185px] lg:ml-auto lg:mt-0">
                <label class="relative block">
                    <span class="sr-only">{{ __('dental::public.search_placeholder') }}</span>
                    <input type="search"
                           x-model="{{ $filterModel }}"
                           @if($searchUrl)
                           @keydown.enter.prevent="window.location = @js($searchUrl) + '?q=' + encodeURIComponent({{ $filterModel }})"
                           @endif
                           placeholder="{{ __('dental::public.search_placeholder') }}"
                           class="h-[46px] w-full rounded-[8px] border border-vmta-teal bg-white pl-[18px] pr-[45px] text-[14px] text-vmta-teal outline-none transition placeholder:text-vmta-teal focus:ring-2 focus:ring-vmta-teal/20">
                    <svg class="pointer-events-none absolute right-[21px] top-1/2 h-[22px] w-[22px] -translate-y-1/2 text-vmta-teal"
                         viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                        <path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </label>
            </div>
        @endif
    </div>
</section>
