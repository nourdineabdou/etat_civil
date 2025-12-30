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
    Route::get('/nni/charts-data', [NniController::class, 'chartsData']);

    // Import routes (dangerous: keep behind auth)
    // Route::get('/import/rhtcongeagent', [App\Http\Controllers\ImportController::class, 'importRhtCongeAgent'])->name('import.rhtcongeagent');
    // Route::post('/import/rhtcongeagent', [App\Http\Controllers\ImportController::class, 'importRhtCongeAgent']);
});

// page  login

// route import
Route::get('/import/rhtcongeagent', [App\Http\Controllers\NniController::class, 'importRhtCongeAgent'])->name('import.rhtcongeagent');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// logout (invalidate session)
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
