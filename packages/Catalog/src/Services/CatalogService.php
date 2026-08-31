<?php

namespace Packages\Catalog\Src\Services;

use Packages\Catalog\Src\Repositories\Interfaces\ComboRepositoryInterface;
use Packages\Catalog\Src\Repositories\Interfaces\DestinationRepositoryInterface;
use Packages\Catalog\Src\Repositories\Interfaces\PartnerRepositoryInterface;
use Packages\Catalog\Src\Repositories\Interfaces\ServiceRepositoryInterface;
use Packages\Catalog\Src\Repositories\Interfaces\SpecialtyRepositoryInterface;
use Packages\Catalog\Src\Repositories\Interfaces\TourPackageRepositoryInterface;

/**
 * Catalog facade for cross-entity admin workflows + CTA fallback resolution.
 * Slice A only exposes resolveCta + repo wiring; admin write paths land in Slice B.
 */
class CatalogService
{
    public function __construct(
        public readonly SpecialtyRepositoryInterface $specialties,
        public readonly DestinationRepositoryInterface $destinations,
        public readonly PartnerRepositoryInterface $partners,
        public readonly ServiceRepositoryInterface $services,
        public readonly TourPackageRepositoryInterface $tours,
        public readonly ComboRepositoryInterface $combos,
    ) {}

    /**
     * Resolve CTA URL for a catalog item using a 3-level cascade:
     *   1. Entity-level `cta_app_url` (per-item override set in admin).
     *   2. Core Settings key `site.app_cta_url_default` (Phase-6 Settings, runtime-editable).
     *   3. Config `catalog.cta_app_url_default` (env: CATALOG_CTA_APP_URL_DEFAULT, ops-managed).
     *
     * Returns null only if all three are empty.
     */
    public function resolveCta(?string $entityCtaUrl): ?string
    {
        // Entity-level value is already URL-validated by the admin FormRequest.
        if (! empty($entityCtaUrl)) {
            return $entityCtaUrl;
        }

        if (function_exists('setting')) {
            $fromSettings = (string) setting('site.app_cta_url_default');
            if ($fromSettings !== '' && $this->isSafeHttpUrl($fromSettings)) {
                return $fromSettings;
            }
        }

        $fromConfig = (string) config('catalog.cta_app_url_default');

        return $fromConfig !== '' && $this->isSafeHttpUrl($fromConfig) ? $fromConfig : null;
    }

    /**
     * Settings + config fallbacks are admin/ops-managed (not user input), but we still gate
     * them to http(s) so a misconfigured `javascript:` or `data:` URL can't make it onto the
     * public CTA button.
     */
    private function isSafeHttpUrl(string $url): bool
    {
        return preg_match('#^https?://#i', $url) === 1;
    }
}
