<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/events', [App\Http\Controllers\Api\TrackingController::class, 'track']);
Route::post('/leads', [App\Http\Controllers\Api\TrackingController::class, 'captureLead']);

// Session Recording API (new)
Route::prefix('rec')->group(function () {
    Route::post('/session/init', [\App\Http\Controllers\RecordingController::class, 'initSession']);
    Route::post('/events', [\App\Http\Controllers\RecordingController::class, 'storeEvents']);
    Route::post('/session/end', [\App\Http\Controllers\RecordingController::class, 'endSession']);
    Route::post('/convert', [\App\Http\Controllers\RecordingController::class, 'markConverted']);
});

// Old Session Recording API
Route::post('/record-session', [App\Http\Controllers\Api\SessionController::class, 'store']);
Route::patch('/record-session/{sessionId}', [App\Http\Controllers\Api\SessionController::class, 'append']);
