<?php

namespace Packages\Core\Src\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Models\MediaSetting;
use Packages\Core\Src\Services\GoogleDriveService;
use Packages\Core\Src\Services\StorageDriverService;

/**
 * Media Settings Controller
 *
 * Handles media configuration UI and updates.
 */
class MediaSettingsController extends BaseController
{
    public function __construct(
        private GoogleDriveService $googleDriveService,
        private StorageDriverService $storageDriverService
    ) {}

    /**
     * Display media settings page
     */
    public function index(): View
    {
        $settings = MediaSetting::getAllWithDefaults();

        // Format max file size for display (convert bytes to MB)
        $settings['media_max_file_size_mb'] = round($settings['media_max_file_size'] / 1024 / 1024, 2);

        // Format chunk size for display (convert bytes to KB)
        $settings['media_chunk_size_kb'] = round($settings['media_chunk_size'] / 1024);

        // Google Drive status
        $googleDriveConnected = $this->googleDriveService->isConnected();
        $googleDriveEmail = $this->googleDriveService->getConnectedEmail();

        // Current storage driver
        $currentDriver = $this->storageDriverService->getCurrentDriver();

        return view('core::admin.media-settings', compact(
            'settings',
            'googleDriveConnected',
            'googleDriveEmail',
            'currentDriver'
        ));
    }

    /**
     * Update media settings
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'max_file_size' => 'required|numeric|min:1|max:1024', // MB
            'allowed_mime_types' => 'required|string',
            'chunk_enabled' => 'nullable|boolean',
            'chunk_size' => 'required|numeric|min:128|max:10240', // KB
            'document_preview_enabled' => 'nullable|boolean',
            'document_preview_provider' => 'required|in:google,microsoft',
            'default_upload_folder' => 'required|string|max:255',
        ]);

        try {
            // Convert MB to bytes for max_file_size
            MediaSetting::setValue('media_max_file_size', (int) ($validated['max_file_size'] * 1024 * 1024));

            // Normalize allowed MIME types (remove spaces, lowercase)
            $mimeTypes = collect(explode(',', $validated['allowed_mime_types']))
                ->map(fn ($type) => strtolower(trim($type)))
                ->filter()
                ->implode(',');
            MediaSetting::setValue('media_allowed_mime_types', $mimeTypes);

            // Boolean settings
            MediaSetting::setValue('media_chunk_enabled', $validated['chunk_enabled'] ?? false);
            MediaSetting::setValue('media_document_preview_enabled', $validated['document_preview_enabled'] ?? false);

            // Convert KB to bytes for chunk_size
            MediaSetting::setValue('media_chunk_size', (int) ($validated['chunk_size'] * 1024));

            // String settings
            MediaSetting::setValue('media_document_preview_provider', $validated['document_preview_provider']);
            MediaSetting::setValue('media_default_upload_folder', $validated['default_upload_folder']);

            // Clear all settings cache
            MediaSetting::clearAllCache();

            return $this->success(null, trans('core::messages.update_success'));
        } catch (\Exception $e) {
            \Log::error('Media settings update failed', ['error' => $e->getMessage()]);

            return $this->error(trans('core::messages.update_error', [], 'Update failed. Please try again.'));
        }
    }
}
