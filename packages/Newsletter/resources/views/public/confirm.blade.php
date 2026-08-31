@extends('site::layouts.public')

@section('content')
<section class="max-w-2xl mx-auto px-4 py-20 text-center">
    @if($success)
        <h1 class="text-3xl font-bold text-[#0b7f7c] mb-4">{{ __('newsletter::newsletter.confirm_success') }}</h1>
        @if($subscriber)
            <p class="text-gray-600">{{ $subscriber->email }}</p>
        @endif
    @else
        <h1 class="text-3xl font-bold text-red-700 mb-4">{{ __('newsletter::newsletter.confirm_failed') }}</h1>
    @endif

    <a href="{{ url('/') }}" class="inline-block mt-6 px-6 py-2 bg-[#0b7f7c] text-white rounded-lg hover:bg-[#086663]">
        {{ __('newsletter::newsletter.admin.heading') }}
    </a>
</section>
@endsection
