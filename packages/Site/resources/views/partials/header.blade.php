@php
    $locale = app()->getLocale();
    $logoUrl = media_url((int) setting('site.logo_media_id'), asset('images/home/header/logo-vmta.png'));
@endphp
<header class=" bg-white sticky top-0 z-50 transition-shadow"
        x-data="{ mobileOpen: false, scrolled: false }"
        x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 10 })"
        :class="scrolled ? 'shadow-md' : ''">

    <div class="relative mx-auto flex items-center max-w-7xl justify-center md:justify-start px-4">
        {{-- Logo --}}
        <a href="{{ url($locale) }}" class="flex-shrink-0">
            <img src="{{ $logoUrl }}" alt="VMTA" class="vmta-header-logo">
        </a>

        {{-- Desktop nav --}}
        <nav class="hidden mx-auto md:flex items-center gap-5 text-[0.9rem] font-semibold uppercase text-vmta-teal tracking-wide [&_a]:hover:text-vmta-red">
            @include('content::public.partials.menu', ['location' => 'main_navigation', 'cssClass' => 'flex items-center gap-10'])
        </nav>

        {{-- Desktop: lang switcher + JOIN CTA --}}
        <div class="hidden md:flex items-center gap-4">
            @include('site::partials.lang-switcher')
            <a href="{{ route('inquiry.' . $locale . '.contact.show') }}"
               class="rounded-md bg-vmta-teal px-5 py-2 text-white text-sm font-bold uppercase hover:opacity-90 transition">
                {{ __('site::site.header.join_cta') }}
            </a>
        </div>

        {{-- Mobile hamburger --}}
        <button type="button" class="absolute left-[18px] md:hidden text-slate-700 hover:text-vmta-teal" @click="mobileOpen = !mobileOpen" aria-label="Menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

    </div>

    {{-- Mobile sidebar backdrop --}}
    <div x-show="mobileOpen"
         x-cloak
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileOpen = false"
         class="fixed inset-0 z-40 bg-black/50 md:hidden"
         aria-hidden="true">
    </div>

    {{-- Mobile sidebar panel --}}
    <div x-show="mobileOpen"
         x-cloak
         x-transition:enter="transition ease-in-out duration-300 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in-out duration-300 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-white shadow-2xl md:hidden">

        {{-- Sidebar header --}}
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <a href="{{ url($locale) }}" @click="mobileOpen = false">
                <img src="{{ $logoUrl }}" alt="VMTA" class="h-10 w-auto">
            </a>
            <button type="button"
                    @click="mobileOpen = false"
                    class="rounded-md p-1.5 text-slate-500 hover:bg-slate-100 hover:text-vmta-teal transition"
                    aria-label="Đóng menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav items --}}
        <nav class="flex-1 overflow-y-auto py-2">
            @include('content::public.partials.menu', [
                'location' => 'main_navigation',
                'cssClass' => 'block text-[0.9rem] font-semibold uppercase tracking-wide text-vmta-teal [&_a]:block [&_a]:px-5 [&_a]:py-3.5 [&_a:hover]:bg-slate-50 [&_a:hover]:text-vmta-red [&_li]:border-b [&_li]:border-slate-100',
                'ulClass'  => 'flex flex-col',
                'mode'     => 'mobile',
            ])
        </nav>

        {{-- Sidebar footer: lang + CTA --}}
        <div class="border-t border-slate-100 px-5 py-4 flex items-center justify-between gap-3">
            @include('site::partials.lang-switcher')
            <a href="{{ route('inquiry.' . $locale . '.contact.show') }}"
               @click="mobileOpen = false"
               class="rounded-md bg-vmta-teal px-4 py-2 text-white text-sm font-bold uppercase hover:opacity-90 transition">
                {{ __('site::site.header.join_cta') }}
            </a>
        </div>
    </div>
</header>
