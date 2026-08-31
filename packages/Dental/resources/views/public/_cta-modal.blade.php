{{--
    Nút CTA tư vấn và dialog nó mở ra.

    Nút và dialog dùng chung một scope Alpine để trigger lật được `open`. Render ở cuối trang,
    ngoài mọi khối overflow-hidden, để lớp phủ fixed không bị cắt.

    Biến: $refType, $refId
--}}
<div x-data="{
        open: false,
        show() { this.open = true; this.$nextTick(() => this.$refs.panel?.querySelector('input, textarea')?.focus()); },
        hide() { this.open = false; this.$refs.trigger?.focus(); },
     }"
     @keydown.escape.window="open && hide()">

    {{-- Figma 409x96 radius 10 nền #d31e45 28px/700 → 327x77 radius 8, 22px --}}
    <div class="text-center">
        <button type="button" x-ref="trigger" @click="show()"
                class="mx-auto flex h-[77px] w-full max-w-[327px] items-center justify-center rounded-[8px] bg-vmta-red px-5 text-[clamp(1rem,1.3vw,22px)] font-bold uppercase leading-[27px] text-white transition hover:brightness-110">
            {{ __('dental::public.cta_consult') }}
        </button>
    </div>

    <div x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4"
         @click.self="hide()"
         role="dialog" aria-modal="true" aria-labelledby="dental-cta-title">

        <div x-ref="panel" x-show="open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">

            <button type="button" @click="hide()"
                    class="absolute right-4 top-4 rounded-full p-1.5 text-[#8a8a8a] transition hover:bg-gray-100 hover:text-[#2f2f2f]"
                    aria-label="{{ __('dental::public.close') }}">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>

            <h2 id="dental-cta-title" class="m-0 mb-1 pr-8 text-[1.25rem] font-bold text-vmta-teal">
                {{ __('dental::public.cta_consult') }}
            </h2>
            <p class="mb-4 text-[0.875rem] text-[#6a6a6a]">{{ __('dental::public.cta_modal_subtitle') }}</p>

            @include('inquiry::public.quick-inquiry-form', [
                'refType' => $refType,
                'refId' => $refId,
            ])
        </div>
    </div>
</div>
