<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Plot;
use App\Services\PortalSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::guard('petugas')->user();
        $jadwalSaya = Plot::where('petugas_id', $user->id_petugas)
            ->where('is_active', '1')
            ->get();
        $laporanSaya = Laporan::where('petugas_id', $user->id_petugas)
            ->orderByDesc('tanggal_lapor')
            ->take(5)
            ->get();
        $totalLaporanSaya = Laporan::where('petugas_id', $user->id_petugas)->count();
        $portalSettings = $user->isSuperAdmin() ? PortalSettings::get() : null;

        return view('profile.index', compact(
            'user',
            'jadwalSaya',
            'laporanSaya',
            'totalLaporanSaya',
            'portalSettings'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::guard('petugas')->user();

        $rules = [
            'username'     => ['required', 'string', 'max:20', Rule::unique('petugas', 'username')->ignore($user->id_petugas, 'id_petugas')],
            'nama_lengkap' => ['required', 'string', 'max:50'],
            'no_hp'        => ['nullable', 'string', 'max:20'],
            'alamat'       => ['nullable', 'string', 'max:100'],
            'gender'       => ['required', 'in:M,F'],
        ];

        if ($request->filled('new_password')) {
            $rules['current_password'] = ['required', 'string'];
            $rules['new_password'] = ['required', 'string', 'min:6', 'confirmed'];
        }

        $validated = $request->validate($rules);

        if ($request->filled('new_password')) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()
                    ->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])
                    ->withInput();
            }
        }

        $data = [
            'username'     => $validated['username'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'no_hp'        => $validated['no_hp'] ?? null,
            'alamat'       => $validated['alamat'] ?? null,
            'gender'       => $validated['gender'],
        ];

        if ($request->filled('new_password')) {
            $data['password'] = Hash::make($validated['new_password']);
        }

        $user->update($data);

        return redirect()->route('profile.index')->with('success', 'Profil akun Anda berhasil diperbarui.');
    }
}
