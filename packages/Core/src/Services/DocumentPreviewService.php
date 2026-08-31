<?php

namespace Packages\Core\Src\Services;

/**
 * DocumentPreviewService
 *
 * Handles document preview using external services (Google/Microsoft).
 */
class DocumentPreviewService
{
    /**
     * Preview provider URLs
     */
    protected array $providers = [
        'google' => 'https://docs.google.com/gview?embedded=true&url={url}',
        'microsoft' => 'https://view.officeapps.live.com/op/view.aspx?src={url}',
    ];

    /**
     * Supported MIME types for preview
     */
    protected array $supportedMimes = [
        'application/pdf' => ['google', 'microsoft'],
        'application/msword' => ['microsoft'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['microsoft'],
        'application/vnd.ms-excel' => ['microsoft'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['microsoft'],
        'application/vnd.ms-powerpoint' => ['microsoft'],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['microsoft'],
    ];

    /**
     * Extension to MIME type mapping
     */
    protected array $extensionMimeMap = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    /**
     * Get preview URL for a document
     */
    public function getPreviewUrl(string $fileUrl, ?string $mimeType = null): ?string
    {
        if (! is_document_preview_enabled()) {
            return null;
        }

        $provider = document_preview_provider();

        if (! $this->canPreview($fileUrl, $mimeType)) {
            return null;
        }

        $providerUrl = $this->providers[$provider] ?? null;

        if (! $providerUrl) {
            return null;
        }

        return str_replace('{url}', urlencode($fileUrl), $providerUrl);
    }

    /**
     * Check if a file can be previewed
     */
    public function canPreview(string $fileUrl, ?string $mimeType = null): bool
    {
        if (! is_document_preview_enabled()) {
            return false;
        }

        // Determine MIME type from extension if not provided
        if (! $mimeType) {
            $extension = strtolower(pathinfo($fileUrl, PATHINFO_EXTENSION));
            $mimeType = $this->extensionMimeMap[$extension] ?? null;
        }

        if (! $mimeType) {
            return false;
        }

        $provider = document_preview_provider();
        $supportedProviders = $this->supportedMimes[$mimeType] ?? [];

        return in_array($provider, $supportedProviders);
    }

    /**
     * Get list of supported extensions
     */
    public function getSupportedExtensions(): array
    {
        return array_keys($this->extensionMimeMap);
    }

    /**
     * Check if extension is supported
     */
    public function isExtensionSupported(string $extension): bool
    {
        $extension = strtolower($extension);

        return isset($this->extensionMimeMap[$extension]);
    }

    /**
     * Get available providers
     */
    public function getProviders(): array
    {
        return array_keys($this->providers);
    }
}
