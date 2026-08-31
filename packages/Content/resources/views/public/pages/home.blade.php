@extends('site::layouts.public')

@php use Packages\Content\Src\Enums\HomeSectionPosition; @endphp

@section('content')
    @php
        $aboutSection = $sections->first(fn($s) => $s->position === HomeSectionPosition::About);
        $aboutTranslation = $aboutSection?->translate($locale) ?? $aboutSection?->translations->first();
    @endphp
    @foreach($sections as $section)
        @php $translation = $section->translate($locale) ?? $section->translations->first(); @endphp
        @if($section->position === HomeSectionPosition::About)
            @continue {{-- About is merged inside Values section --}}
        @endif
        @include('content::public.partials.home.' . $section->position->value, [
            'section' => $section,
            'translation' => $translation,
            'aboutSection' => $section->position === HomeSectionPosition::Values ? $aboutSection : null,
            'aboutTranslation' => $section->position === HomeSectionPosition::Values ? $aboutTranslation : null,
            'coreValuesSection' => $section->position === HomeSectionPosition::Values ? $coreValuesSection : null,
        ])
    @endforeach
    @include('content::public.partials.home.news-teaser', ['posts' => $latestPosts, 'locale' => $locale])
@endsection

@push('scripts')
    @vite('resources/js/hls-bootstrap.js')
@endpush
