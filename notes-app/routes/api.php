<?php

use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\SummaryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ── General API rate limit: 60 requests/minute per IP ──────────────────
Route::middleware(['throttle:60,1'])->group(function () {

    // Notes CRUD
    Route::apiResource('notes', NoteController::class);

    // AI endpoints with stricter rate limit (Gemini has its own limits)
    Route::middleware(['throttle:30,1'])->group(function () {
        Route::post('notes/{id}/summary', [SummaryController::class, 'generate']);
        Route::get('search', [SearchController::class, 'search']);
    });
});
