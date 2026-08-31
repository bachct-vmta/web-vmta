{{--
    Card dịch vụ — Figma 28:767, nhóm card tại (361,576). Tỉ lệ 1248/1563 ≈ 0.798:
      card   255x337 radius 25 → 204x269 radius 20, viền 1px teal
      icon   23,14 209x209 → 18,11 167x167
      tiêu đề y217 20px/700 #5d5d5d → y173 16px, căn giữa, xuống 2 dòng
      nút    y282 80x23 radius 52 nền đỏ → y225 cao 24, rộng theo nhãn (tối thiểu 64), 11px/700

    Biến: $service, $facilitySlug
--}}
@php
    $locale = app()->getLocale();
    $t = $service->translate($locale) ?? $service->translations->first();
    $icon = $service->icon_url;
@endphp

<article class="relative h-[269px] w-[204px] rounded-[20px] border border-vmta-teal bg-white">
    <div class="absolute left-[17px] top-[10px] flex h-[167px] w-[167px] items-center justify-center">
        @if($icon)
            <img src="{{ $icon }}" alt="" class="h-full w-full object-contain" loading="lazy">
        @else
            {{-- Hình răng chung để dịch vụ chưa có icon vẫn đọc ra là dịch vụ nha khoa --}}
            <svg class="h-[96px] w-[96px] text-vmta-teal" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 3c-2.5 0-3.5 1-5 1S4 3.5 4 6c0 3 1 5 1.5 8 .4 2.3.6 4 1.7 4 1 0 1.3-1.4 1.6-3.2.3-1.6.6-2.8 2.2-2.8s1.9 1.2 2.2 2.8c.3 1.8.6 3.2 1.6 3.2 1.1 0 1.3-1.7 1.7-4C17 11 18 9 18 6c0-2.5-1.5-2-3-2s-2.5-1-3-1z"
                      stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
            </svg>
        @endif
    </div>

    <h3 class="absolute inset-x-[11px] top-[172px] m-0 text-center text-[16px] font-bold uppercase leading-[19px] text-[#5d5d5d]">
        {{ $t->title ?? '' }}
    </h3>

    <a href="{{ route('site.'.$locale.'.dental.service', ['facility' => $facilitySlug, 'service' => $t->slug]) }}"
       class="absolute left-1/2 top-[224px] flex h-[24px] min-w-[64px] -translate-x-1/2 items-center justify-center rounded-[42px] bg-vmta-red px-[14px] text-[11px] font-bold leading-[14px] text-white transition hover:brightness-110">
        {{ __('dental::public.detail') }}
    </a>
</article>
