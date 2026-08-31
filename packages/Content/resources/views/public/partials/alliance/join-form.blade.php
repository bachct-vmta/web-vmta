@php
    $locale = app()->getLocale();
    $placeholders = $locale === 'vi'
        ? [
            'name' => 'Tên',
            'email' => 'Email',
            'phone' => 'Số điện thoại',
            'industry' => 'Ngành nghề',
            'company_name' => 'Tên doanh nghiệp',
            'message' => 'Ghi chú',
            'submit' => 'gửi ngay',
        ]
        : [
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'industry' => 'Industry',
            'company_name' => 'Company name',
            'message' => 'Note',
            'submit' => 'submit now',
        ];
@endphp
<section id="section-alliance-join" class="pt-[60px] md-fs:pt-[90px] pb-[60px] md-fs:pb-[90px] bg-white">
    <div class="relative max-w-7xl mx-auto px-4">
        <div class="text-center mb-8">
            <h2 class="font-sharp-bo fs-vmta-80 uppercase font-bold leading-[1.3] text-[#0b7f7c] vmta-letter-spacing-0">
                {{ $locale === 'vi' ? 'Tham Gia liên minh' : 'Join the alliance' }}
            </h2>
            <p class="font-utm-helve fs-vmta-25 mt-3 text-slate-700 leading-[1.5]">
                {!! $locale === 'vi'
                    ? 'Trở thành một phần của hệ sinh thái VMTA<br>VMTA không chỉ kết nối các đơn vị - mà kiến tạo một mạng lưới giá trị bền vững.'
                    : 'Become part of the VMTA ecosystem<br>VMTA does not only connect organizations - it builds a sustainable value network.' !!}
            </p>
        </div>

        @if(session('partner_status'))
            <div class="mb-6 rounded bg-emerald-50 border border-emerald-200 p-4 text-emerald-800 text-center">
                {{ session('partner_status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded bg-red-50 border border-red-200 p-4 text-red-800">
                @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 md-fs:grid-cols-2 gap-8 md-fs:gap-12 items-start">
            <div>
                <img src="{{ asset('images/alliance/join.jpg') }}"
                     alt="{{ $locale === 'vi' ? 'Tham Gia liên minh' : 'Join the alliance' }}"
                     class="w-full rounded-[30px] object-cover shadow-sm" loading="lazy">
            </div>
            <form method="POST" action="{{ route('inquiry.'.$locale.'.partner.store') }}"
                  class="grid grid-cols-1 md:grid-cols-2 gap-3 text-slate-800">
                @csrf
                @honeypot

                <div>
                    <label for="alliance-partner-name" class="sr-only">{{ $placeholders['name'] }}</label>
                    <input id="alliance-partner-name" type="text" name="name" value="{{ old('name') }}" required
                           placeholder="{{ $placeholders['name'] }}"
                           class="min-h-[57px] w-full border-0 bg-[#0b7f7c] px-4 py-4 font-utm-helve text-base text-white placeholder-white focus:outline-none focus:ring-2 focus:ring-[#d31e45]">
                </div>
                <div>
                    <label for="alliance-partner-email" class="sr-only">{{ $placeholders['email'] }}</label>
                    <input id="alliance-partner-email" type="email" name="email" value="{{ old('email') }}" required
                           placeholder="{{ $placeholders['email'] }}"
                           class="min-h-[57px] w-full border-0 bg-[#0b7f7c] px-4 py-4 font-utm-helve text-base text-white placeholder-white focus:outline-none focus:ring-2 focus:ring-[#d31e45]">
                </div>
                <div>
                    <label for="alliance-partner-phone" class="sr-only">{{ $placeholders['phone'] }}</label>
                    <input id="alliance-partner-phone" type="tel" name="phone" value="{{ old('phone') }}" required
                           placeholder="{{ $placeholders['phone'] }}"
                           class="min-h-[57px] w-full border-0 bg-[#0b7f7c] px-4 py-4 font-utm-helve text-base text-white placeholder-white focus:outline-none focus:ring-2 focus:ring-[#d31e45]">
                </div>
                <div>
                    <label for="alliance-partner-industry" class="sr-only">{{ $placeholders['industry'] }}</label>
                    <input id="alliance-partner-industry" type="text" name="industry" value="{{ old('industry') }}"
                           placeholder="{{ $placeholders['industry'] }}"
                           class="min-h-[57px] w-full border-0 bg-[#0b7f7c] px-4 py-4 font-utm-helve text-base text-white placeholder-white focus:outline-none focus:ring-2 focus:ring-[#d31e45]">
                </div>
                <div class="md:col-span-2">
                    <label for="alliance-partner-company-name" class="sr-only">{{ $placeholders['company_name'] }}</label>
                    <input id="alliance-partner-company-name" type="text" name="company_name" value="{{ old('company_name') }}"
                           placeholder="{{ $placeholders['company_name'] }}"
                           class="min-h-[57px] w-full border-0 bg-[#0b7f7c] px-4 py-4 font-utm-helve text-base text-white placeholder-white focus:outline-none focus:ring-2 focus:ring-[#d31e45]">
                </div>
                <div class="md:col-span-2">
                    <label for="alliance-partner-message" class="sr-only">{{ $placeholders['message'] }}</label>
                    <textarea id="alliance-partner-message" name="message" rows="5"
                              placeholder="{{ $placeholders['message'] }}"
                              class="min-h-[150px] w-full resize-none border-0 bg-[#0b7f7c] px-4 py-4 font-utm-helve text-base text-white placeholder-white focus:outline-none focus:ring-2 focus:ring-[#d31e45]">{{ old('message') }}</textarea>
                </div>
                <div class="md:col-span-2 flex justify-start pt-2">
                    <button type="submit"
                            class="rounded-lg bg-[#d31e45] px-10 py-2.5 font-utm-helve text-[15px] font-bold uppercase leading-[1.5] text-white transition hover:bg-[#b01838]">
                        {{ $placeholders['submit'] }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
