{{--
    Gallery chứng nhận — Figma 28:767 y1783. Tỉ lệ 1248/1563 ≈ 0.798:
      nhãn   215x49 radius 10 nền teal, 28px/700 trắng → 172x39, 22px
      ô ảnh  208x261 radius 25, cách 63 → 166x208 radius 20, cách 50
             (6 x 166 + 5 x 50 = 1246, vẫn vừa 6 ô một hàng)
      khoảng trên nhãn 98 → 78

    Biến: $certificates (Collection<MediaFile>)
--}}
@php($certificates = $certificates ?? collect())

@if($certificates->isNotEmpty())
    <section class="pt-[78px]" x-data="{ open: false, src: '', alt: '' }">
        <h2 class="mx-auto flex h-[39px] w-fit min-w-[172px] items-center justify-center rounded-[8px] bg-vmta-teal px-[18px] text-[22px] font-bold leading-[27px] text-white">
            {{ __('dental::public.certificates') }}
        </h2>

        <ul class="mt-[39px] grid list-none justify-center gap-4 p-0 md:gap-[50px] [grid-template-columns:repeat(auto-fit,166px)]">
            @foreach($certificates as $media)
                @php($url = media_permalink_url($media->permalink))
                <li class="m-0">
                    <button type="button"
                            @click="open = true; src = @js($url); alt = @js($media->alt ?? $media->name)"
                            class="block h-[208px] w-[166px] overflow-hidden rounded-[20px] border border-vmta-teal bg-white transition hover:shadow-[0_6px_18px_rgba(11,127,124,0.14)] focus:outline-none focus:ring-2 focus:ring-vmta-teal/40">
                        <img src="{{ $url }}" alt="{{ $media->alt ?? $media->name }}"
                             class="h-full w-full object-cover" loading="lazy">
                    </button>
                </li>
            @endforeach
        </ul>

        <div x-show="open" x-cloak
             @keydown.escape.window="open = false"
             @click.self="open = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-6"
             role="dialog" aria-modal="true">
            <img :src="src" :alt="alt" class="max-h-[85vh] max-w-[90vw] rounded bg-white object-contain p-2">
            <button type="button" @click="open = false"
                    class="absolute right-6 top-6 rounded-full bg-white/90 p-2 text-[#2f2f2f] transition hover:bg-white"
                    aria-label="{{ __('dental::public.detail') }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    </section>
@endif
