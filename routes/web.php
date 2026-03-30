<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\PaguController;
use App\Http\Controllers\ProfilePasswordController;
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
    Route::get('/password', [ProfilePasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password', [ProfilePasswordController::class, 'update'])->name('password.update');
    Route::get('sasaran', [SasaranController::class, 'index'])->name('sasaran.index');

    Route::middleware(['admin'])->group(function () {
        Route::resource('sub-bagian', SubBagianController::class)->except(['create', 'show', 'edit']);
        Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
        Route::resource('sasaran', SasaranController::class)->except(['create', 'edit', 'show', 'index']);
        Route::patch('sasaran/{sasaran}/toggle-status', [SasaranController::class, 'toggleStatus'])->name('sasaran.toggleStatus');
        Route::post('pagu', [PaguController::class, 'store'])->name('pagu.store');
        Route::put('pagu/{pagu}', [PaguController::class, 'update'])->name('pagu.update');
        Route::delete('pagu/{pagu}', [PaguController::class, 'destroy'])->name('pagu.destroy');
        Route::delete('kegiatans/{kegiatan}', [KegiatanController::class, 'destroy'])->name('kegiatans.destroy');
        Route::post('pagu/import-rkks/preview', [PaguController::class, 'previewRkksImport'])->name('pagu.import.preview');
        Route::post('pagu/import-rkks/apply', [PaguController::class, 'applyRkksImport'])->name('pagu.import.apply');
        Route::delete('pagu/import-rkks/preview', [PaguController::class, 'clearRkksImport'])->name('pagu.import.clear');
    });

    // Module Kegiatan
    Route::resource('pagu', PaguController::class)->except(['store', 'update', 'destroy']);
    Route::resource('kegiatans', KegiatanController::class)->except(['destroy']);
    Route::get('api/sasaran/{id}/indikators', [KegiatanController::class, 'getIndikators']);
    Route::get('api/pagu/{id}/details',       [KegiatanController::class, 'getPaguDetails']);

    Route::get('rekap', [RekapController::class, 'index'])->name('rekap.index');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
