@extends('layouts.app')
@section('title', 'Detail Laporan - SiRonda')

@section('content')
<div class="container" style="max-width: 760px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0 fw-bold">📄 Detail Laporan Patroli</h3>
        <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary btn-sm">← Kembali</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="modern-card overflow-hidden">
        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <strong class="fs-6">{{ $laporan->petugas->nama_lengkap ?? '-' }}</strong>
                <span class="text-muted ms-2 small">{{ \Carbon\Carbon::parse($laporan->tanggal_lapor)->format('d M Y, H:i') }} WIB</span>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-{{ $laporan->kategori_situasi === 'Aman' ? 'success' : ($laporan->kategori_situasi === 'Mencurigakan' ? 'warning text-dark' : 'danger') }}">
                    {{ $laporan->kategori_situasi }}
                </span>
                <span class="badge bg-{{ $laporan->status_verifikasi === 'Diverifikasi' ? 'success' : 'warning text-dark' }}">
                    {{ $laporan->status_verifikasi === 'Diverifikasi' ? '✔️ Diverifikasi' : '⏳ Pending' }}
                </span>
            </div>
        </div>
        <div class="p-4">
            @if($laporan->plot)
                <div class="mb-3">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                        🛡️ Jadwal: {{ $laporan->plot->nama_hari }} — {{ $laporan->plot->pos_jaga }} ({{ $laporan->plot->shift }})
                    </span>
                </div>
            @endif

            <p class="fs-6 mb-3">{{ $laporan->isi_laporan }}</p>

            @if($laporan->foto_bukti)
                <div class="mt-3">
                    <h6 class="text-muted small fw-semibold mb-2">Foto Bukti Lapangan:</h6>
                    <img src="{{ asset('storage/' . $laporan->foto_bukti) }}" alt="Foto Bukti" class="img-fluid rounded-3 border" style="max-height: 400px;">
                </div>
            @endif
        </div>
        <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            @if(Auth::guard('petugas')->user()->isAdmin())
                <form method="POST" action="{{ route('laporan.verify', $laporan->id_lapor) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status_verifikasi" value="{{ $laporan->status_verifikasi === 'Diverifikasi' ? 'Pending' : 'Diverifikasi' }}">
                    <button type="submit" class="btn btn-sm btn-{{ $laporan->status_verifikasi === 'Diverifikasi' ? 'warning' : 'success' }}">
                        {{ $laporan->status_verifikasi === 'Diverifikasi' ? 'Kembalikan ke Pending' : '✔️ Verifikasi Laporan' }}
                    </button>
                </form>
            @else
                <span></span>
            @endif

            @if(Auth::guard('petugas')->user()->isAdmin() || $laporan->petugas_id === Auth::guard('petugas')->id())
                <form method="POST" action="{{ route('laporan.destroy', $laporan->id_lapor) }}" onsubmit="return confirm('Hapus laporan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Hapus Laporan</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
