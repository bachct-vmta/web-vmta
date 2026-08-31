{{--
    Một danh mục cơ sở (Bệnh Viện / Phòng Khám) — Figma 23:309. Tỉ lệ 1248/1563 ≈ 0.798:
      tiêu đề    28px/700 #5d5d5d → 22px, căn giữa
      lưới       card 255 gap 72/49 → card 204 gap 57/39 (5 x 204 + 4 x 57 = 1248).
                 Dưới md chuyển một cột, card giãn hết bề ngang, gap dọc rút về 24
      dots       14px cách 29px → 11px cách 23px

    Mỗi trang carousel là một slide chứa lưới, không dùng grid mode của Swiper vì mode đó
    dùng chung spaceBetween cho cả hai trục còn thiết kế cần 72 ngang và 49 dọc.

    Biến: $title, $items (Collection<DentalFacility>)
--}}
@php
    $items = $items ?? collect();
    $locale = app()->getLocale();

    // Hai hàng mỗi trang, năm cột như thiết kế
    $pages = $items->chunk(10);

    // Bỏ dấu để "phong kham 2" khớp "Phòng Khám 2"
    $filterText = function ($facility) use ($locale) {
        $t = $facility->translate($locale) ?? $facility->translations->first();

        return \Illuminate\Support\Str::lower(
            \Illuminate\Support\Str::ascii(trim(($t->name ?? '').' '.($t->address ?? '')))
        );
    };
@endphp

@if($items->isNotEmpty())
    <section>
        <h2 class="m-0 text-center text-[22px] font-bold leading-[27px] text-[#5d5d5d]">{{ $title }}</h2>

        <div x-show="! filtering" x-cloak class="mt-[39px]">
            <div class="swiper" data-vmta-swiper data-per-view="1" data-autoplay="false">
                <div class="swiper-wrapper">
                    @foreach($pages as $page)
                        <div class="swiper-slide">
                            <div class="grid grid-cols-1 gap-y-6 md:justify-center md:gap-x-[57px] md:gap-y-[39px] md:[grid-template-columns:repeat(auto-fit,204px)]">
                                @foreach($page as $facility)
                                    @include('dental::public._facility-card', ['facility' => $facility])
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($pages->count() > 1)
                    <div class="swiper-pagination !static mt-[39px] !w-auto"></div>
                @endif
            </div>
        </div>

        {{-- Khi lọc thì đổi sang lưới phẳng: Swiper không reflow quanh slide bị ẩn --}}
        <div x-show="filtering" x-cloak class="mt-[39px]">
            <div class="grid grid-cols-1 gap-y-6 md:justify-center md:gap-x-[57px] md:gap-y-[39px] md:[grid-template-columns:repeat(auto-fit,204px)]">
                @foreach($items as $facility)
                    <div data-filter-text="{{ $filterText($facility) }}" x-show="matches($el.dataset.filterText)">
                        @include('dental::public._facility-card', ['facility' => $facility])
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
