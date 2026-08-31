{{--
    Quick inquiry partial intended to be included in Catalog detail pages.
    Usage: @include('inquiry::public.quick-inquiry-form', ['refType' => 'service', 'refId' => $service->id])
--}}
<form method="POST" action="{{ route('inquiry.' . app()->getLocale() . '.quick.store') }}" class="space-y-3 rounded border border-slate-200 p-4 bg-slate-50">
    @csrf
    @honeypot
    <input type="hidden" name="source_ref_type" value="{{ $refType }}">
    <input type="hidden" name="source_ref_id" value="{{ $refId }}">

    <h3 class="text-lg font-semibold">{{ __('inquiry::inquiry.quick_heading') }}</h3>

    @if(session('status'))
        <div class="rounded bg-green-50 border border-green-200 p-3 text-green-800 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div>
        <input name="name" type="text" required maxlength="120" value="{{ old('name') }}"
               placeholder="{{ __('inquiry::inquiry.field_name') }}"
               class="w-full rounded border-slate-300">
        @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-2 gap-2">
        <div>
            <input name="phone" type="tel" required maxlength="30" value="{{ old('phone') }}"
                   placeholder="{{ __('inquiry::inquiry.field_phone') }}"
                   class="w-full rounded border-slate-300">
            @error('phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <input name="email" type="email" maxlength="160" value="{{ old('email') }}"
                   placeholder="{{ __('inquiry::inquiry.field_email') }}"
                   class="w-full rounded border-slate-300">
            @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <textarea name="message" rows="3" maxlength="2000"
                  placeholder="{{ __('inquiry::inquiry.field_message') }}"
                  class="w-full rounded border-slate-300">{{ old('message') }}</textarea>
        @error('message') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <label class="flex items-start gap-2 text-xs">
        <input type="checkbox" name="consent_given" value="1" required {{ old('consent_given') ? 'checked' : '' }}>
        <span>{{ __('inquiry::inquiry.field_consent') }}</span>
    </label>
    @error('consent_given') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror

    <button type="submit" class="w-full rounded bg-blue-600 px-4 py-2 text-white font-medium hover:bg-blue-700">
        {{ __('inquiry::inquiry.submit') }}
    </button>
</form>
