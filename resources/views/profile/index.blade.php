@extends('layouts.app')
@section('title', 'Profil & Pengaturan Akun - SiRonda')

@section('content')
@php
    $parts = preg_split('/\s+/', trim($user->nama_lengkap));
    $inisial = strtoupper(substr($parts[0] ?? 'P', 0, 1) . substr($parts[1] ?? '', 0, 1));
    $roleBadge = $user->role === 'super_admin' ? 'danger' : ($user->role === 'admin' ? 'warning text-dark' : 'primary');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0 fw-bold">👤 Profil & Edit Akun Mandiri</h3>
        <small class="text-muted">Kelola informasi pribadi, kata sandi, dan lihat ringkasan aktivitas Anda</small>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    {{-- Kolom Kiri: Kartu Profil & Riwayat Singkat --}}
    <div class="col-lg-4">
        <div class="modern-card p-4 text-center mb-4">
            <div class="avatar-circle avatar-circle-lg mx-auto mb-3">{{ $inisial }}</div>
            <h5 class="fw-bold mb-1">{{ $user->nama_lengkap }}</h5>
            <div class="text-muted small mb-2"><code>@ {{ $user->username }}</code></div>
            <div class="d-flex justify-content-center gap-2 mb-3">
                <span class="badge bg-{{ $roleBadge }}">{{ strtoupper($user->role) }}</span>
                <span class="badge bg-{{ $user->is_active === '1' ? 'success' : 'danger' }}">
                    {{ $user->is_active === '1' ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <div class="text-start small border-top pt-3">
                <div class="mb-1"><strong>No. HP:</strong> {{ $user->no_hp ?? '-' }}</div>
                <div class="mb-1"><strong>Alamat:</strong> {{ $user->alamat ?? '-' }}</div>
                <div><strong>Total Laporan:</strong> {{ $totalLaporanSaya }} laporan</div>
            </div>
        </div>

        <div class="modern-card p-3 mb-4">
            <h6 class="fw-bold mb-2">📅 Jadwal Jaga Saya</h6>
            @forelse($jadwalSaya as $j)
                <div class="small py-1 border-bottom d-flex justify-content-between">
                    <span><strong>{{ $j->nama_hari }}</strong> ({{ $j->pos_jaga }})</span>
                    <span class="badge bg-secondary">{{ $j->status_kehadiran }}</span>
                </div>
            @empty
                <div class="small text-muted">Belum ada jadwal jaga.</div>
            @endforelse
        </div>

        @if($user->isSuperAdmin() && $portalSettings)
            <div class="modern-card p-4 border-primary">
                <h6 class="fw-bold mb-2 text-primary">🎨 Pengaturan Gambar Portal Warga</h6>
                <p class="small text-muted mb-3">Sebagai Super Admin, Anda dapat mengubah gambar Hero Background dan ilustrasi Kegiatan Portal Warga.</p>
                <a href="{{ route('portal.settings') }}" class="btn btn-primary btn-sm w-100">Buka Pengaturan Gambar Portal</a>
            </div>
        @endif
    </div>

    {{-- Kolom Kanan: Form Edit Profil & Ubah Password --}}
    <div class="col-lg-8">
        <div class="modern-card p-4">
            <h5 class="fw-bold mb-3">✏️ Perbarui Data Profil</h5>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required>
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" required>
                        @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">No. HP / WhatsApp</label>
                        <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', $user->no_hp) }}">
                        @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Jenis Kelamin</label>
                        <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                            <option value="M" {{ old('gender', $user->gender) === 'M' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="F" {{ old('gender', $user->gender) === 'F' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Alamat (RT / Blok)</label>
                    <input type="text" name="alamat" class="form-control @error('alamat') is-invalid @enderror" value="{{ old('alamat', $user->alamat) }}">
                    @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <hr class="my-4">
                <h6 class="fw-bold mb-1">🔒 Ubah Password <span class="text-muted fw-normal small">(opsional)</span></h6>
                <p class="small text-muted mb-3">Kosongkan kolom di bawah jika tidak ingin mengubah kata sandi.</p>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Password Saat Ini</label>
                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Wajib diisi jika ingin mengganti password">
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Password Baru</label>
                        <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" placeholder="Minimal 6 karakter">
                        @error('new_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Konfirmasi Password Baru</label>
                        <input type="password" name="new_password_confirmation" class="form-control" placeholder="Ulangi password baru">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary px-4 fw-semibold">Simpan Perubahan Profil</button>
            </form>
        </div>
    </div>
</div>
@endsection
