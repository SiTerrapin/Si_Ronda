<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\PlotController;
use App\Http\Controllers\PortalSettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RekapController;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. AREA PUBLIK / WARGA (Tanpa Login)
// ==========================================
Route::get('/', [PublicController::class, 'beranda'])->name('beranda');
Route::get('/jadwal', [PublicController::class, 'jadwal'])->name('public.jadwal');
Route::get('/anggota', [PublicController::class, 'anggota'])->name('public.anggota');
Route::get('/kegiatan', [PublicController::class, 'kegiatan'])->name('public.kegiatan');

// ==========================================
// 2. AUTENTIKASI (Guest)
// ==========================================
Route::middleware('guest:petugas')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ==========================================
// 3. AREA TERPROTEKSI (Petugas, Admin, Super Admin)
// ==========================================
Route::middleware('auth:petugas')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil & Edit Akun Mandiri
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Petugas & Plot (baca + presensi pribadi)
    Route::get('/petugas', [PetugasController::class, 'index'])->name('petugas.index');
    Route::get('/plot', [PlotController::class, 'index'])->name('plot.index');
    Route::patch('/plot/{id}/presensi', [PlotController::class, 'presensi'])->whereNumber('id')->name('plot.presensi');

    // Laporan Patroli
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');
    Route::get('/laporan/{id}', [LaporanController::class, 'detail'])->whereNumber('id')->name('laporan.detail');
    Route::delete('/laporan/{id}', [LaporanController::class, 'destroy'])->whereNumber('id')->name('laporan.destroy');

    // ==========================================
    // 4. KHUSUS ADMIN & SUPER ADMIN (role:admin)
    // ==========================================
    Route::middleware('role:admin')->group(function () {
        // CRUD Petugas
        Route::get('/petugas/create', [PetugasController::class, 'create'])->name('petugas.create');
        Route::post('/petugas', [PetugasController::class, 'store'])->name('petugas.store');
        Route::get('/petugas/{id}/edit', [PetugasController::class, 'edit'])->whereNumber('id')->name('petugas.edit');
        Route::put('/petugas/{id}', [PetugasController::class, 'update'])->whereNumber('id')->name('petugas.update');
        Route::delete('/petugas/{id}', [PetugasController::class, 'destroy'])->whereNumber('id')->name('petugas.destroy');

        // CRUD Plotting, Auto-Schedule & Auto-Rotate Config
        Route::get('/plot/create', [PlotController::class, 'create'])->name('plot.create');
        Route::post('/plot', [PlotController::class, 'store'])->name('plot.store');
        Route::get('/plot/{id}/edit', [PlotController::class, 'edit'])->whereNumber('id')->name('plot.edit');
        Route::put('/plot/{id}', [PlotController::class, 'update'])->whereNumber('id')->name('plot.update');
        Route::delete('/plot/{id}', [PlotController::class, 'destroy'])->whereNumber('id')->name('plot.destroy');
        Route::post('/plot/schedule', [PlotController::class, 'autoSchedule'])->name('plot.schedule');
        Route::post('/plot/auto-config', [PlotController::class, 'updateAutoConfig'])->name('plot.autoConfig');

        // Verifikasi Laporan
        Route::patch('/laporan/{id}/verify', [LaporanController::class, 'verify'])->whereNumber('id')->name('laporan.verify');

        // Rekap Ronda Harian
        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::post('/rekap/generate', [RekapController::class, 'generate'])->name('rekap.generate');
    });

    // ==========================================
    // 5. KHUSUS SUPER ADMIN (role:super_admin)
    // ==========================================
    Route::middleware('role:super_admin')->group(function () {
        Route::patch('/petugas/{id}/role', [PetugasController::class, 'updateRole'])->whereNumber('id')->name('petugas.updateRole');
        Route::get('/portal-settings', [PortalSettingController::class, 'index'])->name('portal.settings');
        Route::post('/portal-settings', [PortalSettingController::class, 'update'])->name('portal.settings.update');
        Route::delete('/portal-settings/reset', [PortalSettingController::class, 'reset'])->name('portal.settings.reset');
    });
});
