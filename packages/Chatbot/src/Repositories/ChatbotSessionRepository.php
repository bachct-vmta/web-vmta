<?php

namespace Packages\Chatbot\Src\Repositories;

use Packages\Chatbot\Src\Models\ChatbotSession;

class ChatbotSessionRepository
{
    public function find(string $id): ?ChatbotSession
    {
        return ChatbotSession::find($id);
    }

    public function create(array $attributes): ChatbotSession
    {
        return ChatbotSession::create($attributes);
    }

    public function save(ChatbotSession $session): bool
    {
        return $session->save();
    }

    /**
     * Atomically increment message_count via DB lock, returns new count.
     * Used as fallback when Redis is unavailable.
     */
    public function incrementCounterWithLock(string $id): int
    {
        return \DB::transaction(function () use ($id) {
            $session = ChatbotSession::lockForUpdate()->findOrFail($id);
            $session->increment('message_count');
            // increment() issues raw SQL without refreshing the in-memory model
            $session->refresh();

            return $session->message_count;
        });
    }
}
