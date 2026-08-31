@extends('site::layouts.public')

@section('title', $translation->title)

{{--
    Chi tiết dịch vụ — Figma 39:36.

    Nhịp dọc theo thiết kế, tỉ lệ 1248/1563 ≈ 0.798: hàng video cách dải hero 78 (→ 62),
    khối so sánh +79 (→ 63), bảng giá +37 (→ 30), CTA +79 (→ 63). Video và sidebar cách
    nhau 32 (→ 26), chỉ nằm cạnh nhau từ xl: dưới đó sidebar cố định 409 sẽ ép video hẹp
    quá, không giữ được tỉ lệ 2:1 của thiết kế.
--}}

@section('content')
    @include('dental::public._hero', [
        'breadcrumbs' => $breadcrumbs,
        'heroTitle' => $heroTitle,
        'heroImage' => config('dental.hero_image'),
    ])

    <div class="mx-auto w-full max-w-7xl px-4 pb-[64px] pt-[62px]">
        <div class="flex flex-col gap-[26px] xl:flex-row">
            @include('dental::public._video', [
                'videoUrl' => $service->video_url,
                'poster' => $service->video_poster_url,
                'caption' => $translation->video_caption,
            ])

            @include('dental::public._news-sidebar', ['posts' => $posts])
        </div>

        @if(trim((string) $translation->body) !== '')
            <div class="mt-[63px] text-[14px] leading-[1.75] text-[#5d5d5d] [&_p]:mb-4 [&_p:last-child]:mb-0 [&_ul]:mt-3 [&_ul]:list-disc [&_ul]:pl-5 [&_li]:mt-1">
                {!! clean($translation->body, 'post_body') !!}
            </div>
        @endif

        <div class="mt-[63px]">
            @include('dental::public._rich-table', [
                'html' => $translation->comparison_html,
                'variant' => 'comparison',
            ])
        </div>

        <div class="mt-[30px]">
            @include('dental::public._rich-table', [
                'html' => $translation->price_table_html,
                'variant' => 'price',
            ])
        </div>
    </div>

    {{-- Cuối trang, ngoài mọi khối overflow-hidden, để lớp phủ fixed không bị cắt --}}
    <div class="mx-auto w-full max-w-7xl px-4 pb-[64px]">
        @include('dental::public._cta-modal', [
            'refType' => 'dental_service',
            'refId' => $service->id,
        ])
    </div>
@endsection
