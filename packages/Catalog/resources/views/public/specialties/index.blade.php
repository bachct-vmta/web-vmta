@extends('site::layouts.public')

@php
    $locale = app()->getLocale();
    $routeBase = 'site.'.$locale.'.catalog.specialties';
    $heroImage = asset('images/home/hero/banner-bg.png');
    $cards = $specialties->map(function ($specialty) use ($locale, $routeBase) {
        $tr = $specialty->translations->firstWhere('locale', $locale) ?? $specialty->translations->first();
        $name = trim((string) ($tr?->name ?? ''));
        $slug = trim((string) ($tr?->slug ?? ''));

        if ($name === '' || $slug === '') {
            return null;
        }

        $iconPath = ltrim((string) $specialty->icon, '/');
        $iconUrl = $iconPath !== '' && file_exists(public_path('storage/'.$iconPath))
            ? asset('storage/'.$iconPath)
            : null;

        // Icon switch keys are based on VI slugs (stable identifier across locales).
        // EN slug differs (e.g., 'dentistry' vs 'nha-khoa') and would lose the icon match.
        $viTr = $specialty->translations->firstWhere('locale', 'vi');
        $iconKey = trim((string) ($viTr?->slug ?? $slug));

        return [
            'name' => $name,
            'url' => route($routeBase.'.show', ['slug' => $slug]),
            'search' => mb_strtolower($name.' '.$slug),
            'icon_url' => $iconUrl,
            'icon_key' => $iconKey,
        ];
    })->filter()->values();
@endphp

@push('head')
    <style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')
<section class="relative h-[300px] overflow-hidden bg-white">
    <img src="{{ $heroImage }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-25">
    <div class="absolute inset-0 bg-white/50"></div>
    <div class="relative mx-auto flex h-full max-w-7xl flex-col justify-end px-4 py-4">
        <h1 class="text-[2.7rem] font-bold leading-none text-[#0b7f7c] md:text-[3.15rem]">
            {{ mb_strtoupper(__('catalog::public.specialties.heading')) }}
        </h1>
        <nav class="mt-2 text-[1rem] text-[#0b7f7c]" aria-label="breadcrumb">
            <a href="{{ url('/'.$locale) }}" class="hover:underline">{{ __('catalog::public.specialties.breadcrumb_home') }}</a>
            <span class="mx-1">/</span>
            <span>{{ __('catalog::public.specialties.heading') }}</span>
        </nav>
    </div>
</section>

<section
    x-data="{ q: '' }"
    class="bg-white px-4 pb-20 pt-24"
