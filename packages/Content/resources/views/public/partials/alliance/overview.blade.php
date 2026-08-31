@php
    $locale = app()->getLocale();
    $pillars = $locale === 'vi'
        ? [
            ['title' => 'Y tế', 'detail' => '(Bệnh viện / Phòng khám)'],
            ['title' => 'Nghỉ dưỡng', 'detail' => '(Khách sạn / Resort)'],
            ['title' => 'Lữ hành', 'detail' => '(Travel / Agency)'],
            ['title' => 'Bảo hiểm', 'detail' => '(Du lịch / Sức khỏe)'],
        ]
        : [
            ['title' => 'Healthcare', 'detail' => 'Hospitals / Clinics'],
            ['title' => 'Recovery', 'detail' => 'Hotels / Resorts'],
            ['title' => 'Travel', 'detail' => 'Travel / Agencies'],
            ['title' => 'Insurance', 'detail' => 'Travel / Health'],
        ];
    $networkTitle = $locale === 'vi'
        ? 'VMTA - Liên minh Du lịch Y tế Việt Nam'
        : 'VMTA - Vietnam Medical Tourism Alliance';
@endphp
<section id="section-alliance-overview" class="pt-[60px] md-fs:pt-[90px] pb-[60px] md-fs:pb-[90px] bg-white">
    <div class="relative max-w-7xl mx-auto px-4">
        <div class="mb-10">
            <h2 class="font-sharp-bo fs-vmta-80 uppercase font-bold leading-[1.3] text-center text-[#0b7f7c] vmta-letter-spacing-0">
                {{ $locale === 'vi' ? 'Tổng quan mạng lưới' : 'Network overview' }}
            </h2>
            <p class="font-utm-helve mt-3 text-base leading-[1.7] text-slate-700 text-center">
                {!! $locale === 'vi'
                    ? 'Một hệ sinh thái khép kín - vận hành tập trung<br>Mạng lưới VMTA được xây dựng dựa trên 4 trụ cột:'
                    : 'A closed-loop ecosystem with centralized orchestration<br>The VMTA network is built on 4 pillars:' !!}
            </p>
        </div>

        <div class="mx-auto w-full">
            <div class="alliance-overview-network mx-auto w-full rounded-xl bg-[#d31e45] px-5 py-[15px] text-center text-white shadow-sm">
                <h3 class="font-utm-helve fs-vmta-30 uppercase font-bold leading-[1.35]">
                    {!! $locale === 'vi' ? 'VMTA - Liên minh Du lịch Y tế<br>Việt Nam' : e($networkTitle) !!}
                </h3>
            </div>

            <div class="alliance-overview-pillars mx-auto mt-8 md-fs:mt-12 grid grid-cols-1 sm-fs:grid-cols-2 md-fs:grid-cols-4 gap-4 md-fs:gap-6">
                @foreach($pillars as $pillar)
                    <div class="min-h-[76px] rounded-xl bg-[#d31e45] px-5 py-[15px] text-center text-white shadow-sm flex flex-col items-center justify-center">
                        <h4 class="font-utm-helve fs-vmta-25 font-bold uppercase leading-tight">{{ $pillar['title'] }}</h4>
                        <p class="font-utm-helve mt-[5px] text-[18px] uppercase leading-snug">{{ $pillar['detail'] }}</p>
                    </div>
                @endforeach
            </div>

            <p class="mt-7 text-center font-utm-helve fs-vmta-25 uppercase font-bold text-slate-900">
                {{ $locale === 'vi' ? '(Sơ đồ tổ chức)' : '(Network structure)' }}
            </p>
        </div>

        <div class="font-utm-helve leading-[1.7] mt-5 text-center text-slate-700">
            <p>
                {{ $locale === 'vi'
                    ? 'Tất cả được kết nối thông qua hệ điều phối trung tâm, đảm bảo sự liền mạch trong toàn bộ hành trình khách hàng.'
                    : 'All pillars are connected through a central orchestration system to ensure continuity across the full customer journey.' }}
            </p>
        </div>
    </div>
</section>
