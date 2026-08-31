@extends('site::layouts.public')

@section('title', $translation->name)

{{--
    Chi tiết cơ sở — Figma 28:767.

    Tỉ lệ 1248/1563 ≈ 0.798: tiêu đề "Dịch vụ" cách dải hero 57 (→ 45), lưới cách tiêu đề
    49 (→ 39) với cột 204px cách nhau 47 (16 khi dưới md), hàng cách 39, khối chứng nhận
    cách lưới 98 (→ 78).
--}}

@section('content')
    @php
        $locale = app()->getLocale();

        $serviceFilterText = function ($service) use ($locale) {
            $t = $service->translate($locale) ?? $service->translations->first();

            return \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) ($t->title ?? '')));
        };
    @endphp

    <div x-data="dentalFilter()">
        @include('dental::public._hero', [
            'breadcrumbs' => $breadcrumbs,
            'heroTitle' => $translation->name,
            'heroImage' => config('dental.hero_image'),
            'filterModel' => 'query',
        ])

        <div class="mx-auto w-full max-w-7xl px-4 pb-[64px] pt-[45px]">
            <h2 class="m-0 text-center text-[22px] font-bold leading-[27px] text-[#5d5d5d]">
                {{ __('dental::public.services') }}
            </h2>

            @if($services->isEmpty())
                <p class="mt-[39px] text-center text-[#8a8a8a]">{{ __('dental::public.empty') }}</p>
            @else
                <div class="mt-[39px] grid justify-center gap-x-4 gap-y-6 md:gap-x-[47px] md:gap-y-[39px] [grid-template-columns:repeat(auto-fit,204px)]">
                    @foreach($services as $service)
                        <div data-filter-text="{{ $serviceFilterText($service) }}"
                             x-show="matches($el.dataset.filterText)">
                            @include('dental::public._service-card', [
                                'service' => $service,
                                'facilitySlug' => $translation->slug,
                            ])
                        </div>
                    @endforeach
                </div>
            @endif

            @include('dental::public._certificates', ['certificates' => $certificates])
        </div>
    </div>

    @include('dental::public._filter-script')
@endsection
