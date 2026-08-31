@extends('site::layouts.public')

@section('content')
@php
    $locale = app()->getLocale();
    $routeBase = 'site.'.$locale.'.catalog.services';
@endphp
<section class="max-w-6xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-semibold mb-8">{{ __('catalog::public.services.heading') }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-[260px_1fr] gap-8">
        {{-- Filter sidebar --}}
        <aside class="space-y-6 text-sm">
            <h2 class="font-medium">{{ __('catalog::public.filters.heading') }}</h2>
            <form method="GET" class="space-y-4">
                <div>
                    <label class="block font-medium mb-1">{{ __('catalog::public.filters.specialty') }}</label>
                    <select name="filter[specialty]" class="w-full rounded border-slate-300">
                        <option value="">{{ __('catalog::public.filters.all') }}</option>
                        @foreach($specialties as $sp)
                            @php $tr = $sp->translations->firstWhere('locale', $locale) ?? $sp->translations->first(); @endphp
                            <option value="{{ $tr?->slug }}" @selected(request('filter.specialty') === $tr?->slug)>{{ $tr?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-medium mb-1">{{ __('catalog::public.filters.destination') }}</label>
                    <select name="filter[destination]" class="w-full rounded border-slate-300">
                        <option value="">{{ __('catalog::public.filters.all') }}</option>
                        @foreach($destinations as $d)
                            @php $tr = $d->translations->firstWhere('locale', $locale) ?? $d->translations->first(); @endphp
                            <option value="{{ $tr?->slug }}" @selected(request('filter.destination') === $tr?->slug)>{{ $tr?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="number" name="filter[price_min]" min="0" value="{{ request('filter.price_min') }}" placeholder="{{ __('catalog::public.filters.price_min') }}" class="rounded border-slate-300">
                    <input type="number" name="filter[price_max]" min="0" value="{{ request('filter.price_max') }}" placeholder="{{ __('catalog::public.filters.price_max') }}" class="rounded border-slate-300">
                </div>
                <div class="flex gap-2">
                    <button class="flex-1 rounded bg-blue-600 hover:bg-blue-700 text-white py-2">{{ __('catalog::public.filters.apply') }}</button>
                    <a href="{{ route($routeBase.'.index') }}" class="rounded bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2">{{ __('catalog::public.filters.clear') }}</a>
                </div>
            </form>
        </aside>

        {{-- Results --}}
        <div>
            @if($services->isEmpty())
                <p class="text-slate-500">{{ __('catalog::public.services.empty') }}</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @foreach($services as $svc)
                        @php
                            $tr = $svc->translations->firstWhere('locale', $locale) ?? $svc->translations->first();
                            $ptr = $svc->partner?->translations->firstWhere('locale', $locale) ?? $svc->partner?->translations->first();
                        @endphp
                        <article class="rounded border border-slate-200 p-4 hover:shadow transition">
                            @if($svc->is_featured)
                                <span class="text-xs uppercase tracking-wide text-amber-600">★ {{ __('catalog::catalog.item_fields.is_featured') }}</span>
                            @endif
                            <h2 class="mt-1 text-lg font-medium">
                                <a href="{{ route($routeBase.'.show', ['slug' => $tr?->slug]) }}" class="hover:underline">{{ $tr?->title }}</a>
                            </h2>
                            @if($ptr?->name)
                                <p class="text-xs text-slate-500 mt-1">{{ $ptr->name }}</p>
                            @endif
                            @if($tr?->excerpt)
                                <p class="mt-2 text-sm text-slate-600 line-clamp-3">{{ $tr->excerpt }}</p>
                            @endif
                            @if($svc->price_from)
                                <p class="mt-3 text-sm font-medium text-blue-700">{{ __('catalog::public.detail.price_from') }} {{ number_format((float) $svc->price_from, 0) }} {{ $svc->currency }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
                <div class="mt-8">{{ $services->links() }}</div>
            @endif
        </div>
    </div>
</section>
@endsection
