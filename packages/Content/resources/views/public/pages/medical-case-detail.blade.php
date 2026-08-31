@extends('site::layouts.public')

@php
$locale = app()->getLocale();
$detail = is_array($translation->detail_content) ? $translation->detail_content : [];
$asset = fn (string $file) => asset('images/heart-lung-transplant/' . ltrim($file, '/'));
$mediaUrl = function ($media) {
if (! $media?->permalink) return null;
if (preg_match('#^https?://#', $media->permalink)) return $media->permalink;
$base = rtrim(config('file-manager.base_path', '/uploads'), '/');
return url($base . '/' . ltrim($media->permalink, '/'));
};
$text = fn (string $key, string $default = '') => data_get($detail, $key, $default);
$items = function (string $key) use ($detail) {
$value = data_get($detail, $key, []);
if (! is_array($value)) return [];
return array_values(array_filter($value, fn ($item) => is_array($item) && trim(implode('', array_map('strval', $item))) !== ''));
};
$list = function (string $key) use ($detail) {
$value = data_get($detail, $key, []);
if (! is_array($value)) return [];
return array_values(array_filter(array_map('strval', $value), fn ($item) => trim($item) !== ''));
};
$lines = fn (string $value) => array_values(array_filter(preg_split('/\R{2,}/', trim($value)) ?: []));
$trustItems = [
['icon' => 'icon-privacy.png', 'label' => $locale === 'vi' ? 'Bảo mật tuyệt đối' : 'Absolute privacy'],
['icon' => 'icon-response.png', 'label' => $locale === 'vi' ? 'Phản hồi trong 24h' : 'Response within 24h'],
['icon' => 'icon-free.png', 'label' => $locale === 'vi' ? 'Hoàn toàn miễn phí' : 'Completely free'],
];
$choiceImages = [
'choice-surgery-room.jpg',
'choice-doctor-support.jpg',
'choice-intensive-care.jpg',
'choice-recovery-resort.jpg',
];
$breakthroughRightIcons = ['icon-oxygen.png', 'icon-survival.png', 'icon-stethoscope.png'];
$processIcons = ['icon-nutrition.png', 'icon-hospital.png', 'icon-survival.png', 'icon-oxygen.png'];
$heroHighlight = $text('hero_highlight_text', '');
$heroBody = $text('hero_body', $translation->subtitle ?? '');

// p/ul/ol/li/headings/links/img/strong/em/etc.
$introHtmlRaw = $text('intro_body', $translation->intro ?? '');
$introHtml = $introHtmlRaw !== '' ? clean($introHtmlRaw) : '';

// Intro section image: prefer admin-picked intro_media_id from detail_content,
// fall back to the case's main $case->image, then to the static team.jpg.
$introMediaId = (int) $text('intro_media_id', 0) ?: null;
$introMediaModel = $introMediaId ? \Packages\Core\Src\Models\MediaFile::find($introMediaId) : null;
$introImage = $mediaUrl($introMediaModel) ?? $mediaUrl($case->image) ?? $asset('team.jpg');

$caseImage = $mediaUrl($case->image) ?? $asset('team.jpg');
$consultRoute = route('inquiry.' . $locale . '.contact.store');
@endphp

