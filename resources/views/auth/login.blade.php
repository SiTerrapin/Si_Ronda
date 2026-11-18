@extends('layouts.app')
@section('title', 'Login Petugas - SiRonda')

@section('content')
<div class="container mt-5" style="max-width: 440px;">
    <div class="modern-card p-4">
        <div class="text-center mb-4">
            <img src="{{ asset('assets/icon/LogoNav_sironda.png') }}" alt="SiRonda" style="height: 40px;" class="mb-2">
            <h4 class="fw-bold mb-1">Login Petugas SiRonda</h4>
            <small class="text-muted">Masuk untuk mengelola jadwal dan laporan ronda</small>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold small">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" value="{{ old('username') }}" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold small">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-semibold">Login</button>
        </form>

        <div class="mt-3 text-center small">
            <a href="{{ route('register') }}" class="text-decoration-none">Belum punya akun? Daftar di sini</a>
            <span class="mx-2 text-muted">•</span>
            <a href="{{ route('beranda') }}" class="text-decoration-none text-secondary">Portal Warga</a>
        </div>
    </div>
</div>
@endsection
