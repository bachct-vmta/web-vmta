@php
    $locale = app()->getLocale();
    $tr = $section?->translate($locale);

    $defaultTitle = $locale === 'vi' ? 'Tiêu chuẩn liên minh' : 'Alliance standards';
    $title = $tr?->title ?: $defaultTitle;

    // CKEditor body — already sanitized via HTMLPurifier on save (see UpdateAllianceSectionRequest::prepareForValidation)
    $bodyHtml = trim((string) ($tr?->body ?? ''));
    $hasBody = $bodyHtml !== '';

    // Fallback hardcoded content khi DB chưa có body
    $fallbackBullets = $locale === 'vi'
        ? [
            'Năng lực chuyên môn và đội ngũ',
            'Hệ thống vận hành & chất lượng dịch vụ',
            'Cơ sở hạ tầng và trải nghiệm khách hàng',
            'Khả năng phục vụ khách quốc tế',
        ]
        : [
            'Professional capability and team quality',
            'Operations system and service quality',
            'Infrastructure and client experience',
            'Capability to serve international clients',
        ];
@endphp
<section id="section-alliance-standards" class="pt-[60px] md-fs:pt-[90px] pb-[60px] md-fs:pb-[90px] bg-white">
    <div class="relative max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md-fs:grid-cols-2 gap-8 md-fs:gap-12 items-center">
            <div>
                <h2 class="font-sharp-bo fs-vmta-80 uppercase font-bold leading-[1.3] text-[#0b7f7c] text-center md-fs:text-left vmta-letter-spacing-0">
                    {{ $title }}
                </h2>

                @if($hasBody)
                    <div class="cms-body mt-5 font-utm-helve text-slate-700 leading-relaxed">
                        {!! $bodyHtml !!}
                    </div>
                @else
                    {{-- Fallback content khi admin chưa nhập body --}}
                    <p class="font-utm-helve mt-5 text-lg italic text-slate-700">
                        {{ $locale === 'vi' ? 'Tiêu chuẩn thẩm định nghiêm ngặt' : 'Rigorous assessment standards' }}
                    </p>
                    <div class="mt-5 font-utm-helve text-slate-700 leading-relaxed">
                        <p>
                            {{ $locale === 'vi'
                                ? 'Mọi đối tác trong hệ sinh thái VMTA đều phải trải qua quy trình thẩm định toàn diện:'
                                : 'Every partner in the VMTA ecosystem goes through a comprehensive assessment process:' }}
                        </p>
                        <ul class="mt-4 list-disc space-y-1 pl-5">
                            @foreach($fallbackBullets as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-5 font-semibold text-slate-900">
                            {{ $locale === 'vi'
                                ? 'Đảm bảo sự đồng nhất và uy tín toàn hệ thống.'
                                : 'Ensuring consistency and credibility across the network.' }}
                        </p>
                    </div>
                @endif
            </div>
            <div>
                <img src="{{ asset('images/alliance/standards.jpg') }}"
                     alt="{{ $title }}"
                     class="w-full h-full min-h-[430px] rounded-[30px] object-cover shadow-sm" loading="lazy">
            </div>
        </div>
    </div>
</section>

{{-- Typography for .cms-body is loaded globally in site::layouts.public --}}
