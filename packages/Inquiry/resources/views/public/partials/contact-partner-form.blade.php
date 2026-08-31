{{-- Partner inquiry form column --}}
<div id="partner-form" class="lg:pl-8 xl:pl-12" style="scroll-margin-top: 100px;">
    @php $bag = $errors->getBag('partner'); @endphp
    <h2 class="font-sharp-bo text-[30px] md:text-[34px] uppercase font-bold leading-tight text-[#0b7f7c] mb-2 lg:text-right min-h-[90px]">
        {{ __('inquiry::inquiry.partner_heading') }}
    </h2>
    <p class="font-utm-helve text-slate-700 text-base mb-8 lg:text-right">
        {{ __('inquiry::inquiry.partner_subheading') }}
    </p>

    @if(session('partner_status'))
        <div class="mb-4 rounded bg-green-50 border border-green-200 px-4 py-3 text-green-800 font-utm-helve text-sm">
            {{ session('partner_status') }}
        </div>
    @endif

    @if($bag->isNotEmpty())
        <div class="mb-4 rounded bg-red-50 border border-red-200 px-4 py-3 text-red-700 font-utm-helve text-sm">
            {{ __('inquiry::inquiry.form_error') }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('inquiry.' . app()->getLocale() . '.partner.store') }}"
          class="space-y-3">
        @csrf
        @honeypot

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <input id="partner_name" name="name" type="text" required maxlength="120"
                       value="{{ $bag->isNotEmpty() ? old('name') : '' }}"
                       placeholder="{{ __('inquiry::inquiry.field_name') }}"
                       class="w-full px-4 py-4 bg-[#0b7f7c] text-white placeholder-white/90 font-utm-helve text-base focus:outline-none focus:ring-2 focus:ring-[#d31e45]">
                @error('name', 'partner') <p class="text-red-600 text-xs mt-1 font-utm-helve">{{ $message }}</p> @enderror
            </div>

            <div>
                <input id="partner_email" name="email" type="email" required maxlength="160"
                       value="{{ $bag->isNotEmpty() ? old('email') : '' }}"
                       placeholder="{{ __('inquiry::inquiry.field_email') }}"
                       class="w-full px-4 py-4 bg-[#0b7f7c] text-white placeholder-white/90 font-utm-helve text-base focus:outline-none focus:ring-2 focus:ring-[#d31e45]">
                @error('email', 'partner') <p class="text-red-600 text-xs mt-1 font-utm-helve">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <input id="partner_phone" name="phone" type="tel" required maxlength="30"
                       value="{{ $bag->isNotEmpty() ? old('phone') : '' }}"
                       placeholder="{{ __('inquiry::inquiry.field_phone') }}"
                       class="w-full px-4 py-4 bg-[#0b7f7c] text-white placeholder-white/90 font-utm-helve text-base focus:outline-none focus:ring-2 focus:ring-[#d31e45]">
                @error('phone', 'partner') <p class="text-red-600 text-xs mt-1 font-utm-helve">{{ $message }}</p> @enderror
            </div>

            <div>
                <input id="partner_industry" name="industry" type="text" maxlength="120"
                       value="{{ $bag->isNotEmpty() ? old('industry') : '' }}"
                       placeholder="{{ __('inquiry::inquiry.field_industry') }}"
                       class="w-full px-4 py-4 bg-[#0b7f7c] text-white placeholder-white/90 font-utm-helve text-base focus:outline-none focus:ring-2 focus:ring-[#d31e45]">
                @error('industry', 'partner') <p class="text-red-600 text-xs mt-1 font-utm-helve">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <input id="partner_company_name" name="company_name" type="text" maxlength="120"
                   value="{{ $bag->isNotEmpty() ? old('company_name') : '' }}"
                   placeholder="{{ __('inquiry::inquiry.field_company_name') }}"
                   class="w-full px-4 py-4 bg-[#0b7f7c] text-white placeholder-white/90 font-utm-helve text-base focus:outline-none focus:ring-2 focus:ring-[#d31e45]">
            @error('company_name', 'partner') <p class="text-red-600 text-xs mt-1 font-utm-helve">{{ $message }}</p> @enderror
        </div>

        <div>
            <textarea id="partner_message" name="message" rows="5" maxlength="5000"
                      placeholder="{{ __('inquiry::inquiry.field_note') }}"
                      class="w-full px-4 py-4 bg-[#0b7f7c] text-white placeholder-white/90 font-utm-helve text-base focus:outline-none focus:ring-2 focus:ring-[#d31e45] resize-none">{{ $bag->isNotEmpty() ? old('message') : '' }}</textarea>
            @error('message', 'partner') <p class="text-red-600 text-xs mt-1 font-utm-helve">{{ $message }}</p> @enderror
        </div>

        <div class="text-center">
            <button type="submit"
                    class="rounded-lg bg-[#0b7f7c] px-10 py-4 font-sharp-bo text-white uppercase font-bold text-sm hover:bg-[#096d6a] transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b7f7c]">
                {{ __('inquiry::inquiry.submit_partner') }}
            </button>
        </div>
    </form>

    @if(session('partner_status') || $bag->isNotEmpty())
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('partner-form');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        </script>
    @endif
</div>
