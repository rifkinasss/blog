<?php

use App\Http\Controllers\Api\OpenClawArticleController;
use App\Http\Controllers\Api\OpenClawMediaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:openclaw-api')->group(function (): void {
    Route::get('/articles', [OpenClawArticleController::class, 'index'])->middleware('api-token:articles:read');
    Route::post('/articles', [OpenClawArticleController::class, 'store'])->middleware('api-token:articles:create');
    Route::get('/articles/{article}', [OpenClawArticleController::class, 'show'])->middleware('api-token:articles:read');
    Route::patch('/articles/{article}', [OpenClawArticleController::class, 'update'])->middleware('api-token:articles:update|articles:update-own');
    Route::post('/articles/{article}/request-review', [OpenClawArticleController::class, 'requestReview'])->middleware('api-token:review:request');
    Route::post('/articles/{article}/schedule', [OpenClawArticleController::class, 'schedule'])->middleware('api-token:articles:schedule');
    Route::post('/articles/{article}/publish', [OpenClawArticleController::class, 'publish'])->middleware('api-token:articles:publish|articles:publish-own');
    Route::get('/articles/{article}/preview', [OpenClawArticleController::class, 'preview'])->middleware('api-token:articles:preview');
    Route::post('/media', [OpenClawMediaController::class, 'store'])->middleware(['api-token:media:upload', 'throttle:openclaw-media']);
});
