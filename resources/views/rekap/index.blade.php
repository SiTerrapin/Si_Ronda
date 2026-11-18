@extends('layouts.app')
@section('title', 'Rekap Ronda Harian - SiRonda')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0 fw-bold">📊 Rekapitulasi Ronda Harian & Keaktifan</h3>
        <small class="text-muted">Ringkasan kehadiran malam (Hadir, Izin, Alfa) dan kontribusi laporan petugas</small>
    </div>
    <button onclick="window.print()" class="btn btn-outline-dark no-print">🖨️ Cetak / Simpan PDF</button>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- Segmen Generate Rekap Harian --}}
<div class="filter-panel mb-4 no-print">
    <h6 class="fw-bold mb-3">⚡ Generate Rekap Kehadiran Ronda Harian</h6>
    <form method="POST" action="{{ route('rekap.generate') }}" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Tanggal Ronda</label>
            <input type="date" name="tanggal_rekap" class="form-control @error('tanggal_rekap') is-invalid @enderror" value="{{ old('tanggal_rekap', date('Y-m-d')) }}" required>
            @error('tanggal_rekap')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Status Keamanan Malam</label>
            <select name="status_keamanan" class="form-select @error('status_keamanan') is-invalid @enderror" required>
                <option value="Aman">Aman</option>
                <option value="Waspada">Waspada</option>
                <option value="Darurat">Darurat</option>
            </select>
            @error('status_keamanan')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Catatan Rekap <span class="text-muted">(opsional)</span></label>
            <input type="text" name="isi_rekap" class="form-control" placeholder="Ringkasan situasi malam tersebut...">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Simpan Rekap</button>
        </div>
    </form>
</div>

{{-- Segmen Filter Tanggal --}}
<div class="filter-panel mb-4 no-print">
    <form method="GET" action="{{ route('rekap.index') }}" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold mb-1">Dari Tanggal</label>
            <input type="date" name="dari" class="form-control" value="{{ $dari }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold mb-1">Sampai Tanggal</label>
            <input type="date" name="sampai" class="form-control" value="{{ $sampai }}">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Filter Tanggal</button>
            <a href="{{ route('rekap.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

{{-- Tabel Rekap Harian --}}
<div class="modern-card overflow-hidden mb-4">
    <div class="p-3 bg-light border-bottom fw-bold">Riwayat Rekap Kehadiran Harian</div>
    <div class="table-responsive">
        <table class="table table-hover table-bordered mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Terjadwal</th>
                    <th>Hadir</th>
                    <th>Izin</th>
                    <th>Alfa</th>
                    <th>Status Keamanan</th>
                    <th>Catatan</th>
                    <th>Dibuat Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekapHarian as $r)
                    <tr>
                        <td class="fw-semibold">{{ \Carbon\Carbon::parse($r->tanggal_rekap)->format('d M Y') }}</td>
                        <td>{{ $r->total_petugas_terjadwal }}</td>
                        <td><span class="badge bg-success">{{ $r->total_petugas_hadir }}</span></td>
                        <td><span class="badge bg-warning text-dark">{{ $r->total_petugas_ijin }}</span></td>
                        <td><span class="badge bg-danger">{{ $r->total_petugas_alpha }}</span></td>
                        <td>
                            <span class="badge bg-{{ $r->status_keamanan === 'Aman' ? 'success' : ($r->status_keamanan === 'Waspada' ? 'warning text-dark' : 'danger') }}">
                                {{ $r->status_keamanan }}
                            </span>
                        </td>
                        <td>{{ $r->isi_rekap }}</td>
                        <td>{{ $r->petugas->nama_lengkap ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada data rekap harian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Tabel Rekap Laporan Per Petugas --}}
<div class="modern-card overflow-hidden">
    <div class="p-3 bg-light border-bottom fw-bold">Kontribusi Laporan Patroli per Petugas</div>
    <div class="table-responsive">
        <table class="table table-hover table-bordered mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nama Petugas</th>
                    <th>Total Laporan</th>
                    <th>Laporan Terverifikasi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rekapLaporan as $item)
                    <tr>
                        <td class="fw-semibold">{{ $item->nama_lengkap }}</td>
                        <td><span class="badge bg-primary">{{ $item->total_laporan }}</span></td>
                        <td><span class="badge bg-success">{{ $item->terverifikasi }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted">Belum ada laporan petugas yang masuk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
