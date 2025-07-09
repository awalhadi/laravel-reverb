<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// chat routes
Route::prefix('chat')->group(function () {
    // initiate chat
    Route::post('/initialize', [ChatController::class, 'initiate']);
    // get messages
    Route::get('/messages', [ChatController::class, 'messages']);
    // send message
    Route::post('/send', [ChatController::class, 'send']);
});
