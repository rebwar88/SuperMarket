<?php

use App\Http\Controllers\Api\V1\POS\POSController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/pos')->middleware(['web', 'auth'])->group(function () {
    Route::post('/scan', [POSController::class, 'scan']);
    Route::post('/checkout', [POSController::class, 'checkout']);
    Route::post('/open-shift', [POSController::class, 'openShift']);
    Route::post('/close-shift', [POSController::class, 'closeShift']);
    Route::post('/park-cart', [POSController::class, 'parkCart']);
    Route::get('/resume-cart/{id}', [POSController::class, 'resumeCart']);
    Route::post('/return', [POSController::class, 'processReturn']);
});