>
    <div class="mx-auto mb-14 max-w-[830px]">
        <label class="relative block rounded-full bg-[#0b8f8a] shadow-sm">
            <span class="sr-only">{{ __('catalog::public.specialties.search_placeholder') }}</span>
            <input
                type="search"
                x-model="q"
                placeholder="{{ __('catalog::public.specialties.search_placeholder') }}"
                class="h-[58px] w-full rounded-full border-0 bg-transparent py-3 pl-16 pr-6 text-white placeholder:text-white/90 focus:ring-2 focus:ring-[#0b7f7c]/20"
            >
            <svg class="absolute left-6 top-1/2 h-5 w-5 -translate-y-1/2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
            </svg>
        </label>
    </div>

    @if($cards->isEmpty())
        <p class="py-16 text-center text-slate-500">{{ __('catalog::public.specialties.empty') }}</p>
    @else
        <div class="mx-auto grid max-w-7xl grid-cols-2 gap-x-10 gap-y-10 sm:grid-cols-3 lg:grid-cols-6">
            @foreach($cards as $card)
                <a
                    href="{{ $card['url'] }}"
                    x-show="!q || @js($card['search']).includes(q.toLowerCase())"
                    x-transition.opacity
                    class="group flex min-h-[150px] flex-col items-center text-center text-[#0b7f7c] transition hover:-translate-y-1"
                >
                    <span class="flex h-20 w-full max-w-[145px] items-center justify-center rounded-2xl bg-[#0b8f8a] text-white transition group-hover:bg-[#0b7f7c]">
                        @if($card['icon_url'])
                            <img src="{{ $card['icon_url'] }}" alt="" class="h-12 w-12 object-contain brightness-0 invert">
                        @else
                            @switch($card['icon_key'])
                                @case('nha-khoa')
                                    <svg class="h-12 w-12" viewBox="0 0 64 64" aria-hidden="true" fill="currentColor">
                                        <path d="M20.5 6C13.8 6 9 11.7 9 19.2c0 4.8 2 9.1 4.2 13.6 2.2 4.6 2.6 9.8 3.1 14.9.5 5.4 1.3 10.3 5.1 10.3 3.2 0 4.3-4.1 5.8-9.8 1.1-4 2.3-8.5 4.8-8.5s3.7 4.5 4.8 8.5c1.5 5.7 2.6 9.8 5.8 9.8 3.8 0 4.6-4.9 5.1-10.3.5-5.1.9-10.3 3.1-14.9C53 28.3 55 24 55 19.2 55 11.7 50.2 6 43.5 6c-3.9 0-6.8 1.4-9.3 2.6-1 .5-1.8.8-2.2.8s-1.2-.3-2.2-.8C27.3 7.4 24.4 6 20.5 6Z"/>
                                    </svg>
                                    @break
                                @case('phu-san')
                                @case('san-phu-khoa')
                                    <svg class="h-12 w-12" viewBox="0 0 64 64" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M34 13a18 18 0 1 0 0 36"/>
                                        <path d="M26 18c10 3 16 10 18 21"/>
                                        <path d="M43 16l9 9-11 4"/>
                                    </svg>
                                    @break
                                @case('tim-mach')
                                    <svg class="h-12 w-12" viewBox="0 0 64 64" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M32 53S10 39 10 23c0-8 5-13 12-13 5 0 8 3 10 7 2-4 5-7 10-7 7 0 12 5 12 13 0 16-22 30-22 30Z"/>
                                        <path d="M15 33h9l5-12 7 22 5-10h8"/>
                                    </svg>
                                    @break
                                @case('noi-tiet-to')
                                    <svg class="h-12 w-12" viewBox="0 0 64 64" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 11c18 8 10 34 28 42"/>
                                        <path d="M46 11C28 19 36 45 18 53"/>
                                        <path d="M21 20h22M21 32h22M21 44h22"/>
                                    </svg>
                                    @break
                                @case('vat-ly-tri-lieu')
                                    <svg class="h-12 w-12" viewBox="0 0 64 64" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16 40h32M19 40v10M45 40v10"/>
                                        <path d="M32 16a7 7 0 1 0 0 14 7 7 0 0 0 0-14Z"/>
                                        <path d="M22 35c4-5 16-5 20 0M24 19l-7 7M40 19l7 7"/>
                                    </svg>
                                    @break
                                @case('tao-hinh-tham-my')
                                    <svg class="h-12 w-12" viewBox="0 0 64 64" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 24c2-10 8-16 14-16s12 6 14 16"/>
                                        <path d="M17 27c0 17 8 29 15 29s15-12 15-29"/>
                                        <path d="M24 36h.1M40 36h.1M27 47c3 2 7 2 10 0"/>
                                    </svg>
                                    @break
                                @case('nam-khoa')
                                    <svg class="h-12 w-12" viewBox="0 0 64 64" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="25" cy="39" r="15"/>
                                        <path d="M36 28 52 12M42 12h10v10"/>
                                    </svg>
                                    @break
                                @case('mat')
                                    <svg class="h-12 w-12" viewBox="0 0 64 64" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 32s10-16 26-16 26 16 26 16-10 16-26 16S6 32 6 32Z"/>
                                        <circle cx="32" cy="32" r="8"/>
                                    </svg>
                                    @break
                                @case('vo-sinh-hiem-muon')
                                    <svg class="h-12 w-12" viewBox="0 0 64 64" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="32" cy="24" r="12"/>
                                        <path d="M32 36v19M23 46h18"/>
                                        <path d="M45 11l8-8M48 3h5v5"/>
                                    </svg>
                                    @break
                                @case('co-xuong-khop')
                                    <svg class="h-12 w-12" viewBox="0 0 64 64" aria-hidden="true" fill="currentColor">
                                        <path d="M24 5c-7 0-12 6-12 14 0 17 12 35 20 40 8-5 20-23 20-40 0-8-5-14-12-14-4 0-7 2-8 5-1-3-4-5-8-5Zm4 16h8v9h9v8h-9v9h-8v-9h-9v-8h9v-9Z"/>
                                    </svg>
                                    @break
                                @case('y-hoc-co-truyen')
                                    <svg class="h-12 w-12" viewBox="0 0 64 64" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16 48h32l4-20H12l4 20Z"/>
                                        <path d="M20 28c2-8 8-14 16-16 0 8-6 14-16 16Z"/>
                                        <path d="M34 27c4-7 10-10 18-9-2 8-8 12-18 9Z"/>
                                    </svg>
                                    @break
                                @case('nhi-khoa')
                                    <svg class="h-12 w-12" viewBox="0 0 64 64" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="32" cy="32" r="20"/>
                                        <path d="M22 31h.1M42 31h.1M24 41c5 5 11 5 16 0"/>
                                        <path d="M16 28c-7 1-7 11 0 12M48 28c7 1 7 11 0 12"/>
                                    </svg>
                                    @break
                                @default
                                    <svg class="h-12 w-12" viewBox="0 0 64 64" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M32 9v46M9 32h46"/>
                                        <circle cx="32" cy="32" r="23"/>
                                    </svg>
                            @endswitch
                        @endif
                    </span>
                    <span class="mt-4 text-[1.125rem] font-medium uppercase leading-tight">
                        {{ $card['name'] }}
                    </span>
                </a>
            @endforeach
        </div>

        <p
            x-show="q && !@js($cards->pluck('search')->values()).some(n => n.includes(q.toLowerCase()))"
            x-cloak
            class="py-10 text-center text-slate-500"
        >
            {{ __('catalog::public.specialties.empty_search') }}
        </p>
    @endif
</section>
@endsection
