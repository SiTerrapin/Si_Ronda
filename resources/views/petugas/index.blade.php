@extends('layouts.app')
@section('title', 'Data Petugas - SiRonda')

@section('content')
@php $currentUser = Auth::guard('petugas')->user(); @endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0 fw-bold">👥 Daftar Petugas Ronda</h3>
        <small class="text-muted">Kelola anggota aktif, aktivasi pendaftar baru, dan peran petugas</small>
    </div>
    @if($currentUser->isAdmin())
        <a href="{{ route('petugas.create') }}" class="btn btn-primary">+ Tambah Petugas</a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="filter-panel mb-4">
    <form method="GET" action="{{ route('petugas.index') }}" class="row g-2 align-items-center">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control" placeholder="Cari nama, username, atau RT/Blok..." value="{{ $search }}">
        </div>
        @if($currentUser->isAdmin())
            <div class="col-md-4">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="1" {{ $status === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ $status === '0' ? 'selected' : '' }}>Nonaktif / Menunggu Aktivasi</option>
                </select>
            </div>
        @endif
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
            <a href="{{ route('petugas.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="modern-card overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover table-bordered mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th>No. HP</th>
                    <th>RT / Blok</th>
                    <th>Gender</th>
                    <th>Role</th>
                    <th>Status</th>
                    @if($currentUser->isAdmin())
                        <th class="text-center">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($petugasList as $p)
                    @php
                        $roleBadge = $p->role === 'super_admin' ? 'danger' : ($p->role === 'admin' ? 'warning text-dark' : 'primary bg-opacity-10 text-primary');
                        $canManageTarget = $currentUser->isSuperAdmin() || !$p->isAdmin();
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $p->nama_lengkap }}</td>
                        <td><code>{{ $p->username }}</code></td>
                        <td>{{ $p->no_hp ?? '-' }}</td>
                        <td>{{ $p->alamat ?? '-' }}</td>
                        <td>{{ $p->gender === 'M' ? 'Laki-laki' : 'Perempuan' }}</td>
                        <td>
                            @if($currentUser->isSuperAdmin() && $p->id_petugas !== $currentUser->id_petugas)
                                <form method="POST" action="{{ route('petugas.updateRole', $p->id_petugas) }}" class="d-flex align-items-center gap-1">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" class="form-select form-select-sm" style="width: auto;">
                                        <option value="petugas" {{ $p->role === 'petugas' ? 'selected' : '' }}>PETUGAS</option>
                                        <option value="admin" {{ $p->role === 'admin' ? 'selected' : '' }}>ADMIN</option>
                                        <option value="super_admin" {{ $p->role === 'super_admin' ? 'selected' : '' }}>SUPER_ADMIN</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Ubah</button>
                                </form>
                            @else
                                <span class="badge bg-{{ $roleBadge }}">{{ strtoupper($p->role) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($p->is_active === '1')
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        @if($currentUser->isAdmin())
                            <td class="text-center">
                                @if($canManageTarget)
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('petugas.edit', $p->id_petugas) }}" class="btn btn-sm btn-warning">Edit</a>
                                        @if($p->id_petugas !== $currentUser->id_petugas)
                                            <form method="POST" action="{{ route('petugas.destroy', $p->id_petugas) }}" onsubmit="return confirm('Yakin ingin menghapus petugas ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        @endif
                                    </div>
                                @else
                                    <span class="badge bg-secondary">Terproteksi</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $currentUser->isAdmin() ? 8 : 7 }}" class="text-center py-4 text-muted">Belum ada petugas aktif.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
