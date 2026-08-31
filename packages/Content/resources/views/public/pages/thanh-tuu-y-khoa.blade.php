@extends('site::layouts.public')

@section('content')
@php
    $asset = fn (string $file) => asset('images/medical-achievements/' . $file);
    $locale = app()->getLocale();
    $contactUrl = route('inquiry.' . $locale . '.contact.show');
    $serviceUrl = \Illuminate\Support\Facades\Route::has('site.' . $locale . '.catalog.services.index')
        ? route('site.' . $locale . '.catalog.services.index')
        : '#';

    /** Reusable utility class strings (Tailwind v4, no CSS file). */
    $container = 'mx-auto w-[min(100%-2rem,1280px)]';
    $h1Hero = 'font-sharp-bo font-bold uppercase leading-[1.28] tracking-normal text-vmta-teal m-0 mb-[1.35rem] text-[clamp(2.05rem,3.4vw,3.45rem)]';
    $h2Section = 'font-sharp-bo font-bold uppercase leading-[1.28] tracking-normal text-vmta-teal m-0 text-[clamp(1.8rem,3vw,3.25rem)]';
    $h2Case = 'font-sharp-bo font-bold uppercase leading-[1.28] tracking-normal text-vmta-teal m-0 text-[clamp(1.65rem,2.4vw,2.45rem)]';
    $btnBase = 'inline-flex items-center justify-center min-h-[2.8rem] px-[1.35rem] py-3 rounded-[.35rem] font-bold uppercase leading-none no-underline transition hover:opacity-90';
    $btnOutline = "$btnBase border border-vmta-teal bg-white text-vmta-teal";
    $btnRed = "$btnBase border border-vmta-red bg-vmta-red text-white";
    $btnWhite = "$btnBase border border-vmta-teal bg-white text-vmta-teal";

    /* Card classes for case columns + capabilities + assurance (user tweaked: no border-r) */
    $valueGridClass = 'mt-[4.25rem] grid grid-cols-3 gap-10 max-[768px]:grid-cols-1';
    $valueCardClass = 'min-w-0 max-[768px]:pr-0 text-center';
    $valueIconClass = 'mb-4 h-[120px] w-[120px] mx-auto object-contain';
    $valueTitleClass = 'mb-2 font-bold uppercase leading-[1.25] text-vmta-red';
    $valueListClass = 'mt-2 list-none space-y-2 pl-0 text-base leading-[1.45] text-[#111]';
    $valueListItemClass = 'flex items-start justify-center gap-2.5 after:hidden after:[content:none]';
    $valueBadgeClass = 'mt-[0.55em] h-2 w-2 shrink-0 rounded-full bg-vmta-teal';
    $valueBadgeClass = '';
    $valueBodyClass = 'text-base leading-[1.45] text-justify';
    $capabilityGridClass = 'mt-[4.8rem] grid grid-cols-4 gap-10 max-[1024px]:grid-cols-2 max-[768px]:mt-10 max-[768px]:grid-cols-1';
    $capabilityCardClass = 'min-w-0 px-2 text-center';
    $capabilityIconClass = 'mx-auto mb-4 h-[120px] w-[120px] object-contain';
    $capabilityTitleClass = 'mb-2 font-bold uppercase leading-[1.25] text-vmta-red';
    $capabilityBodyClass = 'text-base leading-[1.45] text-justify';
    $assuranceGridClass = 'mt-[4.8rem] grid grid-cols-4 gap-10 max-[1024px]:grid-cols-2 max-[768px]:mt-10 max-[768px]:grid-cols-1';
    $assuranceItemClass = 'flex min-w-0 items-center gap-4 pr-10 max-[768px]:pr-0';
    $assuranceIconClass = 'h-[60px] w-[60px] shrink-0 object-contain';
    $assuranceBodyClass = 'text-base leading-[1.45] text-justify';

    /** Resolve media url (id → MediaFile.permalink) with static fallback. */
    $mediaUrl = function (?int $id, string $fallbackPath) use ($asset) {
        if ($id) {
            $m = \Packages\Core\Src\Models\MediaFile::find($id);
            if ($m && $m->permalink) {
                return $m->permalink;
            }
        }
        return $asset($fallbackPath);
    };

    /** Resolve item icon: icon_media_id > icon_path > hard fallback. */
    $itemIcon = function (array $item, string $hardFallback) use ($asset, $mediaUrl) {
        if (! empty($item['icon_media_id'])) {
            return $mediaUrl($item['icon_media_id'], $hardFallback);
        }
        if (! empty($item['icon_path'])) {
            return $asset($item['icon_path']);
        }
        return $asset($hardFallback);
    };

    $hero = $sections->get(\Packages\Content\Src\Enums\AchievementSectionPosition::Hero->value);
    $caps = $sections->get(\Packages\Content\Src\Enums\AchievementSectionPosition::Capabilities->value);
    $assur = $sections->get(\Packages\Content\Src\Enums\AchievementSectionPosition::Assurance->value);

    $heroT  = $hero?->translate($locale);
    $capsT  = $caps?->translate($locale);
    $assurT = $assur?->translate($locale);

    $heroImage = $hero?->image?->permalink ?? $asset('hero-operating-room.jpg');
    $heroTitle = $heroT?->title;
    $heroEyebrow = $heroT?->subtitle;
    $heroBody = trim((string) ($heroT?->body ?? ''));

    $stats = is_array($heroT?->items) ? $heroT->items : [];

    // Config provides VN titles; override per locale.
    $caseIcons = config('content.medical_case_columns', []);
    if ($locale === 'en') {
        $caseIconTitlesEn = [
            'icon-breakthrough.png'   => 'BREAKTHROUGH SOLUTION',
            'icon-effectiveness.png'  => 'OUTSTANDING RESULTS',
            'icon-meaning.png'        => 'MEANING',
        ];
        $caseIcons = array_map(function ($col) use ($caseIconTitlesEn) {
            $col['title'] = $caseIconTitlesEn[$col['icon_path'] ?? ''] ?? ($col['title'] ?? '');
            return $col;
        }, $caseIcons);
    }

    $capabilities = is_array($capsT?->items) ? $capsT->items : [];
    $assurance = is_array($assurT?->items) ? $assurT->items : [];

    // Hardcoded section icons (index-based) — locale-agnostic, NOT in DB.
    // Admin form doesn't expose icon_path → keeping them here prevents data loss on save.
    $heroStatIcons = ['stat-heart.png', 'stat-hospital.png', 'stat-people.png'];
    $capabilityIcons = ['icon-doctors.png', 'icon-technique.png', 'icon-modern-tech.png', 'icon-trust-destination.png'];
    $assuranceIcons = ['icon-plan.png', 'icon-select.png', 'icon-coordinate.png', 'icon-quality.png'];
