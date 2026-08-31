@extends('site::layouts.public')

@section('title', __('dental::public.breadcrumb.dental'))

{{--
    Danh sách cơ sở — Figma 23:309.

    Nhịp dọc theo thiết kế, tỉ lệ 1248/1563 ≈ 0.798: tiêu đề đầu cách dải hero 56 (→ 45),
    mọi khối sau đó cách nhau 49 (→ 39) — tiêu đề tới lưới, hàng tới hàng, lưới tới dots,
    dots tới đường kẻ, đường kẻ tới tiêu đề.
--}}

@section('content')
    @php
        $locale = app()->getLocale();
        $sections = $categories->filter(fn ($c) => $c->facilities->isNotEmpty());
    @endphp

    {{-- Một scope Alpine bọc cả hero lẫn các section để ô tìm kiếm điều khiển mọi danh sách --}}
    <div x-data="dentalFilter()">
        @include('dental::public._hero', [
            'breadcrumbs' => $breadcrumbs,
            'heroTitle' => __('dental::public.directory_title'),
            'heroImage' => config('dental.hero_image'),
            'filterModel' => 'query',
        ])

        <div class="mx-auto w-full max-w-7xl px-4 pb-[64px] pt-[45px]">
            @forelse($sections as $index => $category)
                @if($index > 0)
                    <hr class="my-[39px] border-t border-[#d9d9d9]">
                @endif

                @include('dental::public._facility-section', [
                    'title' => $category->translate($locale)?->name ?? $category->translations->first()?->name,
                    'items' => $category->facilities,
                ])
            @empty
                <p class="py-16 text-center text-[#8a8a8a]">{{ __('dental::public.empty') }}</p>
            @endforelse
        </div>
    </div>

    @include('dental::public._filter-script')
@endsection