@section('content')
<article class="vmta-heart-lung bg-white">
    <section class="heart-lung-hero">
        <img src="{{ $asset('hero-bg.jpg') }}" alt="" class="heart-lung-hero__bg" fetchpriority="high">
        <div class="heart-lung-shell heart-lung-hero__inner">
            <div class="heart-lung-hero__copy">
                <p class="heart-lung-kicker">{{ $text('hero_eyebrow', 'KỲ TÍCH Y KHOA TẠI VIỆT NAM') }}</p>
                <h1>{{ $text('hero_title', 'GHÉP ĐỒNG THỜI') }}<br>{{ $text('hero_highlight', 'TIM – PHỔI') }}</h1>
               
                @if($heroHighlight)
                    <p class="heart-lung-subtitle mb-2">{{ $heroHighlight }}</p>
                @endif
                <p>{{ $heroBody }}</p>
                <a href="#heart-lung-consult" class="heart-lung-btn heart-lung-btn--red mt-5">{{ $text('cta_label', 'NHẬN TƯ VẤN PHƯƠNG ÁN ĐIỀU TRỊ') }}</a>
                <div class="heart-lung-trust">
                    @foreach($trustItems as $item)
                    <span><img src="{{ $asset($item['icon']) }}" alt="">{{ $item['label'] }}</span>
                    @endforeach
                </div>
            </div>

            <aside class="heart-lung-info-card" aria-label="Thông tin ca điều trị">
                <div>
                    <img src="{{ $asset('icon-hospital.png') }}" alt="">
                    <span>{{ $locale === 'vi' ? 'Bệnh viện thực hiện' : 'Hospital' }}</span>
                    <strong>{{ $text('hospital_name', 'BỆNH VIỆN HỮU NGHỊ VIỆT ĐỨC') }}</strong>
                </div>
                <div>
                    <img src="{{ $asset('icon-calendar.png') }}" alt="">
                    <span>{{ $locale === 'vi' ? 'Thời gian thực hiện' : 'Date' }}</span>
                    <strong>{{ $text('time_value', '08/2025') }}</strong>
                </div>
            </aside>
        </div>
    </section>

    <section class="heart-lung-shell heart-lung-intro">
        <div class="heart-lung-copy text-justify" style="text-align-last: left;">
            {{-- intro_body is CKEditor HTML, purified via mews/purifier --}}
            {!! $introHtml !!}
        </div>
        <img src="{{ $introImage }}" alt="{{ $translation->title }}" loading="lazy">
    </section>

    <section class="heart-lung-shell heart-lung-reasons">
        <h2>{{ $locale === 'vi' ? 'VÌ SAO KỸ THUẬT NÀY ĐẶC BIỆT' : 'WHY THIS TECHNIQUE IS SPECIAL' }}</h2>
        <div class="heart-lung-reason-grid">
            @foreach($items('reason_items') as $item)
                @php
                    // Prefer admin-picked media (icon_media_id). Fall back to the
                    // legacy `icon` filename column so pre-existing seeded data still renders.
                    $reasonIconId = (int) ($item['icon_media_id'] ?? 0) ?: null;
                    $reasonIconModel = $reasonIconId ? \Packages\Core\Src\Models\MediaFile::find($reasonIconId) : null;
                    $reasonIconUrl = $mediaUrl($reasonIconModel)
                        ?? (! empty($item['icon']) ? $asset($item['icon']) : null);
                @endphp
                <article>
                    @if($reasonIconUrl)<img src="{{ $reasonIconUrl }}" alt="" loading="lazy">@endif
                    <h3>{{ $item['title'] ?? '' }}</h3>
                    <p class="text-justify" style="text-align-last: center;">{{ $item['body'] ?? '' }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mx-auto w-[min(calc(100%_-_2rem),1280px)] py-[4.8rem]">
        <h2 class="m-0 text-center text-[clamp(1.6rem,3.2vw,2.9rem)] font-bold leading-[1.28] tracking-normal text-[#0b7f7c] uppercase [font-family:var(--hl-font-display)]">{{ $locale === 'vi' ? 'THÀNH TỰU Y KHOA ĐỘT PHÁ' : 'BREAKTHROUGH MEDICAL ACHIEVEMENT' }}</h2>
        <div class="mt-[3.6rem] grid grid-cols-1 items-center gap-0 min-[901px]:grid-cols-[40%_20%_40%]">
            <article class="overflow-hidden rounded-[.8rem] bg-[#eefbfa] min-[901px]:min-h-[36rem] flex flex-col">
                <h3 class="m-0 mb-[.85rem] bg-[#d31e45] p-[.8rem] text-center text-[clamp(.8125rem,1.1vw,1rem)] font-bold leading-[1.35] text-white">{{ $locale === 'vi' ? 'GIẢI PHÁP ĐỘT PHÁ' : 'BREAKTHROUGH SOLUTIONS' }}</h3>
                <div class="flex-1 flex flex-col justify-between">
                    @foreach($items('breakthrough_left_items') as $item)
                    @php
                        $leftIconId = (int) ($item['icon_media_id'] ?? 0) ?: null;
                        $leftIconModel = $leftIconId ? \Packages\Core\Src\Models\MediaFile::find($leftIconId) : null;
                        $leftIconUrl = $mediaUrl($leftIconModel) ?? (! empty($item['icon']) ? $asset($item['icon']) : null);
                    @endphp
                    <div class="grid grid-cols-[50px_1fr] items-center gap-4 px-[1.9rem] py-[.7rem]">
                        @if($leftIconUrl)<img src="{{ $leftIconUrl }}" alt="" class="h-[60px] w-[50px] object-contain" loading="lazy">@endif
                        <p class="m-0 text-[clamp(.8125rem,1.1vw,1rem)] leading-[1.42] text-[#0b7f7c]"><strong>{{ $item['title'] ?? '' }}</strong> <br> {{ ! empty($item['body']) ? ' ' . $item['body'] : '' }}</p>
                    </div>
                    @endforeach
                </div>
            </article>

            @php
                $breakCenterId = (int) $text('breakthrough_center_media_id', 0) ?: null;
                $breakCenterModel = $breakCenterId ? \Packages\Core\Src\Models\MediaFile::find($breakCenterId) : null;
                $breakCenterUrl = $mediaUrl($breakCenterModel) ?? $asset('lungs.png');
            @endphp
            <img src="{{ $breakCenterUrl }}" alt="" class="z-[1] aspect-square w-[min(145%,370px)] max-w-none justify-self-center object-contain" loading="lazy">

            <article class="overflow-hidden rounded-[.8rem] bg-[#eefbfa] min-[901px]:min-h-[36rem] flex flex-col">
                <h3 class="m-0 mb-[.85rem] bg-[#d31e45] p-[.8rem] text-center text-[clamp(.8125rem,1.1vw,1rem)] font-bold leading-[1.35] text-white">
                    {{ $locale === 'vi' ? 'HIỆU QUẢ VƯỢT TRỘI' : 'SUPERIOR OUTCOMES' }}
                </h3>
                <div class="flex-1 flex flex-col justify-between">
                    @foreach($items('breakthrough_right_items') as $index => $item)
                    @php
                        $rightIconId = (int) ($item['icon_media_id'] ?? 0) ?: null;
                        $rightIconModel = $rightIconId ? \Packages\Core\Src\Models\MediaFile::find($rightIconId) : null;
                        $rightIconUrl = $mediaUrl($rightIconModel)
                            ?? (! empty($item['icon']) ? $asset($item['icon']) : null)
                            ?? (isset($breakthroughRightIcons[$index]) ? $asset($breakthroughRightIcons[$index]) : null);
                    @endphp
                    <div class="grid grid-cols-[50px_1fr] items-center gap-4 px-[1.9rem] py-[.7rem]">
                        @if($rightIconUrl)<img src="{{ $rightIconUrl }}" alt="" class="h-[60px] w-[50px] object-contain" loading="lazy">@endif
                        <p class="m-0 text-[clamp(.8125rem,1.1vw,1rem)] leading-[1.42] text-[#0b7f7c]"><strong>{{ $item['title'] ?? '' }}</strong> <br> {{ ! empty($item['body']) ? ' ' . $item['body'] : '' }}</p>
                    </div>
                    @endforeach
                </div>
            </article>
        </div>
        @if($text('breakthrough_note'))
        <div class="mt-10 grid grid-cols-[64px_1fr] items-center gap-5 bg-[#d31e45] px-8 py-[1.35rem] text-base leading-[1.45] text-white max-[900px]:grid-cols-1 max-[900px]:justify-items-center max-[900px]:text-center">
            <img src="{{ $asset('icon-vietnam.png') }}" alt="" class="h-14 w-14 object-contain">
            <p class="m-0">{{ $text('breakthrough_note') }}</p>
        </div>
        @endif
    </section>

    <section class="heart-lung-shell heart-lung-consult-wrap" id="heart-lung-consult">
        <div>
            <h2>{{ $locale === 'vi' ? 'VÌ SAO CHỌN VIỆT NAM?' : 'WHY CHOOSE VIETNAM?' }}</h2>
            <div class="heart-lung-choice-grid">
                @foreach($items('choice_items') as $index => $item)
                @php
                    $choiceImageId = (int) ($item['image_media_id'] ?? 0) ?: null;
                    $choiceImageModel = $choiceImageId ? \Packages\Core\Src\Models\MediaFile::find($choiceImageId) : null;
                    $choiceImageUrl = $mediaUrl($choiceImageModel)
                        ?? (isset($choiceImages[$index]) ? $asset($choiceImages[$index]) : null);
                @endphp
                <article>
                    @if($choiceImageUrl)
                    <img src="{{ $choiceImageUrl }}" alt="{{ $item['title'] ?? '' }}" loading="lazy">
                    @endif
                    <h3>{{ $item['title'] ?? '' }}</h3>
                    <p class="text-justify" style="text-align-last: left;">{{ $item['body'] ?? '' }}</p>
                </article>
                @endforeach
            </div>

            <h3 class="max-w-[60%] uppercase text-lg font-bold text-[#0b7f7c]">{{ $locale === 'vi' ? 'VMTA - ĐƠN VỊ THẨM ĐỊNH VÀ ĐỒNG HÀNH TIN CẬY' : 'VMTA - TRUSTED CARE COORDINATOR' }}</h3>
            <div class="heart-lung-process mt-10">
                @foreach($items('process_items') as $index => $item)
                <span>
                    <img src="{{ $asset($processIcons[$index] ?? 'icon-vietnam.png') }}" alt=""   class="h-[60px] w-[50px] object-contain [filter:brightness(0)_saturate(100%)_invert(23%)_sepia(32%)_saturate(6001%)_hue-rotate(330deg)_brightness(90%)_contrast(100%)]"
loading="lazy">
                    {{ $item['body'] ?? '' }}
                </span>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ $consultRoute }}" class="heart-lung-form">
            @csrf
            @honeypot
            <input type="hidden" name="source_ref_type" value="medical_case">
            <input type="hidden" name="source_ref_id" value="{{ $case->id }}">
            <h2>{!! nl2br(e($text('form_title', 'NHẬN TƯ VẤN PHƯƠNG ÁN ĐIỀU TRỊ PHÙ HỢP'))) !!}</h2>
            <p>{{ $text('form_body', 'Đội ngũ chuyên gia của VMTA sẽ đánh giá hồ sơ và đề xuất giải pháp tối ưu dành riêng cho bạn') }}</p>
            <div class="heart-lung-form__fields">
                <input name="name" type="text" required maxlength="120" value="{{ old('name') }}" placeholder="{{ __('inquiry::inquiry.field_name') }}*">
                <input name="phone" type="tel" required maxlength="30" value="{{ old('phone') }}" placeholder="{{ __('inquiry::inquiry.field_phone') }}*">
                <input name="email" type="email" required maxlength="160" value="{{ old('email') }}" placeholder="{{ __('inquiry::inquiry.field_email') }}*">
                <textarea name="condition" rows="4" maxlength="160" placeholder="{{ __('inquiry::inquiry.field_condition') }}*">{{ old('condition') }}</textarea>
                <label><input type="checkbox" name="consent_given" value="1" required {{ old('consent_given') ? 'checked' : '' }}> {{ __('inquiry::inquiry.field_consent') }}</label>
                <button type="submit">{{ $locale === 'vi' ? 'NHẬN TƯ VẤN MIỄN PHÍ' : 'GET FREE CONSULTATION' }}</button>
                <div class="heart-lung-trust heart-lung-form__trust">
                    @foreach($trustItems as $item)<span><img src="{{ $asset($item['icon']) }}" alt="">{{ $item['label'] }}</span>@endforeach
                </div>
            </div>
        </form>
    </section>

    <section class="heart-lung-cta">
        <div class="heart-lung-shell">
            @php
                // cta_body now stores CKEditor HTML (description + bullet list combined).
                $ctaBodyHtmlRaw = $text('cta_body', '');
                $ctaBodyHtml = $ctaBodyHtmlRaw !== '' ? clean($ctaBodyHtmlRaw) : '';
            @endphp
            <h2>{!! nl2br(e($text('cta_title', 'MỘT QUYẾT ĐỊNH ĐÚNG CÓ THỂ THAY ĐỔI CẢ CUỘC ĐỜI'))) !!}</h2>
            @if($ctaBodyHtml !== '')
                <div class="heart-lung-copy">
                    {!! $ctaBodyHtml !!}
                </div>
            @endif
            <a href="#heart-lung-consult" class="heart-lung-btn heart-lung-btn--red">{{ $locale === 'vi' ? 'BẮT ĐẦU HÀNH TRÌNH NGAY HÔM NAY' : 'START YOUR JOURNEY TODAY' }}</a>
        </div>
    </section>

    {{-- Recently viewed posts (cookie-backed, 7d TTL, max 3) --}}
    @include('content::public.partials.recently-viewed')
</article>
@endsection