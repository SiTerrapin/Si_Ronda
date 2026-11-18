<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Petugas;
use App\Models\Plot;
use App\Models\Rekap;
use App\Services\AutoScheduleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private function hariIniIndonesia(): string
    {
        $map = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        return $map[Carbon::now('Asia/Jakarta')->dayOfWeek] ?? 'Senin';
    }

    public function index()
    {
        AutoScheduleService::checkAndRunIfDue();

        $petugas = Auth::guard('petugas')->user();
        $now = Carbon::now('Asia/Jakarta');
        $hariIni = $this->hariIniIndonesia();

        $laporanHariIni = Laporan::whereDate('tanggal_lapor', $now->toDateString())
            ->where('petugas_id', $petugas->id_petugas)
            ->count();

        $jadwalSaya = Plot::where('petugas_id', $petugas->id_petugas)
            ->where('is_active', '1')
            ->get();

        $jadwalHariIni = $jadwalSaya->where('nama_hari', $hariIni)->count();

        $jumlahPetugas = Petugas::where('is_active', '1')->count();
        $jumlahJadwalMalamIni = Plot::where('is_active', '1')->where('nama_hari', $hariIni)->count();
        $jumlahLaporanBulanIni = Laporan::whereMonth('tanggal_lapor', $now->month)
            ->whereYear('tanggal_lapor', $now->year)
            ->count();

        // Hitung persentase kehadiran dari rekap harian (atau fallback dari plot_ronda)
        $totalTerjadwal = (int) Rekap::sum('total_petugas_terjadwal');
        $totalHadir = (int) Rekap::sum('total_petugas_hadir');
        if ($totalTerjadwal > 0) {
            $persentaseKehadiran = round(($totalHadir / $totalTerjadwal) * 100);
        } else {
            $plotTotal = Plot::where('is_active', '1')->count();
            $plotHadir = Plot::where('is_active', '1')->where('status_kehadiran', 'Hadir')->count();
            $persentaseKehadiran = $plotTotal > 0 ? round(($plotHadir / $plotTotal) * 100) : 0;
        }

        // Quick View tables
        $jadwalMalamIniList = Plot::with('petugas')
            ->where('is_active', '1')
            ->where('nama_hari', $hariIni)
            ->orderByDesc('is_leader')
            ->get();

        $laporanTerbaru = Laporan::with('petugas')
            ->orderByDesc('tanggal_lapor')
            ->take(5)
            ->get();

        $jam = (int) $now->format('H');
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
            'hariIni',
            'laporanHariIni',
            'jadwalHariIni',
            'jadwalSaya',
            'jumlahPetugas',
            'jumlahJadwalMalamIni',
            'jumlahLaporanBulanIni',
            'persentaseKehadiran',
            'jadwalMalamIniList',
            'laporanTerbaru',
            'sapaan'
        ));
    }
}
