@extends('layouts.app')
@section('title', 'Direktori Anggota Ronda - SiRonda')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0 fw-bold">👥 Direktori Anggota Ronda Aktif</h3>
        <small class="text-muted">Daftar warga yang terdaftar dan siap menjaga keamanan lingkungan</small>
    </div>
    <span class="badge bg-success fs-6 px-3 py-2">{{ $anggota->count() }} Anggota Aktif</span>
</div>

<div class="filter-panel mb-4">
    <form method="GET" action="{{ route('public.anggota') }}" class="row g-2 align-items-center">
        <div class="col-md-6">
            <input type="text" name="q" class="form-control" placeholder="Cari nama anggota atau RT/Blok..." value="{{ $search }}">
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Cari</button>
            <a href="{{ route('public.anggota') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="row g-3">
    @forelse($anggota as $item)
        @php
            $parts = preg_split('/\s+/', trim($item->nama_lengkap));
            $inisial = strtoupper(substr($parts[0] ?? 'A', 0, 1) . substr($parts[1] ?? '', 0, 1));
        @endphp
        <div class="col-sm-6 col-lg-4">
            <div class="member-card d-flex align-items-center gap-3">
                <div class="avatar-circle">{{ $inisial }}</div>
                <div class="flex-grow-1 min-w-0">
                    <h6 class="mb-1 fw-bold text-truncate">{{ $item->nama_lengkap }}</h6>
                    <div class="small text-muted mb-2">
                        📍 {{ $item->alamat ?? 'RT / Blok belum diisi' }}
                        <span class="mx-1">•</span>
                        {{ $item->gender === 'M' ? 'Laki-laki' : 'Perempuan' }}
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <span class="badge bg-success">Aktif</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary">{{ ucfirst($item->role) }}</span>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="modern-card p-5 text-center text-muted">Belum ada anggota ronda aktif yang terdaftar.</div>
        </div>
    @endforelse
</div>
@endsection
