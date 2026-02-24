<?php

use App\Http\Controllers\TrackerAuthController;
use App\Http\Controllers\TrackerScreenshotController;
use App\Http\Controllers\TrackerSegmentController;
use App\Http\Controllers\TrackerSessionController;
use Illuminate\Support\Facades\Route;

Route::prefix('tracker')->group(function () {
    Route::post('/auth/device-code', [TrackerAuthController::class, 'deviceCode']);
    Route::post('/auth/token', [TrackerAuthController::class, 'issueToken']);
    Route::post('/auth/refresh', [TrackerAuthController::class, 'refreshToken']);

    Route::middleware('desktop_api')->group(function () {
        Route::post('/auth/revoke', [TrackerAuthController::class, 'revoke']);

        Route::post('/sessions', [TrackerSessionController::class, 'store']);
        Route::patch('/sessions/{session}/stop', [TrackerSessionController::class, 'stop']);
        Route::post('/segments', [TrackerSegmentController::class, 'store']);
        Route::post('/screenshots', [TrackerScreenshotController::class, 'store']);
    });
});
