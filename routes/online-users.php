<?php

use App\Http\Controllers\OnlineUsersController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard/online-users', [OnlineUsersController::class, 'index'])
        ->name('online-users.index');

    Route::get('/api/online-users/stats', [OnlineUsersController::class, 'api'])
        ->name('online-users.api');
});
