<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// ڕێڕەوی چوونەژوورەوە و دەرچوون
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// پاراستنی شاشەی فرۆشتنی کاشێر
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('pos.index');
    });

    Route::get('/pos', function () {
        return view('pos.index');
    })->name('pos.index');
});
