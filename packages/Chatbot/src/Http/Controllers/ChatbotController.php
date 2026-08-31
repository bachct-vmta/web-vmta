<?php

namespace Packages\Chatbot\Src\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Packages\Chatbot\Src\Exceptions\SessionLimitReachedException;
use Packages\Chatbot\Src\Exceptions\UpstreamUnavailableException;
use Packages\Chatbot\Src\Services\ChatbotSessionService;
use Packages\Chatbot\Src\Services\ChatbotStreamRelay;
use Packages\Chatbot\Src\Services\TourismApiClient;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatbotController extends Controller
{
    public function __construct(
        private ChatbotSessionService $sessionService,
        private TourismApiClient $apiClient,
        private ChatbotStreamRelay $relay
    ) {}

    /**
     * GET /chatbot/session
     * Creates or reuses an existing session. Returns metadata plus the
     * conversation history (so the widget can rehydrate on page reload).
     */
    public function session(Request $request): JsonResponse
    {
        $session = $this->sessionService->loadByCookie($request);

        if (! $session) {
            $session = $this->sessionService->startSession($request);
        }

        $messages = [];
        if ($session->upstream_conversation_id) {
            try {
                $raw = $this->apiClient->getConversation($session->upstream_conversation_id);
                // Normalise to the widget shape: {role: 'user'|'bot', content: '...'}.
                // Upstream returns role='assistant' for bot replies — map it.
                foreach ($raw as $msg) {
                    $role = ($msg['role'] ?? null) === 'user' ? 'user' : 'bot';
                    $content = (string) ($msg['content'] ?? '');
                    if ($content === '') {
                        continue;
                    }
                    $messages[] = ['role' => $role, 'content' => $content];
                }
            } catch (UpstreamUnavailableException $e) {
                \Log::warning('Chatbot history fetch failed', [
                    'error' => $e->getMessage(),
                    'conversation' => $session->upstream_conversation_id,
                ]);
                // Silent fallback — widget still loads with empty history.
            }
        }

        return response()->json([
            'session_id' => $session->id,
            'remaining' => $session->remaining(),
            'is_overflow' => $session->isOverflow(),
            'messages' => $messages,
        ]);
    }

    /**
     * POST /chatbot/message
     * Proxies content to upstream and relays SSE stream back to browser.
     * Returns 403 JSON when session limit is reached.
     */
    public function message(Request $request): StreamedResponse|JsonResponse
    {
        $request->validate(['content' => 'required|string|max:2000']);

        /** @var \Packages\Chatbot\Src\Models\ChatbotSession $session */
        $session = $request->attributes->get('chatbot_session');

        if ($session->hasReachedLimit() || $session->isOverflow()) {
            return $this->overflowResponse($session->remaining());
        }

        try {
            $this->sessionService->incrementMessageCount($session);
        } catch (SessionLimitReachedException) {
            // markOverflow() already called inside incrementMessageCount() on the race path
            return $this->overflowResponse(0);
        }

        try {
            $conversationId = $this->sessionService->ensureConversationId($session, $this->apiClient);
            $stream = $this->apiClient->streamMessage($conversationId, $request->input('content'));
        } catch (UpstreamUnavailableException $e) {
            \Log::warning('Chatbot upstream unavailable', ['error' => $e->getMessage(), 'session' => $session->id]);

            return $this->relay->error(trans('chatbot::chatbot.upstream_error'));
        }

        return $this->relay->relay($stream, function () {
            // post-stream callback reserved for future metric hooks
        });
    }

    /**
     * POST /chatbot/new-chat
     * Expires current session cookie so the next GET /session creates a fresh one.
     */
    public function newChat(): JsonResponse
    {
        // Forget the httpOnly session cookie — forces a new session on next init().
        \Illuminate\Support\Facades\Cookie::queue(
            \Illuminate\Support\Facades\Cookie::forget('cbs_id')
        );

        return response()->json(['ok' => true]);
    }

    private function overflowResponse(int $remaining): JsonResponse
    {
        return response()->json([
            'overflow' => true,
            'remaining' => $remaining,
            'message' => trans('chatbot::chatbot.limit_reached'),
        ], 403);
    }
}
