<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WishController;
use App\Http\Controllers\GeminiController;

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/wishes/all', [WishController::class, 'allWishes'])
        ->middleware('can:view_all_wishes');
    Route::get('/wishes/{wish}', [WishController::class, 'show'])
        ->middleware('can:view_all_wishes');

    Route::put('/wishes/{wish}', [WishController::class, 'update'])
        ->middleware('can:update_wish');
    Route::delete('/wishes/{wish}', [WishController::class, 'destroy'])
        ->middleware('can:delete_wish');
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::get('/gemini/models', [GeminiController::class, 'listModels']); // Run this first!
Route::get('/gemini/generate', [GeminiController::class, 'generate']);
Route::get('/gemini/chat', [GeminiController::class, 'chat']);
Route::get('/gemini/image', [GeminiController::class, 'describeImage']);

Route::post('/wishes', [WishController::class, 'store'])
    ->middleware('throttle:10,1');
