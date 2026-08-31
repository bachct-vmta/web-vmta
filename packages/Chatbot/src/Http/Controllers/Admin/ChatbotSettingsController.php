<?php

namespace Packages\Chatbot\Src\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Packages\Core\Src\Http\Controllers\BaseController;
use Packages\Core\Src\Models\Setting;

class ChatbotSettingsController extends BaseController
{
    public function index(): View
    {
        $suggestedRaw = Setting::get('chatbot.suggested_questions');
        $suggestedQuestions = is_string($suggestedRaw) ? json_decode($suggestedRaw, true) : $suggestedRaw;
        if (! is_array($suggestedQuestions)) {
            $suggestedQuestions = [];
        }

        $settings = [
            'document_group' => Setting::get('chatbot.document_group', config('chatbot.document_group')),
            'ai_provider' => Setting::get('chatbot.ai_provider', config('chatbot.ai_provider')),
            'max_messages_per_session' => (int) Setting::get('chatbot.max_messages_per_session', config('chatbot.max_messages_per_session', 10)),
            'session_ttl' => (int) Setting::get('chatbot.session_ttl', config('chatbot.session_ttl', 86400)),
            'suggested_questions' => $suggestedQuestions,
        ];

        return view('chatbot::admin.settings', compact('settings'));
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document_group' => 'nullable|string|max:50',
            'ai_provider' => 'nullable|string|max:50',
            'max_messages_per_session' => 'required|integer|min:1|max:100',
            'session_ttl' => 'required|integer|min:3600|max:604800',
            'suggested_questions' => 'nullable|array|max:4',
            'suggested_questions.*' => 'nullable|string|max:200',
        ]);

        $docGroup = $validated['document_group'] ? strtoupper(trim($validated['document_group'])) : null;

        // Filter empty strings, keep only non-blank questions (max 4).
        $questions = collect($validated['suggested_questions'] ?? [])
            ->map(fn ($q) => trim($q ?? ''))
            ->filter(fn ($q) => $q !== '')
            ->values()
            ->take(4)
            ->all();

        Setting::set('chatbot.document_group', $docGroup, 'chatbot');
        Setting::set('chatbot.ai_provider', $validated['ai_provider'] ?: null, 'chatbot');
        Setting::set('chatbot.max_messages_per_session', (string) $validated['max_messages_per_session'], 'chatbot');
        Setting::set('chatbot.session_ttl', (string) $validated['session_ttl'], 'chatbot');
        Setting::set('chatbot.suggested_questions', json_encode($questions, JSON_UNESCAPED_UNICODE), 'chatbot', 'json');

        return $this->success(null, trans('chatbot::chatbot.settings_saved'));
    }
}
