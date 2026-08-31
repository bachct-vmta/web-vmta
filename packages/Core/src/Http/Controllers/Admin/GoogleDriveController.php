<?php

namespace Packages\Core\Src\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Services\GoogleDriveService;

/**
 * GoogleDrive Controller
 *
 * Handles Google Drive OAuth flow and connection management.
 */
class GoogleDriveController extends BaseController
{
    public function __construct(
        private GoogleDriveService $googleDriveService
    ) {}

    /**
     * Redirect to Google OAuth consent screen
     */
    public function redirect()
    {
        // Verify Google Drive is configured
        if (! config('file-manager.google_drive.client_id') || ! config('file-manager.google_drive.client_secret')) {
            return redirect()
                ->route(admin_route_name('media.settings.index'))
                ->with('error', 'Google Drive chưa được cấu hình. Vui lòng thêm GOOGLE_DRIVE_CLIENT_ID và GOOGLE_DRIVE_CLIENT_SECRET vào file .env');
        }

        $redirectUri = route(admin_route_name('media.google-drive.callback'));
        $authUrl = $this->googleDriveService->getAuthUrl($redirectUri);

        return redirect()->away($authUrl);
    }

    /**
     * Handle OAuth callback from Google
     */
    public function callback(Request $request)
    {
        // Handle error from Google
        if ($request->has('error')) {
            return redirect()
                ->route(admin_route_name('media.settings.index'))
                ->with('error', 'Google Drive authorization failed: '.$request->input('error_description', $request->input('error')));
        }

        // Verify code is present
        if (! $request->has('code')) {
            return redirect()
                ->route(admin_route_name('media.settings.index'))
                ->with('error', 'Authorization code not received from Google');
        }

        // SECURITY: Verify OAuth state to prevent CSRF
        $expectedState = session()->pull('google_drive_oauth_state');
        if (! $expectedState || $request->input('state') !== $expectedState) {
            return redirect()
                ->route(admin_route_name('media.settings.index'))
                ->with('error', 'Invalid OAuth state. Please try again.');
        }

        $redirectUri = route(admin_route_name('media.google-drive.callback'));
        $result = $this->googleDriveService->handleCallback($request->input('code'), $redirectUri);

        if ($result['success']) {
            return redirect()
                ->route(admin_route_name('media.settings.index'))
                ->with('success', 'Đã kết nối Google Drive: '.$result['email']);
        }

        return redirect()
            ->route(admin_route_name('media.settings.index'))
            ->with('error', 'Không thể kết nối Google Drive: '.($result['message'] ?? 'Unknown error'));
    }

    /**
     * Disconnect Google Drive
     */
    public function disconnect(Request $request)
    {
        $this->googleDriveService->disconnect();

        return redirect()
            ->route(admin_route_name('media.settings.index'))
            ->with('success', 'Đã ngắt kết nối Google Drive');
    }

    /**
     * Get connection status (API endpoint)
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'connected' => $this->googleDriveService->isConnected(),
            'email' => $this->googleDriveService->getConnectedEmail(),
        ]);
    }
}
