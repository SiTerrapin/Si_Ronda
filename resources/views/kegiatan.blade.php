@extends('layouts.app')
@section('title', 'Kegiatan & Situasi Ronda - SiRonda')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="mb-0 fw-bold">📰 Feed Kegiatan & Situasi Keamanan</h3>
        <small class="text-muted">Laporan patroli malam yang telah diverifikasi oleh pengurus keamanan lingkungan</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if(Auth::guard('petugas')->check() && Auth::guard('petugas')->user()->isSuperAdmin())
            <a href="{{ route('portal.settings') }}" class="btn btn-sm btn-warning fw-semibold">🎨 Ubah Gambar Portal</a>
        @endif
        <span class="badge bg-info text-dark fs-6 px-3 py-2">{{ $kegiatan->count() }} Laporan Terverifikasi</span>
    </div>
</div>

<div class="modern-card p-3 mb-4 d-flex flex-wrap align-items-center gap-3">
    <img src="{{ asset($portalSettings['kegiatan_image']) }}" alt="Ilustrasi Kegiatan Ronda" class="rounded-3" style="width: 120px; height: 75px; object-fit: cover;">
    <div>
        <h6 class="fw-bold mb-1">Dokumentasi Patroli Keamanan Lingkungan</h6>
        <small class="text-muted">Setiap laporan di bawah ini telah diverifikasi oleh Admin / Koordinator Keamanan sebelum dipublikasikan ke warga.</small>
    </div>
</div>

<div class="row g-4">
    @forelse($kegiatan as $item)
        <div class="col-md-6 col-lg-4">
            <div class="feed-card">
                @if($item->foto_bukti)
                    <img src="{{ asset('storage/' . $item->foto_bukti) }}" alt="Foto Bukti Patroli" class="feed-thumb">
                @else
                    <div class="bg-light border-bottom d-flex align-items-center justify-content-center text-muted" style="height: 120px;">
                        <span>🛡️ Dokumentasi Patroli Malam</span>
                    </div>
                @endif

                <div class="p-3 flex-grow-1 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-{{ $item->kategori_situasi === 'Aman' ? 'success' : ($item->kategori_situasi === 'Mencurigakan' ? 'warning text-dark' : 'danger') }}">
                            {{ $item->kategori_situasi }}
                        </span>
                        <small class="text-muted">{{ \Carbon\Carbon::parse($item->tanggal_lapor)->format('d M Y, H:i') }} WIB</small>
                    </div>
                    <p class="mb-3 flex-grow-1">{{ $item->isi_laporan }}</p>
                    <div class="pt-2 border-top d-flex justify-content-between align-items-center small">
                        <span class="text-muted">Pelapor: <strong class="text-dark">{{ $item->petugas->nama_lengkap ?? '-' }}</strong></span>
                        <span class="badge bg-info bg-opacity-10 text-info border border-info">✔️ Diverifikasi</span>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="modern-card p-5 text-center text-muted">Belum ada laporan situasi yang diverifikasi untuk ditampilkan.</div>
        </div>
    @endforelse
</div>
@endsection
