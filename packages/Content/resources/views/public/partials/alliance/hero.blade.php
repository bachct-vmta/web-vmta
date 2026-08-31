@php
    $locale = app()->getLocale();
    $heroTitle = $locale === 'vi'
        ? 'Mạng lưới Liên minh Du lịch Y tế VMTA'
        : 'VMTA Medical Tourism Alliance Network';
    $heroSubtitle = $locale === 'vi'
        ? 'Kết nối tinh hoa - Chuẩn hóa trải nghiệm - Kiến tạo giá trị bền vững'
        : 'Connecting excellence - Standardizing experiences - Building lasting value';
    $heroBody = $locale === 'vi'
        ? ['VMTA quy tụ các bệnh viện hàng đầu, khu nghỉ dưỡng cao cấp và đối tác lữ hành trong một hệ sinh thái thống nhất, được vận hành và điều phối theo tiêu chuẩn chung', 'Một mạng lưới không chỉ kết nối - mà còn bảo chứng']
        : ['VMTA brings together leading hospitals, premium resorts and travel partners in a unified ecosystem operated under shared standards.', 'A network that does not only connect - it also assures quality.'];
@endphp
<section id="section-alliance-hero" class="relative min-h-[350px] sm-fs:min-h-[80vh] flex items-start sm-fs:items-center justify-center overflow-hidden bg-white">
    <div class="absolute inset-0">
        <img src="{{ asset('images/alliance/hero.png') }}"
             class="w-full h-full object-cover opacity-20"
             fetchpriority="high" decoding="async" alt="">
    </div>
    <div class="relative z-10 w-full max-w-7xl mx-auto px-[8px] py-5 sm-fs:px-4 sm-fs:py-16">
        <div class="mx-auto max-w-6xl text-center text-[#0b7f7c]">
            <h1 class="alliance-hero-title font-sharp-bo fs-vmta-85 uppercase font-bold leading-[1.4] vmta-letter-spacing-0">
                {{ $heroTitle }}
            </h1>
            <p class="font-utm-helve fs-vmta-25 uppercase font-bold mt-5 text-justify sm-fs:text-center text-[#0b7f7c]">
                {{ $heroSubtitle }}
            </p>
            <div class="font-utm-helve fs-vmta-25 mt-5 w-full sm-fs:max-w-[60%] mx-auto font-bold leading-[1.5] text-justify text-black space-y-4 text-justify" style="text-align-last: center;">
                @foreach($heroBody as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
            <div class="mt-8">
                <a href="{{ route('inquiry.' . $locale . '.contact.show') }}"
                   class="inline-flex min-h-0 w-full items-center justify-center rounded bg-[#d31e45] px-[15px] py-[5px] font-utm-helve text-[15px] font-bold uppercase leading-[1.5] text-white transition hover:bg-[#b01838] sm-fs:w-auto">
                    {{ $locale === 'vi' ? 'Tham gia hệ sinh thái' : 'Join the ecosystem' }}
                </a>
            </div>
        </div>
    </div>
</section>
