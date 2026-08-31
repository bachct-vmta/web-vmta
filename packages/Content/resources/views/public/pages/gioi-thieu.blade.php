@extends('site::layouts.public')

@section('content')
<div id="content" role="main" class="content-area scroll-smooth">

    {{-- Section 1: Hero --}}
    @php
        $tr = $heroSection?->translate(app()->getLocale());
        $heroTitle = strip_tags($tr?->title ?? 'VMTA – Kiến trúc sư trưởng cho hệ sinh thái du lịch việt nam', '<br>');
        $heroBody = trim((string) ($tr?->body ?? ''));
    @endphp
    <section id="section-hero" class="vmta-banner-hero relative min-h-[550px] sm-fs:min-h-[80vh] flex items-center justify-center overflow-hidden bg-white">
        <div class="absolute inset-0">
            <img src="{{ asset('images/about/8cae972b-1b32-4567-b3e9-d7348ea691af.png') }}"
                 class="vmta-bg-img w-full h-full object-cover"
                 fetchpriority="high" decoding="async" width="1344" height="768" alt="">
        </div>
        <div class="relative max-w-7xl mx-auto px-4 z-10">
            <div class="text-center text-[#0b7f7c]">
                <h1 class="font-sharp-bo fs-vmta-85 uppercase font-bold vmta-letter-spacing-0">
                    {!! $heroTitle !!}
                </h1>
                @if($heroBody !== '')
                    {{-- HTMLPurifier-sanitized in UpdateHomeSectionRequest::prepareForValidation --}}
                    <div class="cms-body font-utm-helve fs-vmta-25 uppercase font-bold mt-6 text-[#0b7f7c] max-w-[100%] md:max-w-[60%] mx-auto text-justify" style="text-align-last: center;">
                        {!! $heroBody !!}
                    </div>
                @endif
                <div class="pt-[30px] flex flex-wrap justify-center gap-3">
                    <a href="{{ $tr?->cta_link ?: '#section-who-are' }}"
                       class="inline-block rounded-md border border-[#0b7f7c] bg-white px-6 py-3 font-bold text-[#0b7f7c] uppercase hover:bg-[#0b7f7c] hover:text-white transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b7f7c]">
                        <span>{{ $tr?->cta_label ?? 'Khám Phá Hành trình' }}</span>
                    </a>
                    <a href="{{ $tr?->cta2_link ?: route('inquiry.' . app()->getLocale() . '.contact.show') }}"
                       class="inline-block rounded-md bg-[#d31e45] px-6 py-3 font-bold text-white uppercase hover:bg-[#b01838] transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#d31e45]">
                        <span>{{ $tr?->subtitle ?? 'tham gia hệ sinh thái' }}</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Section 2: VMTA Là ai --}}
    @php
        $tr    = $whoAreSection?->translate(app()->getLocale());
        $items = $tr?->items ?? [];
        $whoAreIcons = ['Asset-1@4x-2.png', 'Asset-2@4x-4.png', 'Asset-3@4x-3.png'];
        $whoAreBody = trim((string) ($tr?->body ?? ''));
    @endphp
    <section id="section-who-are" class="pt-[60px] md-fs:pt-[80px] pb-[60px] md-fs:pb-[80px] bg-white">
        <div class="relative max-w-7xl mx-auto px-4">
            <div class="flex flex-wrap items-center -mx-[15px]">
                <div class="w-full md-fs:w-1/2 px-[15px]">
                    <h2 class="font-sharp-bo fs-vmta-80 uppercase font-bold leading-[1.3] text-[#0b7f7c] mb-0 text-left vmta-letter-spacing-0">
                        {{ $tr?->title ?? 'VMTA Là ai ?' }}
                    </h2>
                    @if($whoAreBody !== '')
                        {{-- HTMLPurifier-sanitized in UpdateAboutSectionRequest::prepareForValidation --}}
                        <div class="cms-body font-utm-helve leading-[1.5] mt-4 max-w-none text-justify" style="text-align-last: left;">
                            {!! $whoAreBody !!}
                        </div>
                    @endif

                    <div class="pt-[15px]"></div>

                    @foreach($items as $i => $item)
                        <div class="flex items-start gap-4 text-left {{ $loop->last ? '' : 'mb-[15px]' }}">
                            <div class="w-[60px] flex-shrink-0">
                                <img src="{{ asset('images/about/' . ($whoAreIcons[$i] ?? 'Asset-1@4x-2.png')) }}"
                                     class="w-full h-auto" alt="" loading="lazy" width="213" height="213">
                            </div>
                            <div class="">
                                <p class="font-bold ">{{ $item['title'] ?? '' }}</p>
                                <p class="text-justify" style="text-align-last: left;">{{ $item['body'] ?? '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="w-full md-fs:w-1/2 px-[15px] mt-8 md-fs:mt-0">
                    @php
                        // Image 1 = VI, Image 2 = EN. Pick the right one per current locale.
                        $whoAreImage = app()->getLocale() === 'en'
                            ? media_url($whoAreSection?->image_2_media_id)
                            : media_url($whoAreSection?->image_1_media_id);
                    @endphp
                    <img src="{{ $whoAreImage ?: asset('images/about/Asset-1@2x.png') }}"
                         class="w-full h-auto" alt="" loading="lazy" width="1168" height="1227">
                </div>
            </div>
        </div>
    </section>

    {{-- Section 3: Giá trị cốt lõi --}}
    @php
        $tr         = $coreValuesSection?->translate(app()->getLocale());
        $items      = $tr?->items ?? [];
        $valueIcons = ['Asset-2@4x-300x300.png', 'Asset-3@4x-300x300.png', 'Asset-4@4x-300x300.png'];
    @endphp
    <section class="vmta-bg-filter-5 relative pt-[60px] md-fs:pt-[80px] pb-[60px] md-fs:pb-[80px] overflow-hidden bg-white">
        <div class="absolute inset-0">
            <img src="{{ asset('images/about/8cae972b-1b32-4567-b3e9-d7348ea691af.png') }}"
                 class="vmta-bg-img w-full h-full object-cover" alt="" loading="lazy">
        </div>
        <div class="relative max-w-7xl mx-auto px-4 z-10">
            <h2 class="font-sharp-bo fs-vmta-80 uppercase text-[#0b7f7c] text-center mb-0 vmta-letter-spacing-0">
                {{ $tr?->title ?? 'giá trị cốt lõi của vmta' }}
            </h2>
            <div class="pt-[20px] md-fs:pt-[80px]"></div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-[1.875rem] items-center">
                @foreach($items as $i => $item)
                    <div class="flex items-center gap-4 text-left">
                        <div class="w-[80px] md-fs:w-[120px] flex-shrink-0">
                            <img src="{{ asset('images/about/' . ($valueIcons[$i] ?? 'Asset-2@4x-300x300.png')) }}"
                                 class="w-full h-auto" alt="" loading="lazy" width="300" height="300">
                        </div>
                        <div class="">
                            <h3 class="text-[#d31e45] font-bold uppercase">{{ $item['title'] ?? '' }}</h3>
                            <p class="text-justify text-slate-700" style="text-align-last: left;">
                                {{ $item['body'] ?? '' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Section 4: Cách VMTA Hoạt Động --}}
    @php
        $tr        = $howWorksSection?->translate(app()->getLocale());
        $items     = $tr?->items ?? [];
        $stepIcons = ['Asset-4@4x-2-300x300.png', 'Asset-2@4x-3-300x300.png', 'Asset-3@4x-2-300x300.png', 'Asset-4@4x-2-300x300.png'];
    @endphp
    <section class="pt-[60px] md-fs:pt-[80px] pb-[60px] md-fs:pb-[80px] bg-white">
        <div class="relative max-w-7xl mx-auto px-4">
            <div class="text-center mb-8 md-fs:mb-[80px]">
                <h2 class="font-sharp-bo fs-vmta-80 uppercase text-[#0b7f7c] mb-0 vmta-letter-spacing-0">
                    {{ $tr?->title ?? 'Cách VMTA Hoạt Động' }}
                </h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-[1.875rem] items-stretch">
                @foreach($items as $i => $item)
                    <div class="text-center ">
                        <img src="{{ asset('images/about/' . ($stepIcons[$i] ?? 'Asset-4@4x-2-300x300.png')) }}"
                             class="w-[80px] h-auto mx-auto mb-4"
                             alt="" loading="lazy" width="300" height="300">
                        <p class="block w-full font-utm-helve text-center font-bold text-[#d31e45] mb-2">
                            {{ $item['title'] ?? '' }}
                        </p>
                        <p class="font-utm-helve text-slate-700 text-justify" style="text-align-last: center;">
                            {{ $item['body'] ?? '' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Section 5: Khác biệt của VMTA --}}
    @php
        $tr    = $differenceSection?->translate(app()->getLocale());
        $items = $tr?->items ?? [];
    @endphp
    <section class="relative min-h-[350px] sm-fs:min-h-[400px] flex items-center justify-start overflow-hidden py-[60px] bg-white">
        <div class="absolute inset-0">
            <img src="{{ asset('images/about/Asset-8-100.jpg') }}"
                 class="w-full h-full object-cover"
                 style="object-position: 50% 0%;"
                 alt="" loading="lazy" width="1440" height="556">
        </div>
        <div class="relative max-w-7xl w-full mx-auto px-4 z-10">
            <div class="max-w-3xl text-left">
                <h2 class="font-sharp-bo fs-vmta-80 uppercase font-bold leading-[1.3] text-[#0b7f7c] mb-6 vmta-letter-spacing-0 text-left">
                    {{ $tr?->title ?? 'KHÁC BIỆT CỦA VMTA' }}
                </h2>
                <div class="vmta-khacbiet font-utm-helve text-slate-700">
                    @foreach($items as $item)
                        <p>{{ $item['text'] ?? '' }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Section 6: Tại sao nên lựa chọn VMTA --}}
    @php
        $tr          = $whyChooseSection?->translate(app()->getLocale());
        $items       = $tr?->items ?? [];
        $chooseIcons = ['Asset-4@4x-300x300.png', 'Asset-6@4x-300x300.png', 'Asset-4@4x-3-300x300.png', 'Asset-5@4x-2-300x300.png'];
    @endphp
    <section class="pt-[60px] md-fs:pt-[80px] pb-[60px] md-fs:pb-[80px] bg-white">
        <div class="relative max-w-7xl mx-auto px-4">
            <div class="text-center mb-8 md-fs:mb-[80px]">
                <h2 class="font-sharp-bo fs-vmta-80 uppercase text-[#0b7f7c] mb-0 vmta-letter-spacing-0">
                    {{ $tr?->title ?? 'TẠI SAO NÊN LỰA CHỌN VMTA' }}
                </h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-[1.875rem] items-stretch">
                @foreach($items as $i => $item)
                    <div class="text-center">
                        <img src="{{ asset('images/about/' . ($chooseIcons[$i] ?? 'Asset-4@4x-300x300.png')) }}"
                             class="  w-[80px] h-auto mx-auto mb-4"
                             alt="" loading="lazy" width="300" height="300">
                        <p class="font-utm-helve uppercase font-bold text-[#d31e45] mb-2">
                            {{ $item['title'] ?? '' }}
                        </p>
                        <p class="font-utm-helve text-slate-700 text-justify" style="text-align-last: center;">
                            {{ $item['body'] ?? '' }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Section 7: Bắt đầu hành trình cùng VMTA --}}
    @php $tr = $startWithUsSection?->translate(app()->getLocale()); @endphp
    <section class="ss-start-with-us relative min-h-[400px] flex items-center overflow-hidden py-[80px] bg-white">
        <div class="absolute inset-0">
            <img src="{{ asset('images/about/Asset-7-100.jpg') }}"
                 class="w-full h-full object-cover"
                 style="object-position: 50% 10%;"
                 alt="" loading="lazy" width="1440" height="556">
        </div>
        <div class="relative max-w-7xl w-full mx-auto px-4 z-10">
            <div class="text-left max-w-3xl">
                <h2 class="font-sharp-bo fs-vmta-80 uppercase font-bold leading-[1.3] text-[#0b7f7c] mb-4 vmta-letter-spacing-0">
                    {{ $tr?->title ?? 'Bắt đầu hành trình cùng VMTA' }}
                </h2>
                @if($tr?->body)
                    <div class="cms-body font-utm-helve italic text-slate-700 mb-6 max-w-xl">
                        {!! $tr->body !!}
                    </div>
                @endif
                <div class="flex flex-wrap gap-3">
                    <a href="{{ $tr?->cta_link ?: route('inquiry.' . app()->getLocale() . '.contact.show') }}"
                       class="inline-block rounded-md border border-[#0b7f7c] bg-white px-6 py-3 font-bold text-[#0b7f7c] uppercase hover:bg-[#0b7f7c] hover:text-white transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b7f7c]">
                        <span>{{ $tr?->cta_label ?? 'NHẬN TƯ VẤN' }}</span>
                    </a>
                    <a href="{{ $tr?->cta2_link ?: route('inquiry.' . app()->getLocale() . '.contact.show') }}"
                       class="inline-block rounded-md bg-[#d31e45] px-6 py-3 font-bold text-white uppercase hover:bg-[#b01838] transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#d31e45]">
                        <span>{{ $tr?->subtitle ?? 'tham gia hệ sinh thái' }}</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

@endsection
