<form method="POST" action="{{ route('inquiry.' . app()->getLocale() . '.contact.store') }}" class="space-y-4">
    @csrf
    @honeypot

    <div>
        <label class="block text-sm font-medium" for="name">{{ __('inquiry::inquiry.field_name') }}</label>
        <input id="name" name="name" type="text" required maxlength="120" value="{{ old('name') }}"
               class="mt-1 w-full rounded border-slate-300">
        @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="email">{{ __('inquiry::inquiry.field_email') }}</label>
        <input id="email" name="email" type="email" required maxlength="160" value="{{ old('email') }}"
               class="mt-1 w-full rounded border-slate-300">
        @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="phone">{{ __('inquiry::inquiry.field_phone') }}</label>
        <input id="phone" name="phone" type="tel" required maxlength="30" value="{{ old('phone') }}"
               class="mt-1 w-full rounded border-slate-300">
        @error('phone') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="block text-sm font-medium" for="message">{{ __('inquiry::inquiry.field_message') }}</label>
        <textarea id="message" name="message" rows="5" required maxlength="5000"
                  class="mt-1 w-full rounded border-slate-300">{{ old('message') }}</textarea>
        @error('message') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>

    <label class="flex items-start gap-2 text-sm">
        <input type="checkbox" name="consent_given" value="1" required {{ old('consent_given') ? 'checked' : '' }}>
        <span>{{ __('inquiry::inquiry.field_consent') }}</span>
    </label>
    @error('consent_given') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

    <button type="submit" class="rounded bg-blue-600 px-6 py-2 text-white font-medium hover:bg-blue-700">
        {{ __('inquiry::inquiry.submit') }}
    </button>
</form>
