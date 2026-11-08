<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\PlotController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\RekapController;

Route::get('/', function () {
    if (Auth::guard('petugas')->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});


Route::middleware('guest:petugas')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

//Tempat Route Menu
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('auth:petugas')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/petugas', [PetugasController::class, 'index'])->name('petugas.index');
    Route::get('/plot', [PlotController::class, 'index'])->name('plot.index');
    Route::post('/plot/schedule', [PlotController::class, 'autoSchedule'])->name('plot.schedule');
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');
    Route::get('/laporan/{id}', [LaporanController::class, 'detail'])->name('laporan.detail');
    Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
});
