@extends('layouts.app')
@section('title', 'Edit Petugas - SiRonda')

@section('content')
<div class="container" style="max-width: 650px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">✏️ Edit Data Petugas</h3>
        <a href="{{ route('petugas.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
    </div>

    <div class="modern-card p-4">
            <form method="POST" action="{{ route('petugas.update', $petugas->id_petugas) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $petugas->username) }}" required>
                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap', $petugas->nama_lengkap) }}" required>
                        @error('nama_lengkap')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">No. HP / WhatsApp</label>
                        <input type="text" name="no_hp" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', $petugas->no_hp) }}">
                        @error('no_hp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Alamat (RT / Blok)</label>
                        <input type="text" name="alamat" class="form-control @error('alamat') is-invalid @enderror" value="{{ old('alamat', $petugas->alamat) }}">
                        @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Gender</label>
                        <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                            <option value="M" {{ old('gender', $petugas->gender) === 'M' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="F" {{ old('gender', $petugas->gender) === 'F' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Role</label>
                        @if(Auth::guard('petugas')->user()->isSuperAdmin())
                            <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                <option value="petugas" {{ old('role', $petugas->role) === 'petugas' ? 'selected' : '' }}>Petugas</option>
                                <option value="admin" {{ old('role', $petugas->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="super_admin" {{ old('role', $petugas->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            </select>
                        @else
                            <input type="text" class="form-control" value="{{ ucfirst($petugas->role) }}" disabled>
                        @endif
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
                            <option value="1" {{ old('is_active', $petugas->is_active) === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('is_active', $petugas->is_active) === '0' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password Baru <small class="text-secondary">(opsional)</small></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Kosongkan jika tidak diubah">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-warning w-100">Perbarui Data Petugas</button>
            </form>
    </div>
</div>
@endsection
