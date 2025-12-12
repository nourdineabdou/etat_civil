<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NniController;
use App\Http\Controllers\AuthController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::middleware('auth')->group(function () {
    Route::get('/', [NniController::class, 'showForm'])->name('dashboard');
    Route::post('/nni/lookup', [NniController::class, 'lookup']);
    Route::get('/nni/stats', [NniController::class, 'stats']);
});

// page  login

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// logout (invalidate session)
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
