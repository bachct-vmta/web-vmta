<?php

use Illuminate\Support\Facades\Route;
use Packages\Chatbot\Src\Http\Controllers\ChatbotController;

/*
| Mounted by ChatbotServiceProvider outside locale groups (no i18n needed for API endpoints).
| /chatbot/session  — rate-limited by IP (honeypot + throttle)
| /chatbot/message  — rate-limited by session cookie + requires EnsureChatbotSession
*/

Route::prefix('chatbot')->name('chatbot.')->group(function () {

    Route::get('/session', [ChatbotController::class, 'session'])
        ->middleware(['throttle:'.config('chatbot.rate_limit_session', 10).',1'])
        ->name('session');

    Route::post('/message', [ChatbotController::class, 'message'])
        ->middleware([
            \Packages\Chatbot\Src\Http\Middleware\EnsureChatbotSession::class,
            'throttle:'.config('chatbot.rate_limit_message', 30).',1',
        ])
        ->name('message');

    Route::post('/new-chat', [ChatbotController::class, 'newChat'])
        ->middleware(['throttle:'.config('chatbot.rate_limit_session', 10).',1'])
        ->name('new-chat');
});