@endphp

<div id="content" role="main" class="font-utm-helve text-[#111] bg-white">
    {{-- Hero --}}
    <section class="relative isolate overflow-hidden bg-white min-h-[535px] after:absolute after:inset-0 after:content-[''] after:bg-[linear-gradient(90deg,#fff_0%,#fff_38%,rgba(255,255,255,0.88)_46%,rgba(255,255,255,0.22)_62%,rgba(255,255,255,0)_100%)] max-[1024px]:after:bg-[rgba(255,255,255,0.72)]">
        <img src="{{ $heroImage }}" alt=""
             class="absolute right-0 top-0 h-[500px] w-[58%] object-cover object-center max-[1024px]:w-full max-[1024px]:opacity-[0.35]"
             fetchpriority="high" width="1440" height="644">
        <div class="{{ $container }} relative z-10 grid grid-cols-[minmax(0,0.88fr)_minmax(520px,1fr)] gap-10 items-end min-h-[535px] py-16 pb-0 max-[1024px]:grid-cols-1 max-[1024px]:items-center max-[768px]:min-h-0 max-[768px]:gap-8 max-[768px]:py-14 max-[768px]:pb-10">
            <div class="self-center max-w-[740px]">
                <h1 class="{{ $h1Hero }}">{!! $heroTitle ? nl2br(e($heroTitle)) : 'Thành tựu Y khoa<br>tiêu biểu tại Việt Nam' !!}</h1>
                @if($heroEyebrow)
                    <p class="text-vmta-red font-bold uppercase mb-5 text-[1.03rem] leading-[1.58]">{{ $heroEyebrow }}</p>
                @endif
                @if($heroBody !== '')
                    {{-- HTMLPurifier-sanitized in UpdateAchievementSectionRequest::prepareForValidation --}}
                    <div class="cms-body text-[1.03rem] leading-[1.58] text-justify" style="text-align-last: left;">
                        {!! $heroBody !!}
                    </div>
                @else
                    <p class="m-0 mb-5 text-[1.03rem] leading-[1.58] text-justify" style="text-align-last: left;">Việt Nam đang từng bước khẳng định vị thế trên bản đồ y khoa thế giới.</p>
                @endif
            </div>
            <div class="grid grid-cols-3 gap-[2.2rem] items-start -mb-4 px-[2.2rem] py-8 pb-[1.8rem] rounded-t-[1.25rem] bg-white shadow-[0_8px_22px_rgba(11,127,124,0.28)] max-[768px]:grid-cols-1 max-[768px]:gap-6 max-[768px]:p-6 max-[768px]:mb-0">
                @foreach($stats as $i => $stat)
                    <div class="max-[768px]:grid max-[768px]:grid-cols-[58px_1fr] max-[768px]:items-center max-[768px]:gap-x-4">
                        <img src="{{ $asset($heroStatIcons[$i] ?? 'stat-heart.png') }}" alt="" width="90" height="90"
                             class="w-[60px] h-[60px] object-contain mb-[0.65rem] max-[768px]:row-span-2 max-[768px]:m-0">
                        <strong class="block text-vmta-red font-sharp-bo text-[clamp(2rem,3.2vw,3.4rem)] leading-none">{{ $stat['value'] ?? '' }}</strong>
                        <span class="block mt-[0.35rem] text-[0.9rem] leading-[1.35]">{!! nl2br(e($stat['label'] ?? '')) !!}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Cases --}}
    @foreach($cases as $case)
        @php
            $caseT = $case->translate($locale);
            $caseImage = $case->image?->permalink ?? $asset('heart-lung-transplant.jpg');

            // CTA target — link to the case detail page when a route exists for it.
            // Currently only the heart-lung case has a dedicated detail page (showHeartLung).
            $caseDetailRoutes = [
                'ghep-dong-thoi-tim-phoi' => 'content.medical-achievements.heart-lung',
            ];
            $detailRouteSuffix = $caseDetailRoutes[$case->slug] ?? null;
            $detailUrl = $detailRouteSuffix && \Illuminate\Support\Facades\Route::has("site.{$locale}.{$detailRouteSuffix}")
                ? route("site.{$locale}.{$detailRouteSuffix}")
                : '#'.$case->slug;
            $col1Items = is_array($caseT?->col1_items) ? $caseT->col1_items : [];
            $col2Items = is_array($caseT?->col2_items) ? $caseT->col2_items : [];
            $col3Body = $caseT?->col3_body;
            $caseGridCols = $case->reverse ? 'grid-cols-[1fr_1.35fr]' : 'grid-cols-[1.35fr_1fr]';
            $copyOrder = $case->reverse ? 'order-2 max-[1024px]:order-none' : '';
        @endphp
        <section id="{{ $case->slug }}" class="py-22 max-[768px]:py-14">
            <div class="{{ $container }}">
                <div class="grid {{ $caseGridCols }} gap-16 items-center max-[1024px]:grid-cols-1">
                    <div class="{{ $copyOrder }}">
                        <h2 class="{{ $h2Case }}">{{ $caseT?->title }}</h2>
                        <p class="mt-4 mb-0 font-bold uppercase text-[#4a4a4a] text-[clamp(1rem,1.4vw,1.18rem)] text-justify leading-[1.42]" style="text-align-last: left;">{{ $caseT?->subtitle }}</p>
                        <p class="mt-4 mb-0 uppercase text-justify leading-[1.42]" style="text-align-last: left;">{{ $caseT?->intro }}</p>
                    </div>
                    <img src="{{ $caseImage }}" alt="" loading="lazy" width="1010" height="605"
                         class="w-full rounded-[1.25rem] object-cover aspect-[1010/605]">
                </div>

                <div class="{{ $valueGridClass }}">
                    @php
                        $columns = [
                            ['items' => $col1Items],
                            ['items' => $col2Items],
                            ['body'  => $col3Body],
                        ];
                    @endphp
                    @foreach($columns as $i => $column)
                        <article class="{{ $valueCardClass }}">
                            <img src="{{ $asset($caseIcons[$i]['icon_path'] ?? $caseIcons[$i]['icon'] ?? 'icon-breakthrough.png') }}" alt="" loading="lazy" width="120" height="120" class="{{ $valueIconClass }}">
                            <h3 class="{{ $valueTitleClass }}">{{ $caseIcons[$i]['title'] ?? '' }}</h3>
                            @if(isset($column['items']) && count($column['items']) > 0)
                                <ul class="{{ $valueListClass }}">
                                    @foreach($column['items'] as $item)
                                        <li class="{{ $valueListItemClass }}">
                                            <span class="{{ $valueBadgeClass }}" aria-hidden="true"></span>
                                            <span>{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @elseif(! empty($column['body']))
                                <p class="{{ $valueBodyClass }}" style="text-align-last: left;">{{ $column['body'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>

                <div class="flex justify-center gap-3 mt-[3.75rem] max-[768px]:flex-col max-[768px]:items-stretch">
                    <a href="{{ $detailUrl }}" class="{{ $btnOutline }}">{{ $locale === 'vi' ? 'XEM CHI TIẾT' : 'VIEW DETAILS' }}</a>
                    <a href="{{ $contactUrl }}" class="{{ $btnRed }}">{{ $locale === 'vi' ? 'NHẬN TƯ VẤN MIỄN PHÍ' : 'FREE CONSULTATION' }}</a>
                </div>
            </div>
        </section>
    @endforeach

    {{-- Capabilities --}}
    <section class="py-22 max-[768px]:py-14">
        <div class="{{ $container }}">
            <h2 class="{{ $h2Section }} text-center">{{ $capsT?->title ?? ($locale === 'vi' ? 'VIỆT NAM – NĂNG LỰC Y HỌC ĐANG VƯƠN TẦM QUỐC TẾ' : 'VIETNAM — MEDICAL CAPABILITY RISING GLOBALLY') }}</h2>
            <div class="{{ $capabilityGridClass }}">
                @foreach($capabilities as $i => $item)
                    <article class="{{ $capabilityCardClass }}">
                        <img src="{{ $asset($capabilityIcons[$i] ?? 'icon-doctors.png') }}" alt="" loading="lazy" width="120" height="120" class="{{ $capabilityIconClass }}">
                        <h3 class="{{ $capabilityTitleClass }}">{!! nl2br(e($item['title'] ?? '')) !!}</h3>
                        <p class="{{ $capabilityBodyClass }}" style="text-align-last: center;">
                            {{ $item['body'] ?? '' }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Assurance --}}
    <section class="relative overflow-hidden py-22 bg-[#11a4a6] max-[768px]:py-14">
        <img src="{{ $asset('vmta-assurance-bg.jpg') }}" alt="" loading="lazy" width="1441" height="556"
             class="absolute inset-0 w-full h-full object-cover">
        <div class="{{ $container }} relative z-10">
            <h2 class="{{ $h2Section }} max-w-[760px]">{{ $assurT?->title ?? ($locale === 'vi' ? 'VMTA – BẢO CHỨNG CHO HÀNH TRÌNH Y TẾ AN TOÀN' : 'VMTA — ASSURANCE FOR A SAFE MEDICAL JOURNEY') }}</h2>
            @php
                $assurBody = trim((string) ($assurT?->body ?? ''));
                $assurDefault = $locale === 'vi'
                    ? 'Lần đầu tiên tại Việt Nam, bệnh nhi 12 tuổi được thay khớp hàng toàn phần bằng công nghệ in 3D và thiết bị định vị PSI thiết kế riêng biệt.'
                    : 'For the first time in Vietnam, a 12-year-old patient underwent total hip replacement using 3D printing technology with custom PSI navigation devices.';
            @endphp
            @if($assurBody !== '')
                <div class="cms-body max-w-[760px] mt-5 text-[1.05rem] leading-[1.5] italic">{!! $assurBody !!}</div>
            @else
                <p class="max-w-[760px] mt-5 mb-0 text-[1.05rem] leading-[1.5]"><em>{{ $assurDefault }}</em></p>
            @endif
            <div class="{{ $assuranceGridClass }}">
                @foreach($assurance as $i => $item)
                    <article class="{{ $assuranceItemClass }}">
                        <img src="{{ $asset($assuranceIcons[$i] ?? 'icon-plan.png') }}" alt="" loading="lazy" width="60" height="60" class="{{ $assuranceIconClass }}">
                        <p class="{{ $assuranceBodyClass }}" style="text-align-last: left;">
                            {{ $item['body'] ?? '' }}
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="relative min-h-[400px] overflow-hidden">
        <img src="{{ $asset('cta-care-journey.jpg') }}" alt="" loading="lazy" width="2560" height="988"
             class="absolute inset-0 w-full h-full object-cover object-[50%_10%]">
        <div class="{{ $container }} relative z-10 min-h-[400px] flex justify-end items-center max-[768px]:justify-start max-[768px]:py-12">
            <div class="w-[min(100%,760px)]">
                <h2 class="font-sharp-bo font-bold uppercase leading-[1.28] tracking-normal m-0 text-[#0b7f7c] text-[clamp(2rem,3.4vw,3.8rem)]">
                    {!! $locale === 'vi' ? 'BẮT ĐẦU HÀNH TRÌNH<br>CHĂM SÓC SỨC KHỎE CỦA BẠN' : 'BEGIN YOUR<br>HEALTHCARE JOURNEY' !!}
                </h2>
                <p class="my-5 text-[1.08rem]"><strong>{{ $locale === 'vi' ? 'Đừng để rào cản thông tin khiến bạn bỏ lỡ cơ hội điều trị tốt nhất' : 'Don\'t let information barriers stop you from accessing the best care' }}</strong></p>
                <a href="{{ $serviceUrl }}" class="{{ $btnWhite }}">{{ $locale === 'vi' ? 'TÌM HIỂU DỊCH VỤ' : 'EXPLORE SERVICES' }}</a>
                <a href="{{ $contactUrl }}" class="{{ $btnRed }}">{{ $locale === 'vi' ? 'NHẬN TƯ VẤN MIỄN PHÍ' : 'FREE CONSULTATION' }}</a>
            </div>
        </div>
    </section>

    {{-- Recently viewed posts (cookie-backed, 7d TTL, max 3) --}}
    @include('content::public.partials.recently-viewed')
</div>
@endsection
