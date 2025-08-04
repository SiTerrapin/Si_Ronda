<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('beranda');
})->name('beranda');

Route::get('/jadwal', function () {
    return view('jadwal');
})->name('jadwal');

Route::get('/anggota', function () {
    return view('anggota');
})->name('anggota');

Route::get('/beranda', function () {
    return view('beranda');
})->name('beranda');

Route::get('/kegiatan', function () {
    return view('kegiatan');
})->name('kegiatan');