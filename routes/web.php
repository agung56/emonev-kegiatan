<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\PaguController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\SasaranController;
use App\Http\Controllers\SubBagianController;
use App\Http\Controllers\UserController;

// Halaman Login
Route::get('/', function () {
    return view('auth.login');
})->name('login');

// Proses Login & Logout
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    // Satu pintu dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('sub-bagian', SubBagianController::class)->except(['create', 'show', 'edit']);
    Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
    Route::resource('pagu', PaguController::class);
    Route::resource('sasaran', SasaranController::class)->except(['create', 'edit', 'show']);
    Route::patch('sasaran/{sasaran}/toggle-status', [SasaranController::class, 'toggleStatus'])->name('sasaran.toggleStatus');

    // Module Kegiatan
    Route::resource('kegiatans', KegiatanController::class);
    Route::get('api/sasaran/{id}/indikators', [KegiatanController::class, 'getIndikators']);
    Route::get('api/pagu/{id}/details',       [KegiatanController::class, 'getPaguDetails']);

    Route::get('rekap', [RekapController::class, 'index'])->name('rekap.index');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});