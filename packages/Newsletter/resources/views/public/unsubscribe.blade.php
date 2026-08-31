@extends('site::layouts.public')

@section('content')
<section class="max-w-2xl mx-auto px-4 py-20 text-center">
    @if($success)
        <h1 class="text-3xl font-bold text-[#0b7f7c] mb-4">{{ __('newsletter::newsletter.unsubscribe_success') }}</h1>
        @if($subscriber)
            <p class="text-gray-600">{{ $subscriber->email }}</p>
        @endif
    @elseif($subscriber === null && request()->query('email'))
        <h1 class="text-3xl font-bold text-red-700 mb-4">{{ __('newsletter::newsletter.unsubscribe_failed') }}</h1>
    @else
        <h1 class="text-2xl font-bold text-gray-800 mb-4">{{ __('newsletter::newsletter.unsubscribe_intro') }}</h1>
        <form method="GET" action="{{ route('newsletter.unsubscribe') }}" class="max-w-md mx-auto flex gap-2">
            <input type="email" name="email" required placeholder="you@example.com"
                   class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-[#0b7f7c]">
            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                {{ __('newsletter::newsletter.unsubscribe_button') }}
            </button>
        </form>
    @endif

    <a href="{{ url('/') }}" class="inline-block mt-6 px-6 py-2 bg-[#0b7f7c] text-white rounded-lg hover:bg-[#086663]">
        {{ __('newsletter::newsletter.admin.heading') }}
    </a>
</section>
@endsection
