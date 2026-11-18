<?php

namespace App\Http\Controllers;

use App\Models\Petugas;
use App\Models\Plot;
use App\Services\AutoScheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlotController extends Controller
{
    public function index(Request $request)
    {
        // Trigger auto-rotate mingguan jika sudah melewati hari & jam reset siklus
        AutoScheduleService::checkAndRunIfDue();

        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $filterHari = $request->query('hari');

        $query = Plot::with('petugas')->where('is_active', '1');

        if ($filterHari && in_array($filterHari, $hariList, true)) {
            $query->where('nama_hari', $filterHari);
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

        $mapHari = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        $hariIni = $mapHari[Carbon::now('Asia/Jakarta')->dayOfWeek] ?? 'Senin';
        $autoConfig = AutoScheduleService::getConfig();

        return view('plot.index', compact('jadwal', 'hariList', 'filterHari', 'hariIni', 'autoConfig'));
    }

    public function create()
    {
        $petugasList = Petugas::where('is_active', '1')->orderBy('nama_lengkap')->get();
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $posList = ['Pos Utama', 'Pos Utara', 'Pos Selatan', 'Pos Barat', 'Pos Timur'];
        $shiftList = ['Malam (22:00 - 04:00)', 'Sore (18:00 - 22:00)', 'Dini Hari (00:00 - 05:00)'];

        return view('plot.create', compact('petugasList', 'hariList', 'posList', 'shiftList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'petugas_id'    => ['required', 'exists:petugas,id_petugas'],
            'nama_hari'     => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'tanggal_ronda' => ['nullable', 'date'],
            'pos_jaga'      => ['required', 'string', 'max:50'],
            'shift'         => ['required', 'string', 'max:30'],
            'is_leader'     => ['required', 'in:0,1'],
        ]);

        // Validasi Anti-Bentrok
        $bentrok = Plot::where('petugas_id', $validated['petugas_id'])
            ->where('nama_hari', $validated['nama_hari'])
            ->where('shift', $validated['shift'])
            ->where('is_active', '1')
            ->exists();

        if ($bentrok) {
            return back()
                ->withInput()
                ->withErrors(['petugas_id' => 'Petugas tersebut sudah dijadwalkan pada hari dan shift yang sama (bentrok).']);
        }

        Plot::create([
            'petugas_id'       => $validated['petugas_id'],
            'nama_hari'        => $validated['nama_hari'],
            'tanggal_ronda'    => $validated['tanggal_ronda'] ?? null,
            'pos_jaga'         => $validated['pos_jaga'],
            'shift'            => $validated['shift'],
            'is_leader'        => $validated['is_leader'],
            'is_active'        => '1',
            'status_kehadiran' => 'Terjadwal',
        ]);

        return redirect()->route('plot.index')->with('success', 'Jadwal ronda berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $plot = Plot::findOrFail($id);
        $petugasList = Petugas::where('is_active', '1')->orderBy('nama_lengkap')->get();
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $posList = ['Pos Utama', 'Pos Utara', 'Pos Selatan', 'Pos Barat', 'Pos Timur'];
        $shiftList = ['Malam (22:00 - 04:00)', 'Sore (18:00 - 22:00)', 'Dini Hari (00:00 - 05:00)'];

        return view('plot.edit', compact('plot', 'petugasList', 'hariList', 'posList', 'shiftList'));
    }

    public function update(Request $request, $id)
    {
        $plot = Plot::findOrFail($id);

        $validated = $request->validate([
            'petugas_id'       => ['required', 'exists:petugas,id_petugas'],
            'nama_hari'        => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'tanggal_ronda'    => ['nullable', 'date'],
            'pos_jaga'         => ['required', 'string', 'max:50'],
            'shift'            => ['required', 'string', 'max:30'],
            'is_leader'        => ['required', 'in:0,1'],
            'status_kehadiran' => ['required', 'in:Terjadwal,Hadir,Izin,Alfa'],
        ]);

        $bentrok = Plot::where('petugas_id', $validated['petugas_id'])
            ->where('nama_hari', $validated['nama_hari'])
            ->where('shift', $validated['shift'])
            ->where('is_active', '1')
            ->where('id_plot', '!=', $plot->id_plot)
            ->exists();

        if ($bentrok) {
            return back()
                ->withInput()
                ->withErrors(['petugas_id' => 'Petugas tersebut sudah dijadwalkan pada hari dan shift yang sama (bentrok).']);
        }

        $plot->update([
            'petugas_id'       => $validated['petugas_id'],
            'nama_hari'        => $validated['nama_hari'],
            'tanggal_ronda'    => $validated['tanggal_ronda'] ?? null,
            'pos_jaga'         => $validated['pos_jaga'],
            'shift'            => $validated['shift'],
            'is_leader'        => $validated['is_leader'],
            'status_kehadiran' => $validated['status_kehadiran'],
        ]);

        return redirect()->route('plot.index')->with('success', 'Jadwal ronda berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $plot = Plot::findOrFail($id);
        $plot->delete();

        return redirect()->route('plot.index')->with('success', 'Jadwal ronda berhasil dihapus.');
    }

    public function autoSchedule()
    {
        try {
            $created = AutoScheduleService::rotateNow();
            if ($created === 0) {
                return back()->with('error', 'Belum ada petugas aktif untuk penjadwalan.');
            }

            return redirect()->route('plot.index')->with('success', 'Penjadwalan otomatis berhasil disusun secara proporsional.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menjadwalkan: ' . $e->getMessage());
        }
    }

    public function updateAutoConfig(Request $request)
    {
        $validated = $request->validate([
            'auto_rotate_enabled' => ['nullable', 'boolean'],
            'reset_after_day'     => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
        ]);

        AutoScheduleService::saveConfig([
            'auto_rotate_enabled' => $request->boolean('auto_rotate_enabled'),
            'reset_after_day'     => $validated['reset_after_day'],
        ]);

        return back()->with('success', 'Pengaturan Auto-Rotate Mingguan berhasil diperbarui.');
    }

    public function presensi(Request $request, $id)
    {
        $plot = Plot::findOrFail($id);
        $user = Auth::guard('petugas')->user();

        if (!$user->isAdmin() && $plot->petugas_id !== $user->id_petugas) {
            abort(403, 'Anda hanya dapat mengisi presensi untuk jadwal Anda sendiri.');
        }

        $allowed = $user->isAdmin()
            ? ['Terjadwal', 'Hadir', 'Izin', 'Alfa']
            : ['Hadir', 'Izin'];

        $validated = $request->validate([
            'status_kehadiran' => ['required', 'in:' . implode(',', $allowed)],
        ]);

        // Validasi waktu real-time untuk petugas biasa (Admin/Super Admin bebas untuk koreksi)
        if (!$user->isAdmin()) {
            $now = Carbon::now('Asia/Jakarta');

            if ($validated['status_kehadiran'] === 'Hadir' && !$plot->canAttendNow($now)) {
                $reason = $plot->presensiStatusReason($now) ?? 'Di luar jam shift ronda.';
                return back()->with('error', "Presensi Hadir ditolak: {$reason} ({$plot->nama_hari}, {$plot->shift}).");
            }

            if ($validated['status_kehadiran'] === 'Izin' && !$plot->canRequestLeaveNow($now)) {
                $reason = $plot->presensiStatusReason($now) ?? 'Di luar jam shift ronda.';
                return back()->with('error', "Pengajuan Izin ditolak: {$reason} ({$plot->nama_hari}, {$plot->shift}).");
            }
        }

        $plot->update([
            'status_kehadiran' => $validated['status_kehadiran'],
        ]);

        return back()->with('success', 'Status kehadiran berhasil diperbarui menjadi ' . $validated['status_kehadiran'] . '.');
    }
}
