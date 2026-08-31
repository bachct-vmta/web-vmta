@extends('site::layouts.public')

@php
    $locale = app()->getLocale();
    $hubRoute = 'site.'.$locale.'.catalog.specialties.index';
    $slug = (string) ($translation->slug ?? '');
    $isDentalSource = in_array($slug, ['nha-khoa', 'dentistry'], true);
    $shellClass = 'max-w-7xl px-4 mx-auto';
    $headingClass = 'm-0 font-sharp-bo font-bold uppercase tracking-[0.05em] text-vmta-teal';
    $sectionHeadingClass = $headingClass.' mx-auto max-w-3xl text-center text-[clamp(1.25rem,3.8vw,2.875rem)] leading-[1.4]';
    $copyClass = 'text-[#4a4a4a] text-[1.0625rem] leading-[1.75] [&_p]:mb-4 [&_p:last-child]:mb-0 [&_ul]:mt-3 [&_ul]:list-outside [&_ul]:list-disc [&_ul]:pl-5 [&_li]:mt-[0.45rem] [&_li::after]:hidden [&_li::after]:[content:none] [&_li::marker]:text-[0.8em] [&_li::marker]:text-vmta-teal';
    $listClass = 'mt-3 list-outside list-disc pl-5 text-[#4a4a4a] text-[0.95rem] leading-[1.7] marker:text-[0.8em] marker:text-vmta-teal [&_li]:mt-[0.45rem] [&_li::after]:hidden [&_li::after]:[content:none]';

    $publicAsset = fn (string $path) => asset('images/specialties/nha-khoa/'.$path);
    $mediaUrl = function ($pathOrPermalink) {
        if (! $pathOrPermalink) return null;
        if (preg_match('#^https?://#', (string) $pathOrPermalink)) return $pathOrPermalink;
        $base = rtrim(config('file-manager.base_path', '/uploads'), '/');
        return url($base.'/'.ltrim((string) $pathOrPermalink, '/'));
    };

    $heroUrl = $isDentalSource
        ? $publicAsset('hero-bg.png')
        : $mediaUrl($specialty->heroMedia?->permalink);
    $introBgUrl = $isDentalSource ? $publicAsset('intro-bg.jpg') : null;
    $introImageUrl = $isDentalSource
        ? $publicAsset('intro-dental.jpg')
        : $mediaUrl($specialty->introImage?->permalink);

    $strengths = is_array($translation->strengths_json) ? $translation->strengths_json : [];
    $hospitals = is_array($translation->hospitals_json) ? $translation->hospitals_json : [];

    $resolveJsonMedia = function ($mediaId, $fallbackPath) use ($mediaMap, $mediaUrl) {
        if ($mediaId && isset($mediaMap[$mediaId])) {
            return $mediaUrl($mediaMap[$mediaId]->permalink);
        }
        return $mediaUrl($fallbackPath);
    };

    $safeUrl = function ($url) {
        $url = trim((string) $url);
        if ($url === '') return null;
        if (str_starts_with($url, '#')) return $url;
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) return $url;
        if (preg_match('#^(https?://|mailto:|tel:)#i', $url)) return $url;
        return null;
    };

    if ($isDentalSource) {
        $dentalIntroTitle = $locale === 'en'
            ? 'Vietnam - A new destination for high-quality dentistry'
            : 'Việt Nam – Điểm đến mới của nha khoa chất lượng cao';
        $dentalIntroLead = $locale === 'en'
            ? 'Elevating smiles - Optimizing oral health'
            : 'Nâng tầm nụ cười – Tối ưu sức khỏe răng miệng';
        $dentalIntroBody = $locale === 'en'
            ? '<p>Vietnam is emerging as a dental hub in Asia, combining medical expertise, modern technology, and competitive treatment costs.</p><ul><li>Internationally trained dental teams</li><li>Advanced treatment technology (3D scan, CAD/CAM)</li><li>Premium dental materials meeting global standards</li></ul><p>Beyond treatment, Vietnam offers a care journey combined with restorative travel for faster, more comfortable recovery.</p><p>A practical option for patients seeking high quality at reasonable cost.</p>'
            : '<p>Trong những năm gần đây, Việt Nam đang nổi lên như một trung tâm nha khoa tại châu Á, nơi hội tụ giữa chuyên môn y khoa, công nghệ hiện đại và chi phí hợp lý.</p><ul><li>Đội ngũ bác sĩ được đào tạo quốc tế</li><li>Công nghệ điều trị tiên tiến (3D scan, CAD/CAM)</li><li>Vật liệu nha khoa cao cấp đạt chuẩn toàn cầu</li></ul><p>Không chỉ dừng lại ở điều trị, Việt Nam còn mang đến trải nghiệm kết hợp nghỉ dưỡng – giúp khách hàng phục hồi nhanh hơn và thoải mái hơn.</p><p>Một lựa chọn tối ưu cho những ai tìm kiếm chất lượng cao với chi phí hợp lý.</p>';

        $genericIntroTitles = ['Chăm sóc sức khỏe răng miệng chuẩn quốc tế', 'International-standard dental care'];
        $genericIntroLeads = ['Đội ngũ chuyên gia đầu ngành, trang thiết bị nhập khẩu, quy trình vô khuẩn nghiêm ngặt.'];
        $genericIntroBodies = ['Hệ thống nha khoa thuộc Liên minh VMTA cung cấp đầy đủ dịch vụ từ tổng quát đến chuyên sâu: trồng răng Implant, niềng răng, Veneer thẩm mỹ, tẩy trắng và phục hình.'];

        $introTitle = (! $translation->intro_h2 || in_array(trim((string) $translation->intro_h2), $genericIntroTitles, true))
            ? $dentalIntroTitle
            : $translation->intro_h2;
        $introLead = (! $translation->intro_lead || in_array(trim((string) $translation->intro_lead), $genericIntroLeads, true))
            ? $dentalIntroLead
            : $translation->intro_lead;
        $introBodyText = trim(strip_tags((string) $translation->intro_body_html));
        $introBody = (! $introBodyText || in_array($introBodyText, $genericIntroBodies, true))
            ? $dentalIntroBody
            : $translation->intro_body_html;

        $baseStrengths = $locale === 'en' ? [
            ['title' => 'Implant', 'image' => $publicAsset('implant.jpg'), 'bullets' => ['Durable solution for missing teeth', 'Restores chewing function and aesthetics', 'Computer-guided precision placement']],
            ['title' => 'Veneer', 'image' => $publicAsset('veneer.jpg'), 'bullets' => ['Harmonious and natural smile design', 'Minimally invasive, fast turnaround', 'Tailored for international patients']],
            ['title' => 'Orthodontics', 'image' => $publicAsset('orthodontics.jpg'), 'bullets' => ['Corrects bite misalignment', 'Flexible options (braces, Invisalign)', 'Clear treatment timeline']],
        ] : [
            ['title' => 'Implant (Trồng răng)', 'image' => $publicAsset('implant.jpg'), 'bullets' => ['Giải pháp phục hồi răng mất bền vững', 'Đảm bảo chức năng ăn nhai và thẩm mỹ', 'Ứng dụng công nghệ định vị chính xác']],
            ['title' => 'Veneer (Dán sứ thẩm mỹ)', 'image' => $publicAsset('veneer.jpg'), 'bullets' => ['Thiết kế nụ cười hài hòa, tự nhiên', 'Ít xâm lấn, thời gian nhanh', 'Phù hợp khách hàng quốc tế']],
            ['title' => 'Chỉnh nha (Niềng răng)', 'image' => $publicAsset('orthodontics.jpg'), 'bullets' => ['Điều chỉnh sai lệch khớp cắn', 'Lựa chọn linh hoạt (mắc cài, invisalign)', 'Lộ trình rõ ràng']],
        ];
        $hasStrengthImages = collect($strengths)->contains(fn ($item) => ! empty($item['image']) || ! empty($item['image_media_id']) || ! empty($item['image_path']));
        $sourceStrengthTitles = collect($baseStrengths)->pluck('title')->all();
        $usesSourceStrengths = collect($strengths)->contains(fn ($item) => in_array(trim((string) ($item['title'] ?? '')), $sourceStrengthTitles, true));
        if (count($strengths) < 3 || ! $hasStrengthImages || (count($strengths) < 9 && $usesSourceStrengths)) {
            $strengths = array_merge($baseStrengths, $baseStrengths, $baseStrengths);
        }

        $defaultHospital = $locale === 'en' ? [
            'name' => 'Central Hospital of Odonto-Stomatology Ho Chi Minh City',
            'bullets' => ['Leading dental institution in Vietnam', 'Specialist medical team', 'Experience with complex cases'],
            'cta_primary' => ['label' => 'Book appointment', 'url' => '#lead-form'],
            'cta_secondary' => ['label' => 'Learn more', 'url' => '#lead-form'],
        ] : [
            'name' => 'Bệnh viện Răng Hàm Mặt Trung ương TP.HCM',
            'bullets' => ['Đơn vị đầu ngành về nha khoa tại Việt Nam', 'Đội ngũ bác sĩ chuyên sâu', 'Kinh nghiệm điều trị ca phức tạp'],
            'cta_primary' => ['label' => 'Đặt lịch', 'url' => '#lead-form'],
            'cta_secondary' => ['label' => 'Tìm hiểu thêm', 'url' => '#lead-form'],
        ];
        $hospitals = array_values($hospitals);
        $seedHospital = $hospitals[0] ?? $defaultHospital;
        while (count($hospitals) < 4) $hospitals[] = $seedHospital;
        $hospitals = array_map(function ($hospital) use ($defaultHospital) {
            return [
                'name' => trim((string) ($hospital['name'] ?? '')) ?: $defaultHospital['name'],
                'bullets' => ! empty($hospital['bullets']) && is_array($hospital['bullets']) ? $hospital['bullets'] : $defaultHospital['bullets'],
                'image_media_id' => $hospital['image_media_id'] ?? null,
                'image_path' => $hospital['image_path'] ?? null,
                'cta_primary' => ! empty($hospital['cta_primary']['url']) ? $hospital['cta_primary'] : $defaultHospital['cta_primary'],
                'cta_secondary' => ! empty($hospital['cta_secondary']['url']) ? $hospital['cta_secondary'] : $defaultHospital['cta_secondary'],
            ];
        }, array_slice($hospitals, 0, 4));
    } else {
        $introTitle = $translation->intro_h2;
        $introLead = $translation->intro_lead;
        $introBody = $translation->intro_body_html;
    }
