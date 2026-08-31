{{--
    CTA button with 3-level fallback (entity → site.app_cta_url_default → catalog config).
    Usage: @include('catalog::partials.cta-button', ['url' => $service->cta_app_url, 'label' => __('catalog::public.cta.book_now')])
--}}
@php
    $resolved = app(\Packages\Catalog\Src\Services\CatalogService::class)->resolveCta($url ?? null);
@endphp
@if($resolved)
    <a href="{{ $resolved }}"
       target="_blank" rel="noopener noreferrer"
       class="inline-flex items-center gap-2 rounded bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 text-sm font-medium transition">
        {{ $label ?? __('catalog::public.cta.book_now') }}
        <span aria-hidden="true">→</span>
    </a>
@endif
