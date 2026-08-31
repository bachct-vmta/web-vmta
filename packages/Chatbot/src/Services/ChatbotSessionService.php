<?php

namespace Packages\Chatbot\Src\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redis;
use Packages\Chatbot\Src\Exceptions\SessionLimitReachedException;
use Packages\Chatbot\Src\Models\ChatbotSession;
use Packages\Chatbot\Src\Repositories\ChatbotSessionRepository;
use Packages\Report\Src\Facades\Report;

class ChatbotSessionService
{
    private const COOKIE_NAME = 'cbs_id';

    public function __construct(
        private ChatbotSessionRepository $repo
    ) {}

    /**
     * Create a new session, set signed httpOnly cookie on response.
     */
    public function startSession(Request $request): ChatbotSession
    {
        $maxMessages = (int) $this->resolvedSetting('max_messages_per_session');
        $ttl = (int) $this->resolvedSetting('session_ttl');

        $session = $this->repo->create([
            'message_count' => 0,
            'max_messages' => $maxMessages,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'locale' => app()->getLocale(),
            'started_at' => now(),
            'last_activity_at' => now(),
            'expires_at' => now()->addSeconds($ttl),
        ]);

        Cookie::queue(
            Cookie::make(
                self::COOKIE_NAME,
                $session->id,
                $ttl / 60, // minutes
                '/',
                null,
                config('app.env') === 'production',
                true,  // httpOnly
                false,
                'Strict'
            )
        );

        Report::increment('chatbot.sessions_started');

        return $session;
    }

    /**
     * Load session from signed cookie; returns null if missing/expired/invalid.
     */
    public function loadByCookie(Request $request): ?ChatbotSession
    {
        $id = $request->cookie(self::COOKIE_NAME);

        if (! $id) {
            return null;
        }

        $session = $this->repo->find($id);

        if (! $session || $session->isExpired()) {
            return null;
        }

        return $session;
    }

    /**
     * Atomically increment the message counter.
     * Returns new count. Throws SessionLimitReachedException if already at limit.
     */
    public function incrementMessageCount(ChatbotSession $session): int
    {
        if ($session->hasReachedLimit()) {
            throw new SessionLimitReachedException();
        }

        $newCount = $this->atomicIncrement($session->id);

        if ($newCount > $session->max_messages) {
            // Redis INCR pushed past limit — treat as overflow (concurrent race)
            $this->markOverflow($session);
            throw new SessionLimitReachedException();
        }

        // Keep DB in sync (best-effort, not authoritative for limit check)
        $session->message_count = $newCount;
        $session->last_activity_at = now();
        $this->repo->save($session);

        Report::increment('chatbot.messages_sent');

        return $newCount;
    }

    public function markOverflow(ChatbotSession $session): void
    {
        $session->handoff_reason = 'overflow';
        $session->last_activity_at = now();
        $this->repo->save($session);

        Report::increment('chatbot.overflow_handoff');
    }

    public function ensureConversationId(ChatbotSession $session, TourismApiClient $client): string
    {
        if ($session->upstream_conversation_id) {
            return $session->upstream_conversation_id;
        }

        $conversationId = $client->createConversation();
        $session->upstream_conversation_id = $conversationId;
        $this->repo->save($session);

        return $conversationId;
    }

    /**
     * Redis INCR for O(1) atomic counter; falls back to DB lockForUpdate.
     */
    private function atomicIncrement(string $sessionId): int
    {
        $key = config('chatbot.counter_key_prefix').$sessionId;

        try {
            $count = Redis::incr($key);

            // Align TTL with session expiry (set on first increment)
            if ($count === 1) {
                $ttl = (int) $this->resolvedSetting('session_ttl');
                Redis::expire($key, $ttl);
            }

            return $count;
        } catch (\Throwable) {
            // Redis unavailable — fall back to DB pessimistic lock
            return $this->repo->incrementCounterWithLock($sessionId);
        }
    }

    private function resolvedSetting(string $key): mixed
    {
        try {
            return \Packages\Core\Src\Models\Setting::get("chatbot.{$key}") ?? config("chatbot.{$key}");
        } catch (\Throwable) {
            return config("chatbot.{$key}");
        }
    }
}
