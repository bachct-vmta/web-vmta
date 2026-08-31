<?php

use Illuminate\Support\Facades\Route;
use Packages\Chatbot\Src\Http\Controllers\Admin\ChatbotSettingsController;
use Packages\Chatbot\Src\Http\Controllers\Admin\ConversationController;
use Packages\Chatbot\Src\Http\Controllers\Admin\DocumentController;
use Packages\Chatbot\Src\Http\Controllers\Admin\DocumentGroupController;

Route::prefix('chatbot')->name('chatbot.')->group(function () {
    Route::get('/settings', [ChatbotSettingsController::class, 'index'])
        ->middleware('permission:chatbot.settings')
        ->name('settings.index');

    Route::put('/settings', [ChatbotSettingsController::class, 'update'])
        ->middleware('permission:chatbot.settings')
        ->name('settings.update');

    Route::middleware('permission:chatbot.document_groups')->group(function () {
        Route::get('/document-groups', [DocumentGroupController::class, 'index'])
            ->name('document-groups.index');
        Route::post('/document-groups', [DocumentGroupController::class, 'store'])
            ->name('document-groups.store');
        Route::delete('/document-groups/{code}', [DocumentGroupController::class, 'destroy'])
            ->where('code', '[A-Z0-9_-]+')
            ->name('document-groups.destroy');
    });

    Route::middleware('permission:chatbot.documents')->group(function () {
        Route::get('/documents', [DocumentController::class, 'index'])
            ->name('documents.index');
        Route::post('/documents', [DocumentController::class, 'store'])
            ->name('documents.store');
        Route::post('/documents/scan', [DocumentController::class, 'scan'])
            ->name('documents.scan');
        Route::post('/documents/process-all', [DocumentController::class, 'processAll'])
            ->name('documents.process-all');
        Route::post('/documents/{document}/process', [DocumentController::class, 'process'])
            ->name('documents.process');
        Route::get('/documents/{document}/chunks', [DocumentController::class, 'chunks'])
            ->name('documents.chunks');
        Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])
            ->name('documents.destroy');
    });

    Route::middleware('permission:chatbot.conversations')->group(function () {
        Route::get('/conversations', [ConversationController::class, 'index'])
            ->name('conversations.index');
        Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])
            ->name('conversations.show');
    });
});
