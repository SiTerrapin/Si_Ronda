<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Plot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('petugas')->user();
        $kategori = $request->query('kategori');
        $status = $request->query('status');

        $query = Laporan::with(['petugas', 'plot'])->orderByDesc('tanggal_lapor');

        if ($kategori && in_array($kategori, ['Aman', 'Mencurigakan', 'Darurat/Bencana', 'Kriminalitas'], true)) {
            $query->where('kategori_situasi', $kategori);
        }

        if ($status && in_array($status, ['Pending', 'Diverifikasi'], true)) {
            $query->where('status_verifikasi', $status);
        }

        $laporan = $query->get();

        $jadwalSaya = Plot::where('petugas_id', $user->id_petugas)
            ->where('is_active', '1')
            ->get();

        return view('laporan.index', compact('laporan', 'jadwalSaya', 'kategori', 'status'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plot_id'          => ['nullable', 'exists:plot_ronda,id_plot'],
            'kategori_situasi' => ['nullable', 'in:Aman,Mencurigakan,Darurat/Bencana,Kriminalitas'],
            'isi_laporan'      => ['required', 'string'],
            'foto_bukti'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('laporan', 'public');
        }

        Laporan::create([
            'petugas_id'        => Auth::guard('petugas')->id(),
            'plot_id'           => $validated['plot_id'] ?? null,
            'tanggal_lapor'     => Carbon::now('Asia/Jakarta'),
            'kategori_situasi'  => $validated['kategori_situasi'] ?? 'Aman',
            'isi_laporan'       => $validated['isi_laporan'],
            'foto_bukti'        => $fotoPath,
            'status_verifikasi' => 'Pending',
        ]);

        return back()->with('success', 'Laporan berhasil disimpan.');
    }

    public function detail($id)
    {
        $laporan = Laporan::with(['petugas', 'plot'])->findOrFail($id);
        return view('laporan.detail', compact('laporan'));
    }

    public function verify(Request $request, $id)
    {
        $laporan = Laporan::findOrFail($id);

        $validated = $request->validate([
            'status_verifikasi' => ['required', 'in:Pending,Diverifikasi'],
        ]);

        $laporan->update([
            'status_verifikasi' => $validated['status_verifikasi'],
        ]);

        return back()->with('success', 'Status verifikasi laporan diperbarui menjadi ' . $validated['status_verifikasi'] . '.');
    }

    public function destroy($id)
    {
        $laporan = Laporan::findOrFail($id);
        $user = Auth::guard('petugas')->user();

        if (!$user->isAdmin() && $laporan->petugas_id !== $user->id_petugas) {
            abort(403, 'Anda tidak berhak menghapus laporan ini.');
        }

        if ($laporan->foto_bukti && Storage::disk('public')->exists($laporan->foto_bukti)) {
            Storage::disk('public')->delete($laporan->foto_bukti);
        }

        $laporan->delete();

        return redirect()->route('laporan.index')->with('success', 'Laporan berhasil dihapus.');
    }
}
