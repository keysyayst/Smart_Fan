<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index']);
Route::post('/manual', [DashboardController::class, 'manual']);
Route::post('/auto', [DashboardController::class, 'auto']);
Route::get('/manual/latest', function () {
    $data = \App\Models\ManualControl::orderBy('id', 'desc')->first();
    if ($data) {
        return response()->json([
            'mode' => $data->mode ?? 'AUTO',
            'fan' => $data->fan ?? null
        ]);
    }
    return response()->json(['mode' => 'AUTO', 'fan' => null]);
});

