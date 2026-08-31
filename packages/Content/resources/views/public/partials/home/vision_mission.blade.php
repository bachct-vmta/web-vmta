@php $items = $translation?->items ?? []; @endphp
<section class="py-[60px] md-fs:py-[80px] bg-white overflow-hidden" data-home-section="vision_mission">
    <div class="max-w-7xl mx-auto px-4 space-y-[60px] md-fs:space-y-[80px]">

        {{-- Row 1: TẦM NHÌN — 2-col layout (title left, body right) --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
            <div class="md:col-span-5">
                @if($translation?->title)
                    <h2 class="font-sharp-bo fs-vmta-80 uppercase text-[#0b7f7c] leading-none">
                        {{ $translation->title }}
                    </h2>
                @endif
            </div>
            <div class="md:col-span-7">
                {{-- .text-with-bglayout > .col-inner (vmta.vn) — bg image overflow-right --}}
                <div class="p-[3.125rem] w-[150%] pr-[50%] bg-[url('/images/home/vision-mission/asset-6-bg.jpg')] bg-[center_right] bg-contain bg-no-repeat overflow-visible">
                    @if($translation?->body)
                        {{-- HTMLPurifier-sanitized in UpdateHomeSectionRequest::prepareForValidation --}}
                        <div class="cms-body font-utm-helve text-[#d31e45] leading-relaxed italic relative">
                            <span class="text-[#d31e45] text-3xl font-serif leading-none align-top absolute left-[-5%] mr-1">&ldquo;</span>{!! $translation->body !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Row 2: SỨ MỆNH — mirror layout (bg image LEFT, title RIGHT) + 3-col audience --}}
        <div>
            {{-- Title row: text-with-bglayout-left (image left) + title (right) --}}
            <div class="grid grid-cols-1 md:grid-cols-12 items-center gap-8">
                <div class="md:col-span-9 hidden lg:block">
                    {{-- .text-with-bglayout.text-with-bglayout-left > .col-inner (vmta.vn) --}}
                    <div class="w-[150%] ml-[-34%] p-[6.25rem] pl-[35%] bg-[url('/images/home/vision-mission/asset-7-bg.jpg')] bg-left bg-contain bg-no-repeat">
                        <div class="h-px]"></div>
                    </div>
                </div>
                <div class="md:col-span-3 md:col-start-10">
                    @if($translation?->subtitle)
                        <h2 class="font-sharp-bo fs-vmta-80 uppercase text-[#0b7f7c] text-center lg:text-right leading-none">
                            {{ $translation->subtitle }}
                        </h2>
                    @endif
                </div>
            </div>
            @if(count($items) > 0)
                <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-10">
                    @foreach($items as $item)
                        <div class="text-center md:text-left">
                            <h4 class="font-utm-helve fs-vmta-20 font-bold uppercase text-[#d31e45] text-center tracking-wide">
                                {{ $item['audience'] ?? '' }}
                            </h4>
                            <p class="mt-3 font-utm-helve text-slate-700 leading-relaxed text-justify" style="text-align-last: center;">{{ $item['body'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Row 3: HLS Video --}}
        @if($section->video_url)
            <div class="rounded-xl overflow-hidden shadow-lg">
                <video data-hls-src="{{ $section->video_url }}"
                       poster="{{ asset('images/home/vision-mission/video-poster.webp') }}"
                       muted autoplay loop playsinline controls
                       class="w-full rounded-xl"
                       aria-label="{{ $translation?->title ?? 'VMTA video' }}">
                </video>
            </div>
        @endif

    </div>
</section>
