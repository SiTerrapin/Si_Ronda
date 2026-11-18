@extends('layouts.app')
@section('title', 'Beranda Warga - SiRonda')

@section('content')
{{-- Hero Banner dengan Overlay Gelap di atas gambar portal --}}
<div class="hero-banner mb-4" style="background-image: url('{{ asset($portalSettings['hero_background']) }}');">
    <div class="hero-overlay text-white">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge rounded-pill px-3 py-2 mb-3" style="background-color: rgba(16, 185, 129, 0.25); border: 1px solid #10b981; color: #a7f3d0;">
                    🛡️ Portal Keamanan Lingkungan RT / RW
                </span>
                <h1 class="display-6 fw-bold mb-2">{{ $portalSettings['hero_title'] }}</h1>
                <p class="lead opacity-75 mb-4">
                    {{ $portalSettings['hero_subtitle'] }}
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('public.jadwal') }}" class="btn btn-success px-4 fw-semibold">📅 Lihat Jadwal Mingguan</a>
                    <a href="{{ route('public.kegiatan') }}" class="btn btn-outline-light px-4">📰 Laporan Situasi Terkini</a>
                    @if(Auth::guard('petugas')->check() && Auth::guard('petugas')->user()->isSuperAdmin())
                        <a href="{{ route('portal.settings') }}" class="btn btn-warning px-3 fw-semibold">🎨 Ubah Gambar Portal</a>
                    @endif
                </div>
            </div>
            <div class="col-lg-4 text-center d-none d-lg-block">
                <img src="{{ asset($portalSettings['kegiatan_image']) }}" alt="Kegiatan Ronda" class="img-fluid rounded-4 shadow-lg border border-light border-opacity-25" style="max-height: 210px; object-fit: cover;">
            </div>
        </div>
    </div>
</div>

{{-- Segmen Khusus: Petugas Siaga Malam Ini (Kartu Profil Horizontal) --}}
<div class="modern-card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="d-flex align-items-center gap-2">
            <h4 class="mb-0 fw-bold">🌙 Petugas Siaga Malam Ini ({{ $hariIni }})</h4>
            <span class="badge bg-success badge-pulse">Aktif Malam Ini</span>
        </div>
        <a href="{{ route('public.jadwal') }}" class="btn btn-sm btn-outline-primary">Lihat Jadwal 1 Minggu →</a>
    </div>

    <div class="row g-3">
        @forelse($jadwalMalamIni as $item)
            @php
                $nama = $item->petugas->nama_lengkap ?? 'Petugas';
                $parts = preg_split('/\s+/', trim($nama));
                $inisial = strtoupper(substr($parts[0] ?? 'P', 0, 1) . substr($parts[1] ?? '', 0, 1));
                $badgeKehadiran = $item->status_kehadiran === 'Hadir' ? 'success' : ($item->status_kehadiran === 'Izin' ? 'warning text-dark' : ($item->status_kehadiran === 'Alfa' ? 'danger' : 'secondary'));
            @endphp
            <div class="col-md-4">
                <div class="member-card d-flex align-items-center gap-3 p-3" style="background-color: #f8fafc;">
                    <div class="avatar-circle">{{ $inisial }}</div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <h6 class="mb-0 fw-bold text-truncate">{{ $nama }}</h6>
                            @if($item->is_leader === '1')
                                <span class="badge bg-warning text-dark">Komandan</span>
                            @endif
                        </div>
                        <div class="small text-muted mt-1">
                            <span class="badge bg-primary bg-opacity-10 text-primary">{{ $item->pos_jaga }}</span>
                            <span class="ms-1">{{ $item->shift }}</span>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-{{ $badgeKehadiran }}">{{ $item->status_kehadiran }}</span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-secondary mb-0 text-center">Belum ada jadwal petugas ronda untuk malam {{ $hariIni }}.</div>
            </div>
        @endforelse
    </div>
</div>

{{-- Kartu Statistik Ringkas --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card accent-emerald p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Petugas Siaga Hari Ini</span>
                    <div class="stat-value mt-1">{{ $jadwalMalamIni->count() }}</div>
                    <small class="text-muted">Bertugas pada hari {{ $hariIni }}</small>
                </div>
                <span class="stat-icon bg-success bg-opacity-10 text-success">🛡️</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card accent-blue p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Petugas Aktif</span>
                    <div class="stat-value mt-1">{{ $totalPetugasAktif }}</div>
                    <small class="text-muted">Warga terdaftar di direktori ronda</small>
                </div>
                <span class="stat-icon bg-primary bg-opacity-10 text-primary">👥</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card accent-amber p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Laporan Terverifikasi Minggu Ini</span>
                    <div class="stat-value mt-1">{{ $laporanMingguIni }}</div>
                    <small class="text-muted">Sudah disahkan oleh Admin/RT</small>
                </div>
                <span class="stat-icon bg-warning bg-opacity-10 text-warning">📋</span>
            </div>
        </div>
    </div>
</div>

{{-- Berita / Situasi Keamanan Terbaru --}}
<div class="modern-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 fw-bold">📢 Situasi Keamanan Terbaru</h5>
        <a href="{{ route('public.kegiatan') }}" class="btn btn-sm btn-outline-secondary">Semua Kegiatan →</a>
    </div>
    <div class="row g-3">
        @forelse($kegiatanTerbaru as $lap)
            <div class="col-md-4">
                <div class="feed-card">
                    @if($lap->foto_bukti)
                        <img src="{{ asset('storage/' . $lap->foto_bukti) }}" alt="Foto Patroli" class="feed-thumb">
                    @endif
                    <div class="p-3 flex-grow-1 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-{{ $lap->kategori_situasi === 'Aman' ? 'success' : ($lap->kategori_situasi === 'Mencurigakan' ? 'warning text-dark' : 'danger') }}">
                                {{ $lap->kategori_situasi }}
                            </span>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($lap->tanggal_lapor)->format('d M Y H:i') }}</small>
                        </div>
                        <p class="mb-3 small flex-grow-1">{{ \Illuminate\Support\Str::limit($lap->isi_laporan, 125) }}</p>
                        <div class="small text-primary fw-semibold">Pelapor: {{ $lap->petugas->nama_lengkap ?? '-' }}</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-3">Belum ada laporan situasi yang diverifikasi untuk publik.</div>
        @endforelse
    </div>
</div>
@endsection
