{{-- Patient contact form column --}}
<div id="patient-form" class="lg:border-r lg:border-white/60 lg:pr-8 xl:pr-12" style="scroll-margin-top: 100px;">
    @php $bag = $errors->getBag('patient'); @endphp
    <h2 class="font-sharp-bo text-[30px] md:text-[34px] uppercase font-bold leading-tight text-[#0b7f7c] mb-2">
        {{ __('inquiry::inquiry.patient_heading') }}
    </h2>
    <p class="font-utm-helve text-slate-700 text-base mb-8">
        {{ __('inquiry::inquiry.patient_subheading') }}
    </p>

    @if(session('status'))
        <div class="mb-4 rounded bg-green-50 border border-green-200 px-4 py-3 text-green-800 font-utm-helve text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if($bag->isNotEmpty())
        <div class="mb-4 rounded bg-red-50 border border-red-200 px-4 py-3 text-red-700 font-utm-helve text-sm">
            {{ __('inquiry::inquiry.form_error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('inquiry.' . app()->getLocale() . '.contact.store') }}" class="space-y-3">
        @csrf
        @honeypot

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <input id="patient_name" name="name" type="text" required maxlength="120"
                       value="{{ $bag->isNotEmpty() ? old('name') : '' }}"
                       placeholder="{{ __('inquiry::inquiry.field_name') }}"
                       class="w-full px-4 py-4 bg-[#0b7f7c] text-white placeholder-white/90 font-utm-helve text-base focus:outline-none focus:ring-2 focus:ring-[#d31e45]">
                @error('name', 'patient') <p class="text-red-600 text-xs mt-1 font-utm-helve">{{ $message }}</p> @enderror
            </div>

            <div>
                <input id="patient_email" name="email" type="email" required maxlength="160"
                       value="{{ $bag->isNotEmpty() ? old('email') : '' }}"
                       placeholder="{{ __('inquiry::inquiry.field_email') }}"
                       class="w-full px-4 py-4 bg-[#0b7f7c] text-white placeholder-white/90 font-utm-helve text-base focus:outline-none focus:ring-2 focus:ring-[#d31e45]">
                @error('email', 'patient') <p class="text-red-600 text-xs mt-1 font-utm-helve">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <input id="patient_phone" name="phone" type="tel" required maxlength="30"
                   value="{{ $bag->isNotEmpty() ? old('phone') : '' }}"
                   placeholder="{{ __('inquiry::inquiry.field_phone') }}"
                   class="w-full px-4 py-4 bg-[#0b7f7c] text-white placeholder-white/90 font-utm-helve text-base focus:outline-none focus:ring-2 focus:ring-[#d31e45]">
            @error('phone', 'patient') <p class="text-red-600 text-xs mt-1 font-utm-helve">{{ $message }}</p> @enderror
        </div>

        <div>
            <input id="patient_condition" name="condition" type="text" maxlength="160"
                   value="{{ $bag->isNotEmpty() ? old('condition') : '' }}"
                   placeholder="{{ __('inquiry::inquiry.field_condition') }}"
                   class="w-full px-4 py-4 bg-[#0b7f7c] text-white placeholder-white/90 font-utm-helve text-base focus:outline-none focus:ring-2 focus:ring-[#d31e45]">
            @error('condition', 'patient') <p class="text-red-600 text-xs mt-1 font-utm-helve">{{ $message }}</p> @enderror
        </div>

        <div>
            <textarea id="patient_message" name="message" rows="5" maxlength="5000"
                      placeholder="{{ __('inquiry::inquiry.field_note') }}"
                      class="w-full px-4 py-4 bg-[#0b7f7c] text-white placeholder-white/90 font-utm-helve text-base focus:outline-none focus:ring-2 focus:ring-[#d31e45] resize-none">{{ $bag->isNotEmpty() ? old('message') : '' }}</textarea>
            @error('message', 'patient') <p class="text-red-600 text-xs mt-1 font-utm-helve">{{ $message }}</p> @enderror
        </div>

        <div class="text-center">
            <button type="submit"
                    class="rounded-lg bg-[#0b7f7c] px-10 py-4 font-sharp-bo text-white uppercase font-bold text-sm hover:bg-[#096d6a] transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0b7f7c]">
                {{ __('inquiry::inquiry.submit_patient') }}
            </button>
        </div>
    </form>

    @if(session('status') || $bag->isNotEmpty())
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById('patient-form');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        </script>
    @endif
</div>
