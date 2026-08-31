<?php

namespace Packages\Core\Src\Services;

use Google\Client as Google_Client;
use Google\Service\Drive as Google_Service_Drive;
use Illuminate\Support\Facades\Http;
use Packages\Core\Src\Models\GoogleDriveCredential;
use Packages\Core\Src\Repositories\Interfaces\GoogleDriveCredentialRepositoryInterface;

/**
 * GoogleDrive Service
 *
 * Handles Google Drive OAuth flow and client management.
 */
class GoogleDriveService
{
    private ?Google_Client $client = null;

    public function __construct(
        private GoogleDriveCredentialRepositoryInterface $credentialRepository,
        private EncryptionService $encryption
    ) {}

    /**
     * Get authenticated Google Client
     */
    public function getClient(): ?Google_Client
    {
        if ($this->client) {
            return $this->client;
        }

        $credential = $this->credentialRepository->getActive();

        if (! $credential) {
            return null;
        }

        try {
            $this->client = new Google_Client;
            $this->client->setClientId(config('file-manager.google_drive.client_id'));
            $this->client->setClientSecret(config('file-manager.google_drive.client_secret'));

            $tokenData = json_decode($this->encryption->decrypt($credential->access_token_enc), true);
            $this->client->setAccessToken($tokenData);

            // Refresh if expired
            if ($credential->expiresSoon()) {
                $this->refreshToken($credential);
            }

            return $this->client;
        } catch (\Exception $e) {
            \Log::error('GoogleDriveService: Failed to get client', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate OAuth URL
     */
    public function getAuthUrl(string $redirectUri): string
    {
        $client = new Google_Client;
        $client->setClientId(config('file-manager.google_drive.client_id'));
        $client->setClientSecret(config('file-manager.google_drive.client_secret'));
        $client->setRedirectUri($redirectUri);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->addScope(Google_Service_Drive::DRIVE_FILE);

        // SECURITY: Add state parameter to prevent CSRF
        $state = bin2hex(random_bytes(16));
        session()->put('google_drive_oauth_state', $state);
        $client->setState($state);

        return $client->createAuthUrl();
    }

    /**
     * Handle OAuth callback and save tokens
     */
    public function handleCallback(string $code, string $redirectUri): array
    {
        try {
            $client = new Google_Client;
            $client->setClientId(config('file-manager.google_drive.client_id'));
            $client->setClientSecret(config('file-manager.google_drive.client_secret'));
            $client->setRedirectUri($redirectUri);

            $token = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                return [
                    'success' => false,
                    'message' => $token['error_description'] ?? $token['error'],
                ];
            }

            // Get user info
            $client->setAccessToken($token);
            $service = new Google_Service_Drive($client);
            $about = $service->about->get(['fields' => 'user']);
            $email = $about->getUser()->getEmailAddress();

            // Generate a unique ID for encryption context
            $credentialId = (string) now()->timestamp;

            // Deactivate existing credentials
            $this->credentialRepository->deactivateAll();

            // Save new credential
            $credential = $this->credentialRepository->create([
                'email' => $email,
                'access_token_enc' => $this->encryption->encrypt(
                    json_encode($token),
                    'google_drive_credentials',
                    'access_token_enc',
                    $credentialId
                ),
                'refresh_token_enc' => $this->encryption->encrypt(
                    $token['refresh_token'] ?? '',
                    'google_drive_credentials',
                    'refresh_token_enc',
                    $credentialId
                ),
                'expires_at' => now()->addSeconds($token['expires_in'] ?? 3600),
                'folder_id' => config('file-manager.google_drive.folder_id'),
                'is_active' => true,
            ]);

            return [
                'success' => true,
                'email' => $email,
                'credential_id' => $credential->id,
            ];
        } catch (\Exception $e) {
            \Log::error('GoogleDriveService: OAuth callback failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Refresh expired token
     */
    private function refreshToken(GoogleDriveCredential $credential): bool
    {
        try {
            $refreshToken = $this->encryption->decrypt($credential->refresh_token_enc);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('file-manager.google_drive.client_id'),
                'client_secret' => config('file-manager.google_drive.client_secret'),
                'refresh_token' => $refreshToken,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                $token = $response->json();
                $token['refresh_token'] = $refreshToken;

                $this->credentialRepository->update($credential->id, [
                    'access_token_enc' => $this->encryption->encrypt(
                        json_encode($token),
                        'google_drive_credentials',
                        'access_token_enc',
                        (string) $credential->id
                    ),
                    'expires_at' => now()->addSeconds($token['expires_in'] ?? 3600),
                ]);

                $this->client->setAccessToken($token);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('GoogleDriveService: Token refresh failed', [
                'credential_id' => $credential->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check if connected to Google Drive
     */
    public function isConnected(): bool
    {
        $credential = $this->credentialRepository->getActive();

        return $credential !== null;
    }

    /**
     * Get connected email
     */
    public function getConnectedEmail(): ?string
    {
        $credential = $this->credentialRepository->getActive();

        return $credential?->email;
    }

    /**
     * Disconnect (revoke) Google Drive
     */
    public function disconnect(): bool
    {
        return $this->credentialRepository->deactivateAll() > 0;
    }

    /**
     * Get folder ID for uploads
     */
    public function getFolderId(): ?string
    {
        $credential = $this->credentialRepository->getActive();

        return $credential?->folder_id ?? config('file-manager.google_drive.folder_id');
    }
}
