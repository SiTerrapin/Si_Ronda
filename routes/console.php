<?php

use App\Services\AutoScheduleService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ronda:auto-rotate', function () {
    $ran = AutoScheduleService::checkAndRunIfDue();
    $this->info($ran ? 'Auto-rotate jadwal ronda berhasil dijalankan.' : 'Belum memasuki siklus reset atau fitur nonaktif.');
})->purpose('Jalankan rotasi jadwal ronda otomatis mingguan jika sudah melewati hari & jam reset');

Schedule::command('ronda:auto-rotate')->hourly();
