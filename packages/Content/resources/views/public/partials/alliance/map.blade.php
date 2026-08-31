@php
    $locale = app()->getLocale();
@endphp
<section id="section-alliance-map" class="relative pt-[60px] md-fs:pt-[90px] pb-[60px] md-fs:pb-[90px] overflow-hidden bg-white">
    <div class="absolute inset-0">
        <img src="{{ asset('images/alliance/background.png') }}" alt="" class="w-full h-full object-cover opacity-[0.12] scale-150" loading="lazy">
    </div>
    <div class="relative max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md-fs:grid-cols-2 gap-8 md-fs:gap-12 items-center">
            <div>
                <h2 class="font-sharp-bo fs-vmta-80 uppercase font-bold leading-[1.3] text-[#0b7f7c] text-center md-fs:text-left vmta-letter-spacing-0">
                    {{ $locale === 'vi' ? 'Bản đồ mạng lưới' : 'Network map' }}
                </h2>
                <p class="font-utm-helve mt-5 text-lg italic text-slate-700">
                    {{ $locale === 'vi' ? 'Mạng lưới trải dài trên toàn quốc' : 'A nationwide network' }}
                </p>
                <div class="font-utm-helve leading-[1.7] mt-4 text-slate-700 space-y-3">
                    <p>{{ $locale === 'vi'
                        ? 'VMTA kết nối các đối tác tại những trung tâm y tế và du lịch trọng điểm, tạo nên một hệ sinh thái linh hoạt và đa dạng.'
                        : 'VMTA connects partners in key medical and travel destinations, creating a flexible and diverse ecosystem.' }}</p>
                    <p>{{ $locale === 'vi'
                        ? 'Sẵn sàng đáp ứng nhiều nhu cầu điều trị và trải nghiệm khác nhau.'
                        : 'Ready to support a wide range of treatment needs and experience expectations.' }}</p>
                </div>
            </div>
            <div>
                <img src="{{ asset('images/alliance/map.jpg') }}"
                     alt="{{ $locale === 'vi' ? 'Bản đồ mạng lưới' : 'Network map' }}"
                     class="aspect-[4/3] w-full rounded-[30px] object-cover shadow-sm" loading="lazy">
            </div>
        </div>
    </div>
</section>
