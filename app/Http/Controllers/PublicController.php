<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Petugas;
use App\Models\Plot;
use App\Services\AutoScheduleService;
use App\Services\PortalSettings;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicController extends Controller
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

    public function beranda()
    {
        AutoScheduleService::checkAndRunIfDue();

        $hariIni = $this->hariIniIndonesia();
        $portalSettings = PortalSettings::get();

        $jadwalMalamIni = Plot::with('petugas')
            ->where('is_active', '1')
            ->where('nama_hari', $hariIni)
            ->orderByDesc('is_leader')
            ->get();

        $totalPetugasAktif = Petugas::where('is_active', '1')->count();
        $laporanMingguIni = Laporan::where('status_verifikasi', 'Diverifikasi')
            ->where('tanggal_lapor', '>=', Carbon::now('Asia/Jakarta')->startOfWeek())
            ->count();
        $kegiatanTerbaru = Laporan::with('petugas')
            ->where('status_verifikasi', 'Diverifikasi')
            ->orderByDesc('tanggal_lapor')
            ->take(3)
            ->get();

        return view('beranda', compact(
            'hariIni',
            'jadwalMalamIni',
            'totalPetugasAktif',
            'laporanMingguIni',
            'kegiatanTerbaru',
            'portalSettings'
        ));
    }

    public function jadwal(Request $request)
    {
        AutoScheduleService::checkAndRunIfDue();

        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $filterHari = $request->query('hari');
        $search = trim((string) $request->query('q', ''));

        $query = Plot::with('petugas')
            ->where('is_active', '1');

        if ($filterHari && in_array($filterHari, $hariList, true)) {
            $query->where('nama_hari', $filterHari);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('pos_jaga', 'like', "%{$search}%")
                  ->orWhereHas('petugas', function ($sub) use ($search) {
                      $sub->where('nama_lengkap', 'like', "%{$search}%")
                          ->orWhere('alamat', 'like', "%{$search}%");
                  });
            });
        }

        $jadwal = $query
            ->orderByRaw("CASE nama_hari
                WHEN 'Senin' THEN 1
                WHEN 'Selasa' THEN 2
                WHEN 'Rabu' THEN 3
                WHEN 'Kamis' THEN 4
                WHEN 'Jumat' THEN 5
                WHEN 'Sabtu' THEN 6
                WHEN 'Minggu' THEN 7
                ELSE 8 END")
            ->orderByDesc('is_leader')
            ->get();

        $hariIni = $this->hariIniIndonesia();

        return view('jadwal', compact('jadwal', 'hariList', 'filterHari', 'search', 'hariIni'));
    }

    public function anggota(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $query = Petugas::where('is_active', '1')
            ->where('role', 'petugas');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $anggota = $query->orderBy('nama_lengkap')->get();

        return view('anggota', compact('anggota', 'search'));
    }

    public function kegiatan()
    {
        $kegiatan = Laporan::with('petugas')
            ->where('status_verifikasi', 'Diverifikasi')
            ->orderByDesc('tanggal_lapor')
            ->get();

        $portalSettings = PortalSettings::get();

        return view('kegiatan', compact('kegiatan', 'portalSettings'));
    }
}
