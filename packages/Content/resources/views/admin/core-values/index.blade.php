@extends('core::layouts.admin')

@section('title', __('content::content.about.positions.core_values'))

@section('content')
<div class="p-6 max-w-6xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">{{ __('content::content.about.positions.core_values') }}</h1>

    @if(session('status'))
        <div class="mb-4 rounded bg-emerald-50 border border-emerald-200 p-3 text-emerald-800" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded bg-red-50 border border-red-200 p-3 text-red-800">
            @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
        </div>
    @endif

    @include('content::admin.about._fieldset-core_values', ['position' => $position, 'section' => $section])
</div>
@endsection