@endphp

@section('content')
<article class="vmta-specialty-detail bg-white font-utm-helve text-[#4a4a4a]">
    <section class="vmta-specialty-hero relative min-h-[300px] bg-white bg-cover bg-[position:50%_35%] pt-32 pb-12 before:absolute before:inset-0 before:bg-white/70 max-[850px]:pt-20 max-[850px]:text-center" @if($heroUrl) style="background-image:url('{{ $heroUrl }}')" @endif>
        <div class="vmta-specialty-shell {{ $shellClass }} relative z-[1]">
            <h1 class="{{ $headingClass }} text-[clamp(1.25rem,4vw,3.125rem)] leading-none">{{ $heroH1 }}</h1>
            <nav class="mt-3 text-[clamp(0.875rem,1.2vw,1.5625rem)] leading-[1.1] text-vmta-teal [&_a]:mr-1.5 [&_span]:mr-1.5" aria-label="breadcrumb">
                <a href="{{ url('/'.$locale) }}">{{ __('catalog::public.specialties.breadcrumb_home') }}</a>
                @unless($isDentalSource)
                    <span>/</span><a href="{{ route($hubRoute) }}">{{ __('catalog::public.specialties.heading') }}</a>
                @endunless
                <span>/</span><span>{{ $breadcrumb }}</span>
            </nav>
        </div>
    </section>

    @if($introTitle || $introLead || $introBody)
        <section class="vmta-specialty-intro bg-cover bg-center py-20 max-[640px]:py-16" @if($introBgUrl) style="background-image:url('{{ $introBgUrl }}')" @endif>
            <div class="vmta-specialty-shell {{ $shellClass }} grid grid-cols-[minmax(0,7fr)_minmax(280px,5fr)] items-center gap-[clamp(2rem,5vw,5rem)] max-[850px]:grid-cols-1">
                <div>
                    @if($introTitle)<h2 class="{{ $headingClass }} text-[clamp(1.125rem,3vw,2.25rem)] leading-[1.4]">{{ $introTitle }}</h2>@endif
                    @if($introLead)<p class="my-4 mb-9 text-lg font-bold text-[#4a4a4a]">{{ $introLead }}</p>@endif
                    @if($introBody)
                        <div class="vmta-specialty-copy {{ $copyClass }}">
                            {!! clean($introBody) !!}
                        </div>
                    @endif
                </div>
                @if($introImageUrl)
                    <img class="w-full rounded-[2rem] object-cover" src="{{ $introImageUrl }}" alt="{{ $translation->name }}" loading="lazy">
                @endif
            </div>
        </section>
    @endif

    @if(! empty($strengths))
        <section class="vmta-specialty-strengths py-20 max-[640px]:py-16">
            <div class="vmta-specialty-shell {{ $shellClass }}">
                <h2 class="{{ $sectionHeadingClass }}">{{ $isDentalSource ? ($locale === 'en' ? 'Dental Strengths in Vietnam' : 'Thế mạnh nha khoa tại Việt Nam') : trim(($translation->strengths_h2_line1 ?? '').' '.($translation->strengths_h2_line2 ?? '')) }}</h2>
                <div class="vmta-specialty-card-grid mt-[3.75rem] grid grid-cols-3 gap-x-[3.75rem] gap-y-[4.5rem] max-[850px]:grid-cols-2 max-[640px]:grid-cols-1 max-[640px]:gap-10">
                    @foreach($strengths as $item)
                        @php
                            $itemImage = $item['image'] ?? $resolveJsonMedia($item['image_media_id'] ?? null, $item['image_path'] ?? null);
                        @endphp
                        <article class="min-w-0">
                            @if($itemImage)<img class="aspect-[753/650] w-full rounded-[2.5rem] object-cover" src="{{ $itemImage }}" alt="{{ $item['title'] ?? '' }}">@endif
                            @if(! empty($item['title']))<h3 class="mt-5 mb-0 text-[1.0625rem] font-bold text-vmta-red">{{ $item['title'] }}</h3>@endif
                            @if(! empty($item['bullets']) && is_array($item['bullets']))
                                <ul class="{{ $listClass }}">@foreach($item['bullets'] as $bullet)<li>{{ $bullet }}</li>@endforeach</ul>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if(! empty($hospitals))
        <section class="vmta-specialty-hospitals py-20 max-[640px]:py-16">
            <div class="vmta-specialty-shell {{ $shellClass }}">
                <h2 class="{{ $sectionHeadingClass }}">{{ $isDentalSource ? ($locale === 'en' ? 'Leading Dental Hospitals in Vietnam' : 'Các bệnh viện nha khoa hàng đầu Việt Nam') : trim(($translation->hospitals_h2_line1 ?? '').' '.($translation->hospitals_h2_line2 ?? '')) }}</h2>
                @if($translation->hospitals_subtitle)<p class="mt-7 text-[1.375rem] italic text-[#4a4a4a] text-center">{{ $translation->hospitals_subtitle }}</p>@endif
                <div class="vmta-specialty-hospital-grid mt-20 grid grid-cols-2 gap-x-32 gap-y-20 max-[850px]:mt-16 max-[850px]:grid-cols-1 max-[850px]:gap-16">
                    @foreach($hospitals as $h)
                        @php
                            $hospitalImage = $resolveJsonMedia($h['image_media_id'] ?? null, $h['image_path'] ?? null);
                            $ctaPrimary = $h['cta_primary'] ?? null;
                            $ctaSecondary = $h['cta_secondary'] ?? null;
                            $ctaPrimaryUrl = $safeUrl($ctaPrimary['url'] ?? null);
                            $ctaSecondaryUrl = $safeUrl($ctaSecondary['url'] ?? null);
                        @endphp
                        <article>
                            @if($hospitalImage)<img class="aspect-[16/10] w-full rounded-[2rem] object-cover" src="{{ $hospitalImage }}" alt="{{ $h['name'] ?? '' }}" loading="lazy">@endif
                            @if(! empty($h['name']))<h3 class="{{ $hospitalImage ? 'mt-5 ' : '' }}mb-6 text-base font-bold uppercase tracking-[0.04em] text-[#4a4a4a]">{{ $h['name'] }}</h3>@endif
                            @if(! empty($h['bullets']) && is_array($h['bullets']))
                                <ul class="{{ $listClass }}">@foreach($h['bullets'] as $bullet)<li>{{ $bullet }}</li>@endforeach</ul>
                            @endif
                            <div>
                                @if($ctaPrimaryUrl)<a class="mt-6 mr-1 inline-flex min-h-10 items-center rounded-sm border-2 border-vmta-red bg-vmta-red px-4 text-[0.95rem] font-bold uppercase text-white" href="{{ $ctaPrimaryUrl }}">{{ $ctaPrimary['label'] ?? __('catalog::public.cta.book_now') }}</a>@endif
                                @if($ctaSecondaryUrl)<a class="mt-6 mr-1 inline-flex min-h-10 items-center rounded-sm border-2 border-vmta-teal px-4 text-[0.95rem] font-bold uppercase text-vmta-teal" href="{{ $ctaSecondaryUrl }}">{{ $ctaSecondary['label'] ?? __('catalog::public.specialties.view_detail') }}</a>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($specialty->show_lead_form)
        @include('catalog::public.specialties._lead-form', ['specialty' => $specialty, 'translation' => $translation])
    @endif
</article>
@endsection
