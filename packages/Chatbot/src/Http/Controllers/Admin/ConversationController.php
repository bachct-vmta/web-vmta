<?php

namespace Packages\Chatbot\Src\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Packages\Chatbot\Src\Exceptions\UpstreamUnavailableException;
use Packages\Chatbot\Src\Services\TourismApiClient;

class ConversationController extends Controller
{
    public function __construct(private readonly TourismApiClient $client) {}

    public function index(Request $request): View
    {
        $q = $request->string('q')->trim()->toString() ?: null;
        $skip = max(0, (int) $request->input('skip', 0));
        $limit = min(100, max(1, (int) $request->input('limit', 30)));

        $conversations = [];
        $upstreamError = null;

        try {
            $conversations = $q
                ? $this->client->searchConversations($q)
                : $this->client->listConversations($skip, $limit);
        } catch (UpstreamUnavailableException $e) {
            $upstreamError = $e->getMessage();
        }

        return view('chatbot::admin.conversations.index', [
            'conversations' => $conversations,
            'q' => $q,
            'skip' => $skip,
            'limit' => $limit,
            'upstreamError' => $upstreamError,
        ]);
    }

    public function show(string $conversationId): View
    {
        $messages = [];
        $upstreamError = null;

        try {
            $messages = $this->client->getConversation($conversationId);
        } catch (UpstreamUnavailableException $e) {
            $upstreamError = $e->getMessage();
        }

        return view('chatbot::admin.conversations.show', [
            'conversationId' => $conversationId,
            'messages' => $messages,
            'upstreamError' => $upstreamError,
        ]);
    }
}
