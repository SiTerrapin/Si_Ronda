<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Petugas;
use App\Models\Laporan;
use App\Models\Plot;
use Carbon\Carbon;

class DashboardController
{
    public function index()
    {
        $petugas = Auth::guard('petugas')->user();

        $laporanHariIni = Laporan::whereDate('tanggal_lapor', Carbon::today())
            ->where('petugas_id', $petugas->id_petugas)
            ->count();

        $jadwalHariIni = Plot::where('nama_hari', Carbon::now()->locale('id')->dayName)
            ->where('petugas_id', $petugas->id_petugas)
            ->count();

        $jumlahPetugas = Petugas::where('is_active', '1')->count();
        $jumlahJadwal = Plot::count();
        $jumlahLaporan = Laporan::count();

        $jam = Carbon::now()->format('H');
        if ($jam < 11) {
            $sapaan = 'Selamat pagi';
        } elseif ($jam < 15) {
            $sapaan = 'Selamat siang';
        } elseif ($jam < 18) {
            $sapaan = 'Selamat sore';
        } else {
            $sapaan = 'Selamat malam';
        }

        return view('dashboard', compact(
            'petugas',
            'laporanHariIni',
            'jadwalHariIni',
            'jumlahPetugas',
            'jumlahJadwal',
            'jumlahLaporan',
            'sapaan'
        ));
    }

}
