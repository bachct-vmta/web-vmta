@extends('site::layouts.public')

@section('content')
@php
    $locale = app()->getLocale();
    $routeBase = 'site.'.$locale.'.catalog.combos';
@endphp
<section class="max-w-6xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-semibold mb-8">{{ __('catalog::public.combos.heading') }}</h1>

    <form method="GET" class="mb-6 flex flex-wrap gap-2 text-sm">
        <input type="number" name="filter[price_min]" min="0" value="{{ request('filter.price_min') }}" placeholder="{{ __('catalog::public.filters.price_min') }}" class="rounded border-slate-300">
        <input type="number" name="filter[price_max]" min="0" value="{{ request('filter.price_max') }}" placeholder="{{ __('catalog::public.filters.price_max') }}" class="rounded border-slate-300">
        <input type="number" name="filter[discount_min]" min="0" max="100" value="{{ request('filter.discount_min') }}" placeholder="{{ __('catalog::public.detail.discount') }} %" class="rounded border-slate-300">
        <button class="rounded bg-blue-600 hover:bg-blue-700 text-white px-4 py-2">{{ __('catalog::public.filters.apply') }}</button>
        <a href="{{ route($routeBase.'.index') }}" class="rounded bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2">{{ __('catalog::public.filters.clear') }}</a>
    </form>

    @if($combos->isEmpty())
        <p class="text-slate-500">{{ __('catalog::public.combos.empty') }}</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($combos as $combo)
                @php $tr = $combo->translations->firstWhere('locale', $locale) ?? $combo->translations->first(); @endphp
                <article class="rounded border border-slate-200 p-4 hover:shadow transition">
                    @if($combo->discount_percent)
                        <span class="inline-block text-xs font-medium bg-red-100 text-red-700 px-2 py-1 rounded">-{{ (int) $combo->discount_percent }}%</span>
                    @endif
                    <h2 class="mt-2 text-lg font-medium">
                        <a href="{{ route($routeBase.'.show', ['slug' => $tr?->slug]) }}" class="hover:underline">{{ $tr?->title }}</a>
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ $combo->services->count() }} {{ __('catalog::public.detail.includes_services') }} ·
                        {{ $combo->tourPackages->count() }} {{ __('catalog::public.detail.includes_tours') }}
                    </p>
                    @if($tr?->excerpt)
                        <p class="mt-2 text-sm text-slate-600 line-clamp-3">{{ $tr->excerpt }}</p>
                    @endif
                    @if($combo->price_from)
                        <p class="mt-3 text-sm font-medium text-blue-700">{{ __('catalog::public.detail.price_from') }} {{ number_format((float) $combo->price_from, 0) }} {{ $combo->currency }}</p>
                    @endif
                </article>
            @endforeach
        </div>
        <div class="mt-8">{{ $combos->links() }}</div>
    @endif
</section>
@endsection
