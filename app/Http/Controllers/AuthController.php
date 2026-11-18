<?php

namespace App\Http\Controllers;

use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $petugas = Petugas::where('username', $credentials['username'])->first();

        if ($petugas && Hash::check($credentials['password'], $petugas->password)) {
            if ($petugas->is_active === '1') {
                Auth::guard('petugas')->login($petugas);
                $request->session()->regenerate();
                return redirect()->intended(route('dashboard'));
            }

            return back()->withErrors(['username' => 'Akun nonaktif'])->onlyInput('username');
        }

        return back()->withErrors(['username' => 'Username atau password salah'])->onlyInput('username');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'username'     => ['required', 'string', 'max:20', 'unique:petugas,username'],
            'password'     => ['required', 'string', 'min:6', 'confirmed'],
            'nama_lengkap' => ['required', 'string', 'max:50'],
            'no_hp'        => ['nullable', 'string', 'max:20'],
            'alamat'       => ['nullable', 'string', 'max:100'],
            'gender'       => ['required', 'in:M,F'],
        ]);

        Petugas::create([
            'username'     => $validated['username'],
            'password'     => Hash::make($validated['password']),
            'nama_lengkap' => $validated['nama_lengkap'],
            'no_hp'        => $validated['no_hp'] ?? null,
            'alamat'       => $validated['alamat'] ?? null,
            'gender'       => $validated['gender'],
            'role'         => 'petugas',
            'is_active'    => '0',
        ]);

        return redirect()->route('login')->with('success', 'Akun berhasil dibuat. Menunggu aktivasi Admin sebelum dapat login.');
    }

    public function logout(Request $request)
    {
        Auth::guard('petugas')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
