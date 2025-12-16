<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index']);
Route::post('/manual', [DashboardController::class, 'manual']);
Route::get('/manual/latest', function () {
    return \App\Models\ManualControl::latest()->first();
});

