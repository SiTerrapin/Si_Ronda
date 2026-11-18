@extends('layouts.app')
@section('title', 'Registrasi Petugas - SiRonda')

@section('content')
<div class="container mt-4" style="max-width: 520px;">
    <div class="modern-card p-4">
        <div class="text-center mb-3">
            <h4 class="fw-bold mb-1">Registrasi Petugas SiRonda</h4>
            <p class="text-muted small mb-0">Akun baru akan diverifikasi & diaktifkan oleh Admin RT/RW sebelum digunakan.</p>
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

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold small">Username</label>
                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" placeholder="Maksimal 20 karakter" value="{{ old('username') }}" required>
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" placeholder="Nama lengkap warga" value="{{ old('nama_lengkap') }}" required>
                @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold small">No. HP / WhatsApp</label>
                    <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror" placeholder="0812xxxxxxxx" value="{{ old('no_hp') }}">
                    @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold small">Jenis Kelamin</label>
                    <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                        <option value="">Pilih Gender</option>
                        <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Alamat (RT / Blok)</label>
                <input type="text" name="alamat" class="form-control @error('alamat') is-invalid @enderror" placeholder="Contoh: RT 02 / Blok B" value="{{ old('alamat') }}">
                @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold small">Password</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password" required>
            </div>

            <button type="submit" class="btn btn-success w-100 fw-semibold">Daftar</button>
        </form>

        <div class="mt-3 text-center small">
            <a href="{{ route('login') }}" class="text-decoration-none">Sudah punya akun? Login di sini</a>
        </div>
    </div>
</div>
@endsection
