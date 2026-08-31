{{--
    Card cơ sở — Figma 23:309, nhóm card tại (179,575). Tỉ lệ 1248/1563 ≈ 0.798:
      card    255x337 radius 25 → 204x269 radius 20, viền 1px teal
      tiêu đề y23 24px/700 teal → y18 19px, căn giữa
      ảnh     14,69 228x145 radius 9 → 11,55 182x116 radius 7
      badge   56,231 144x23 radius 52 nền đỏ → 45,184 115x18, chữ 11px, chấm 14px
      địa chỉ 14,268 226 rộng 14px → 11,214 180 rộng 11px
      nút     88,298 80x23 nền teal 14px/700 → y234 cao 24, rộng theo nhãn (tối thiểu 64), 11px

    Toạ độ nhỏ hơn 1px so với Figma vì viền vẽ phía trong, con tuyệt đối tính từ padding box.
    Dưới md card bỏ toạ độ tuyệt đối, xếp dọc và chiếm trọn bề ngang màn hình.

    Biến: $facility
--}}
@php
    $locale = app()->getLocale();
    $t = $facility->translate($locale) ?? $facility->translations->first();
    $image = $facility->cover_url;
@endphp

<article class="relative flex w-full flex-col items-center gap-[10px] rounded-[20px] border border-vmta-teal bg-white p-4
                md:block md:h-[269px] md:w-[204px] md:gap-0 md:p-0">
    <h3 class="m-0 w-full truncate text-center text-[19px] font-bold leading-[23px] text-vmta-teal
               md:absolute md:inset-x-0 md:top-[17px] md:px-2">
        {{ $t->name ?? '' }}
    </h3>

    <div class="aspect-[182/116] w-full overflow-hidden rounded-[7px] bg-[#d9d9d9]
                md:absolute md:left-[10px] md:top-[54px] md:aspect-auto md:h-[116px] md:w-[182px]">
        @if($image)
            <img src="{{ $image }}" alt="{{ $t->name ?? '' }}"
                 class="h-full w-full object-cover" loading="lazy">
        @endif
    </div>

    <p @class([
        'm-0 flex h-[18px] w-[fit-content] items-center justify-center gap-[5px] rounded-[42px] pl-[2px] pr-[8px] text-[11px] leading-[14px] text-white md:absolute md:left-[44px] md:top-[183px]' => true,
        'bg-vmta-red' => $facility->is_operating,
        'bg-[#9a9a9a]' => ! $facility->is_operating,
    ])>
        <span @class([
            'h-[14px] w-[14px] shrink-0 rounded-full border-2 border-white' => true,
            'bg-vmta-red' => $facility->is_operating,
            'bg-[#9a9a9a]' => ! $facility->is_operating,
        ])></span>
        {{ $facility->is_operating ? __('dental::public.operating') : __('dental::public.not_operating') }}
    </p>

    @if($t->address ?? null)
        <p class="m-0 w-full truncate text-center text-[11px] leading-[14px] text-[#5d5d5d]
                  md:absolute md:left-[10px] md:top-[213px] md:w-[180px]">
            <span class="font-bold">{{ __('dental::public.address_label') }}</span>
            {{ $t->address }}
        </p>
    @endif

    <a href="{{ route('site.'.$locale.'.dental.facility', ['facility' => $t->slug]) }}"
       class="flex h-[24px] min-w-[64px] items-center justify-center rounded-[42px] bg-vmta-teal px-[14px] text-[11px] font-bold leading-[14px] text-white transition hover:brightness-110
              md:absolute md:left-1/2 md:top-[234px] md:-translate-x-1/2">
        {{ __('dental::public.detail') }}
    </a>
</article>
