@extends('site::layouts.public')

@section('content')
<div id="content" role="main" class="content-area scroll-smooth">
    @include('content::public.partials.alliance.hero', ['section' => $heroSection])
    @include('content::public.partials.alliance.overview', ['section' => $overviewSection])
    @includeIf('catalog::public.partials.alliance-partner-list')
    @include('content::public.partials.alliance.standards', ['section' => $standardsSection])
    @include('content::public.partials.alliance.map', ['section' => $mapSection])
    @include('content::public.partials.alliance.join-form', ['section' => $joinFormSection])
</div>
@endsection
