<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Petugas;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showlogin() {
        return view('auth.login');
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        $petugas = Petugas::where('username', $credentials['username'])->first();

        if ($petugas && Hash::check($credentials['password'], $petugas->password)) {
            if ($petugas->is_active === '1') {
                Auth::guard('petugas')->login($petugas);
                $request->session()->regenerate();
                return redirect()->intended('/dashboard');
            } else {
                return back()->withErrors(['username' => 'Akun nonaktif']);
            }
        }

        return back()->withErrors(['username' => 'Username atau password salah']);
    }

    public function showRegister() {
        return view('auth.register');
    }

    public function register(Request $request) {
        $validated = $request->validate([
            'username' => ['required', 'unique:petugas'],
            'password' => ['required', 'min:6', 'confirmed'],
            'nama_lengkap' => ['required'],
            'gender' => ['required', 'in:M,F'],
        ]);

        $petugas = Petugas::create([
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'nama_lengkap' => $validated['nama_lengkap'],
            'gender' => $validated['gender'],
            'is_active' => '1',
        ]);

        return redirect()->route('login')->with('success', 'Akun berhasil dibuat. Silakan login.');
    }

    public function logout(Request $request) {
        Auth::guard('petugas')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

}
