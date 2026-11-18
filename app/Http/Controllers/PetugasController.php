<?php

namespace App\Http\Controllers;

use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PetugasController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        $currentUser = Auth::guard('petugas')->user();

        $query = Petugas::query();

        // Petugas biasa hanya melihat petugas aktif
        if (!$currentUser->isAdmin()) {
            $query->where('is_active', '1');
        } elseif ($status === '1' || $status === '0') {
            $query->where('is_active', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $petugasList = $query->orderByDesc('is_active')->orderBy('nama_lengkap')->get();

        return view('petugas.index', compact('petugasList', 'search', 'status'));
    }

    public function create()
    {
        return view('petugas.create');
    }

    public function store(Request $request)
    {
        $currentUser = Auth::guard('petugas')->user();

        $validated = $request->validate([
            'username'     => ['required', 'string', 'max:20', 'unique:petugas,username'],
            'password'     => ['required', 'string', 'min:6', 'confirmed'],
            'nama_lengkap' => ['required', 'string', 'max:50'],
            'no_hp'        => ['nullable', 'string', 'max:20'],
            'alamat'       => ['nullable', 'string', 'max:100'],
            'gender'       => ['required', 'in:M,F'],
            'role'         => ['nullable', 'in:super_admin,admin,petugas'],
            'is_active'    => ['required', 'in:0,1'],
        ]);

        // Admin biasa tidak boleh membuat akun admin/super_admin
        $targetRole = $validated['role'] ?? 'petugas';
        if (!$currentUser->isSuperAdmin() && $targetRole !== 'petugas') {
            abort(403, 'Hanya Super Admin yang dapat menetapkan role Admin atau Super Admin.');
        }

        Petugas::create([
            'username'     => $validated['username'],
            'password'     => Hash::make($validated['password']),
            'nama_lengkap' => $validated['nama_lengkap'],
            'no_hp'        => $validated['no_hp'] ?? null,
            'alamat'       => $validated['alamat'] ?? null,
            'gender'       => $validated['gender'],
            'role'         => $currentUser->isSuperAdmin() ? $targetRole : 'petugas',
            'is_active'    => $validated['is_active'],
        ]);

        return redirect()->route('petugas.index')->with('success', 'Data petugas berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $petugas = Petugas::findOrFail($id);
        $currentUser = Auth::guard('petugas')->user();

        // Admin biasa tidak boleh mengedit akun super_admin atau sesama admin
        if (!$currentUser->isSuperAdmin() && $petugas->isAdmin()) {
            abort(403, 'Admin tidak dapat mengedit akun Admin lain atau Super Admin.');
        }

        return view('petugas.edit', compact('petugas'));
    }

    public function update(Request $request, $id)
    {
        $petugas = Petugas::findOrFail($id);
        $currentUser = Auth::guard('petugas')->user();

        // Admin biasa tidak boleh mengubah akun super_admin atau sesama admin
        if (!$currentUser->isSuperAdmin() && $petugas->isAdmin()) {
            abort(403, 'Admin tidak dapat mengubah akun Admin lain atau Super Admin.');
        }

        $validated = $request->validate([
            'username'     => ['required', 'string', 'max:20', Rule::unique('petugas', 'username')->ignore($petugas->id_petugas, 'id_petugas')],
            'password'     => ['nullable', 'string', 'min:6', 'confirmed'],
            'nama_lengkap' => ['required', 'string', 'max:50'],
            'no_hp'        => ['nullable', 'string', 'max:20'],
            'alamat'       => ['nullable', 'string', 'max:100'],
            'gender'       => ['required', 'in:M,F'],
            'role'         => ['nullable', 'in:super_admin,admin,petugas'],
            'is_active'    => ['required', 'in:0,1'],
        ]);

        // Jika admin biasa mencoba memanipulasi field role ke selain role asal/petugas -> tolak 403
        if (!$currentUser->isSuperAdmin() && isset($validated['role']) && $validated['role'] !== $petugas->role) {
            abort(403, 'Hanya Super Admin yang dapat mengubah role pengguna.');
        }

        $data = [
            'username'     => $validated['username'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'no_hp'        => $validated['no_hp'] ?? null,
            'alamat'       => $validated['alamat'] ?? null,
            'gender'       => $validated['gender'],
            'role'         => $currentUser->isSuperAdmin() ? ($validated['role'] ?? $petugas->role) : $petugas->role,
            'is_active'    => $validated['is_active'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $petugas->update($data);

        return redirect()->route('petugas.index')->with('success', 'Data petugas berhasil diperbarui.');
    }

    public function updateRole(Request $request, $id)
    {
        $currentUser = Auth::guard('petugas')->user();
        if (!$currentUser->isSuperAdmin()) {
            abort(403, 'Hanya Super Admin yang dapat mengubah role pengguna.');
        }

        $petugas = Petugas::findOrFail($id);
        $validated = $request->validate([
            'role' => ['required', 'in:super_admin,admin,petugas'],
        ]);

        if ($petugas->id_petugas === $currentUser->id_petugas && $validated['role'] !== 'super_admin') {
            return back()->with('error', 'Anda tidak dapat menurunkan role Super Admin milik Anda sendiri.');
        }

        $petugas->update(['role' => $validated['role']]);

        return back()->with('success', "Role {$petugas->nama_lengkap} berhasil diubah menjadi " . strtoupper($validated['role']) . '.');
    }

    public function destroy($id)
    {
        $petugas = Petugas::findOrFail($id);
        $currentUser = Auth::guard('petugas')->user();

        if ($petugas->id_petugas === $currentUser->id_petugas) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang login.');
        }

        if (!$currentUser->isSuperAdmin() && $petugas->isAdmin()) {
            abort(403, 'Admin tidak dapat menghapus akun Admin lain atau Super Admin.');
        }

        $petugas->delete();

        return redirect()->route('petugas.index')->with('success', 'Data petugas berhasil dihapus.');
    }
}
