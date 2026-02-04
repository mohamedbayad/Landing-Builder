<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/events', [App\Http\Controllers\Api\TrackingController::class, 'track']);
Route::post('/leads', [App\Http\Controllers\Api\TrackingController::class, 'captureLead']);
