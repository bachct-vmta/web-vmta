@extends('site::layouts.public')

@section('content')
<section class="max-w-2xl mx-auto px-4 py-12">
    <div class="mb-6 rounded bg-red-50 border border-red-200 p-4">
        <h1 class="text-2xl font-bold text-red-800 mb-2">{{ __('inquiry::inquiry.emergency_heading') }}</h1>
        <p class="text-red-700 text-sm">{{ __('inquiry::inquiry.emergency_blurb') }}</p>

        @if($hotline ?? null)
            <a href="tel:{{ preg_replace('/\s+/', '', $hotline) }}"
               class="mt-4 inline-flex items-center gap-2 rounded bg-red-600 px-5 py-2 text-white font-semibold hover:bg-red-700">
                📞 {{ __('inquiry::inquiry.call_now') }}: {{ $hotline }}
            </a>
        @endif
    </div>

    @if(session('status'))
        <div class="mb-6 rounded bg-green-50 border border-green-200 p-4 text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('inquiry.' . app()->getLocale() . '.emergency.store') }}" class="space-y-4">
        @csrf
        @honeypot

        <div>
            <label class="block text-sm font-medium" for="name">{{ __('inquiry::inquiry.field_name') }}</label>
            <input id="name" name="name" type="text" required maxlength="120" value="{{ old('name') }}"
                   class="mt-1 w-full rounded border-slate-300">
            @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="phone">{{ __('inquiry::inquiry.field_phone') }}</label>
            <input id="phone" name="phone" type="tel" required maxlength="30" value="{{ old('phone') }}"
                   class="mt-1 w-full rounded border-slate-300">
            @error('phone') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium" for="message">{{ __('inquiry::inquiry.field_message') }}</label>
            <textarea id="message" name="message" rows="3" maxlength="1000"
                      class="mt-1 w-full rounded border-slate-300">{{ old('message') }}</textarea>
            @error('message') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-start gap-2 text-sm">
            <input type="checkbox" name="consent_given" value="1" required {{ old('consent_given') ? 'checked' : '' }}>
            <span>{{ __('inquiry::inquiry.field_consent') }}</span>
        </label>
        @error('consent_given') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

        <button type="submit" class="rounded bg-red-600 px-6 py-2 text-white font-semibold hover:bg-red-700">
            {{ __('inquiry::inquiry.submit_urgent') }}
        </button>
    </form>
</section>
@endsection
