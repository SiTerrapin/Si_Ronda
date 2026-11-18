<?php

namespace App\Http\Controllers;

use App\Models\Plot;
use App\Models\Rekap;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RekapController extends Controller
{
    private function namaHariIndonesia(Carbon $date): string
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

        return $map[$date->dayOfWeek] ?? 'Senin';
    }

    public function index(Request $request)
    {
        $dari = $request->query('dari');
        $sampai = $request->query('sampai');

        $rekapQuery = Rekap::with('petugas')->orderByDesc('tanggal_rekap');

        if ($dari) {
            $rekapQuery->whereDate('tanggal_rekap', '>=', $dari);
        }
        if ($sampai) {
            $rekapQuery->whereDate('tanggal_rekap', '<=', $sampai);
        }

        $rekapHarian = $rekapQuery->get();

        // Ringkasan kontribusi laporan per petugas
        $rekapLaporan = DB::table('laporan_petugas')
            ->join('petugas', 'laporan_petugas.petugas_id', '=', 'petugas.id_petugas')
            ->select(
                'petugas.nama_lengkap',
                DB::raw('COUNT(*) as total_laporan'),
                DB::raw("SUM(CASE WHEN laporan_petugas.status_verifikasi = 'Diverifikasi' THEN 1 ELSE 0 END) as terverifikasi")
            )
            ->groupBy('petugas.id_petugas', 'petugas.nama_lengkap')
            ->orderByDesc('total_laporan')
            ->get();

        return view('rekap.index', compact('rekapHarian', 'rekapLaporan', 'dari', 'sampai'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'tanggal_rekap'   => ['required', 'date'],
            'status_keamanan' => ['required', 'in:Aman,Waspada,Darurat'],
            'isi_rekap'       => ['nullable', 'string'],
        ]);

        $tanggal = Carbon::parse($validated['tanggal_rekap']);
        $namaHari = $this->namaHariIndonesia($tanggal);

        $plots = Plot::where('nama_hari', $namaHari)
            ->where('is_active', '1')
            ->get();

        $terjadwal = $plots->count();
        $hadir = $plots->where('status_kehadiran', 'Hadir')->count();
        $ijin = $plots->where('status_kehadiran', 'Izin')->count();
        $alfa = $plots->where('status_kehadiran', 'Alfa')->count();

        if (!empty($validated['isi_rekap'])) {
            $catatanRekap = $validated['isi_rekap'];
        } elseif ($terjadwal === 0) {
            $catatanRekap = "Tidak ada jadwal ronda aktif pada hari {$namaHari}.";
        } else {
            $catatanRekap = "Rekap ronda malam hari {$namaHari}.";
        }

        $payload = [
            'petugas_id'              => Auth::guard('petugas')->id(),
            'total_petugas_terjadwal' => $terjadwal,
            'total_petugas_hadir'     => $hadir,
            'total_petugas_ijin'      => $ijin,
            'total_petugas_alpha'     => $alfa,
            'status_keamanan'         => $validated['status_keamanan'],
            'isi_rekap'               => $catatanRekap,
        ];

        $existingRekap = Rekap::whereDate('tanggal_rekap', $tanggal->toDateString())->first();
        if ($existingRekap) {
            $existingRekap->update($payload);
        } else {
            Rekap::create(array_merge(['tanggal_rekap' => $tanggal->toDateString()], $payload));
        }

        return redirect()->route('rekap.index')->with('success', 'Rekap ronda harian untuk tanggal ' . $tanggal->format('d M Y') . ' berhasil disimpan.');
    }
}
