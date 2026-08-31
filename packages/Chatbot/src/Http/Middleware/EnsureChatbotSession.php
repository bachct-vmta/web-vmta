<?php

namespace Packages\Chatbot\Src\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Packages\Chatbot\Src\Services\ChatbotSessionService;
use Symfony\Component\HttpFoundation\Response;

class EnsureChatbotSession
{
    public function __construct(private ChatbotSessionService $service) {}

    public function handle(Request $request, Closure $next): Response
    {
        $session = $this->service->loadByCookie($request);

        if (! $session) {
            return response()->json(['error' => 'No active session. Call GET /chatbot/session first.'], 400);
        }

        // Attach to request so downstream controller doesn't re-query
        $request->attributes->set('chatbot_session', $session);

        return $next($request);
    }
}
