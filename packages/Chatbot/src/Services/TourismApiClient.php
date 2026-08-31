<?php

namespace Packages\Chatbot\Src\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Packages\Chatbot\Src\Exceptions\UpstreamUnavailableException;
use Psr\Http\Message\StreamInterface;

class TourismApiClient
{
    private Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => rtrim(config('chatbot.api_base'), '/'),
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);
    }

    /**
     * Get a valid upstream Bearer token for the user (`tichhop`) role.
     */
    public function token(): string
    {
        return $this->getOrRefreshToken(
            cacheKey: config('chatbot.upstream_token_cache_key'),
            username: config('chatbot.username'),
            password: config('chatbot.password'),
            role: 'user',
        );
    }

    /**
     * Get a valid upstream Bearer token for the admin role.
     * Throws if admin creds are not configured.
     */
    public function adminToken(): string
    {
        $username = config('chatbot.admin_username');
        $password = config('chatbot.admin_password');

        if (empty($username) || empty($password)) {
            throw new UpstreamUnavailableException(
                'Tourism API admin credentials are not configured. Set CHATBOT_ADMIN_USERNAME / CHATBOT_ADMIN_PASSWORD in .env.'
            );
        }

        return $this->getOrRefreshToken(
            cacheKey: config('chatbot.upstream_admin_token_cache_key'),
            username: $username,
            password: $password,
            role: 'admin',
        );
    }

    /**
     * Create a new upstream conversation, returns conversation_id string.
     */
    public function createConversation(): string
    {
        $documentGroup = $this->resolvedSetting('document_group');
        $aiProvider = $this->resolvedSetting('ai_provider');

        $payload = [];
        if ($documentGroup !== null && $documentGroup !== '') {
            $payload['document_group'] = $documentGroup;
        }
        if ($aiProvider) {
            $payload['ai_provider'] = $aiProvider;
        }

        // Guzzle's JSON option serializes empty PHP arrays as `[]`, but FastAPI's
        // pydantic schema expects a JSON object `{}`. Force object encoding when
        // payload is empty so "search all groups" mode works.
        $body = empty($payload)
            ? '{}'
            : json_encode($payload, JSON_UNESCAPED_UNICODE);

        try {
            $response = $this->authorizedRequest('POST', '/api/chat/conversations', [
                RequestOptions::BODY => $body,
                RequestOptions::HEADERS => ['Content-Type' => 'application/json'],
            ]);

            $data = json_decode((string) $response->getBody(), true);

            return $data['id'] ?? $data['conversation_id'] ?? throw new UpstreamUnavailableException('Missing conversation id in response');
        } catch (UpstreamUnavailableException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new UpstreamUnavailableException('Failed to create upstream conversation: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Start a streaming message request, returns the Guzzle response body stream.
     *
     * Stream timeouts override the constructor's 30s default because LLM RAG
     * responses routinely exceed 30s. timeout=0 disables the wall-clock cap;
     * read_timeout still protects against an upstream that has gone silent
     * mid-stream (no bytes for N seconds → fail).
     */
    public function streamMessage(string $conversationId, string $content): StreamInterface
    {
        try {
            $response = $this->authorizedRequest(
                'POST',
                "/api/chat/conversations/{$conversationId}/messages/stream",
                [
                    RequestOptions::JSON => ['content' => $content],
                    RequestOptions::STREAM => true,
                    RequestOptions::TIMEOUT => 0,
                    RequestOptions::READ_TIMEOUT => (int) config('chatbot.stream_read_timeout', 120),
                    RequestOptions::CONNECT_TIMEOUT => 10,
                ]
            );

            return $response->getBody();
        } catch (\Throwable $e) {
            throw new UpstreamUnavailableException('Upstream stream request failed: '.$e->getMessage(), 0, $e);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Conversations (user) — §2
    // ─────────────────────────────────────────────────────────

    public function listConversations(int $skip = 0, int $limit = 30): array
    {
        return $this->jsonRequest('GET', '/api/chat/conversations', [
            RequestOptions::QUERY => ['skip' => $skip, 'limit' => min($limit, 100)],
        ]);
    }

    public function searchConversations(string $q): array
    {
        return $this->jsonRequest('GET', '/api/chat/conversations/search', [
            RequestOptions::QUERY => ['q' => $q],
        ]);
    }

    public function getConversation(string $conversationId): array
    {
        return $this->jsonRequest('GET', "/api/chat/conversations/{$conversationId}");
    }

    // ─────────────────────────────────────────────────────────
    //  Document Groups (admin) — §4
    // ─────────────────────────────────────────────────────────

    public function listDocumentGroups(): array
    {
        return $this->jsonRequest('GET', '/api/document-groups/', useAdmin: true);
    }

    public function createDocumentGroup(string $code, string $name): array
    {
        return $this->jsonRequest('POST', '/api/document-groups/', [
            RequestOptions::JSON => ['code' => $code, 'name' => $name],
        ], useAdmin: true);
    }

    public function deleteDocumentGroup(string $code): void
    {
        $this->authorizedRequest('DELETE', "/api/document-groups/{$code}", [], useAdmin: true);
    }

    // ─────────────────────────────────────────────────────────
    //  Documents (admin) — §5
    // ─────────────────────────────────────────────────────────

    public function listDocuments(?string $q = null): array
    {
        $options = [];
        if ($q !== null && $q !== '') {
            $options[RequestOptions::QUERY] = ['q' => $q];
        }

        return $this->jsonRequest('GET', '/api/documents/', $options, useAdmin: true);
    }

    /**
     * Upload a document file via multipart/form-data.
     * $file must be a Laravel UploadedFile (server-side proxy).
     */
    public function uploadDocument(UploadedFile $file, string $groupCode): array
    {
        return $this->jsonRequest('POST', '/api/documents/upload', [
            RequestOptions::MULTIPART => [
                [
                    'name' => 'file',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                    'headers' => ['Content-Type' => $file->getMimeType() ?: 'application/octet-stream'],
                ],
                [
                    'name' => 'group_code',
                    'contents' => $groupCode,
                ],
            ],
        ], useAdmin: true);
    }

    public function scanDocuments(string $groupCode): array
    {
        return $this->jsonRequest('POST', '/api/documents/scan', [
            RequestOptions::QUERY => ['group_code' => $groupCode],
        ], useAdmin: true);
    }

    public function processDocument(string $documentId): array
    {
        return $this->jsonRequest('POST', "/api/documents/process/{$documentId}", [], useAdmin: true);
    }

    public function processAllDocuments(): array
    {
        return $this->jsonRequest('POST', '/api/documents/process-all', [], useAdmin: true);
    }

    public function documentChunks(string $documentId): array
    {
        return $this->jsonRequest('GET', "/api/documents/{$documentId}/chunks", useAdmin: true);
    }

    public function deleteDocument(string $documentId): void
    {
        $this->authorizedRequest('DELETE', "/api/documents/{$documentId}", [], useAdmin: true);
    }

    public function deleteAllDocuments(): void
    {
        $this->authorizedRequest('DELETE', '/api/documents', [], useAdmin: true);
    }

    // ─────────────────────────────────────────────────────────
    //  Internal helpers
    // ─────────────────────────────────────────────────────────

    /**
     * Make an authorized request, auto-retrying once on 401 (token race).
     */
    private function authorizedRequest(string $method, string $uri, array $options = [], bool $isRetry = false, bool $useAdmin = false)
    {
        $token = $useAdmin ? $this->adminToken() : $this->token();
        $options['headers']['Authorization'] = 'Bearer '.$token;

        try {
            return $this->http->request($method, $uri, $options);
        } catch (ClientException $e) {
            if ($e->getCode() === 401 && ! $isRetry) {
                Cache::forget($useAdmin
                    ? config('chatbot.upstream_admin_token_cache_key')
                    : config('chatbot.upstream_token_cache_key'));

                return $this->authorizedRequest($method, $uri, $options, isRetry: true, useAdmin: $useAdmin);
            }
            throw $e;
        }
    }

    /**
     * JSON-decoded request — wraps authorizedRequest + body parsing + error translation.
     */
    private function jsonRequest(string $method, string $uri, array $options = [], bool $useAdmin = false): array
    {
        try {
            $response = $this->authorizedRequest($method, $uri, $options, useAdmin: $useAdmin);
            $body = (string) $response->getBody();

            return $body === '' ? [] : (json_decode($body, true) ?? []);
        } catch (ClientException $e) {
            $detail = $this->extractErrorDetail($e);
            throw new UpstreamUnavailableException($detail, $e->getCode(), $e);
        } catch (\Throwable $e) {
            throw new UpstreamUnavailableException('Tourism API request failed: '.$e->getMessage(), 0, $e);
        }
    }

    private function extractErrorDetail(ClientException $e): string
    {
        $body = (string) $e->getResponse()?->getBody();
        $data = json_decode($body, true);

        return $data['detail'] ?? $e->getMessage();
    }

    /**
     * Common token fetch+cache logic for both user and admin roles.
     */
    private function getOrRefreshToken(string $cacheKey, string $username, string $password, string $role): string
    {
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            $response = $this->http->post('/api/auth/login', [
                RequestOptions::JSON => [
                    'username' => $username,
                    'password' => $password,
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);
            $token = $data['access_token'] ?? $data['token']
                ?? throw new UpstreamUnavailableException("No token in {$role} login response");
            $exp = $data['expires_in'] ?? 28800; // doc says 8h default

            $ttl = max(60, $exp - config('chatbot.upstream_token_refresh_buffer', 1800));
            Cache::put($cacheKey, $token, $ttl);

            return $token;
        } catch (UpstreamUnavailableException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new UpstreamUnavailableException("Upstream {$role} login failed: ".$e->getMessage(), 0, $e);
        }
    }

    /** Reads setting from Core Settings DB first, falls back to config. */
    private function resolvedSetting(string $key): mixed
    {
        try {
            return \Packages\Core\Src\Models\Setting::get("chatbot.{$key}") ?? config("chatbot.{$key}");
        } catch (\Throwable) {
            return config("chatbot.{$key}");
        }
    }
}
