<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Admin\KnowledgeBaseController;
use App\Http\Controllers\Admin\SupportTicketController;

Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return view('chat.index');
    })->name('home');

    Route::post('/chat', [ChatController::class, 'chat'])->name('chat');

    Route::get('/chat-history', [ChatController::class, 'history'])->name('chat.history');

    Route::delete('/clear-history', [ChatController::class, 'clearHistory'])->name('chat.clear');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('knowledge-base', KnowledgeBaseController::class);

        Route::resource('support-tickets', SupportTicketController::class)
            ->only(['index', 'show', 'update', 'destroy']);
    });

});

require __DIR__.'/auth.php';
