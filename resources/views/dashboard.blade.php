@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="container mt-4">
    <h3 class="mb-4">{{ $sapaan }}, {{ $petugas->nama_lengkap }}!</h3>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">Petugas Aktif</div>
                <div class="card-body">
                    <h5>{{ $jumlahPetugas }}</h5>
                    <p>Petugas yang aktif saat ini.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">Total Jadwal</div>
                <div class="card-body">
                    <h5>{{ $jumlahJadwal }}</h5>
                    <p>Jumlah plot ronda yang telah dijadwalkan.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-header bg-success text-white">Total Laporan</div>
                <div class="card-body">
                    <h5>{{ $jumlahLaporan }}</h5>
                    <p>Laporan yang telah dikirim oleh petugas.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">Aktivitas Anda Hari Ini</div>
                <div class="card-body">
                    <p><strong>Jadwal Ronda:</strong> {{ $jadwalHariIni }} kali</p>
                    <p><strong>Laporan Terkirim:</strong> {{ $laporanHariIni }} laporan</p>
                    @if($laporanHariIni == 0)
                        <div class="alert alert-danger mt-3">⚠️ Anda belum mengirim laporan hari ini.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-secondary">
                <div class="card-header bg-secondary text-white">Navigasi Cepat</div>
                <div class="card-body">
                    <a href="{{ route('plot.index') }}" class="btn btn-outline-primary w-100 mb-2">📅 Lihat Jadwal</a>
                    <a href="{{ route('laporan.index') }}" class="btn btn-outline-success w-100 mb-2">📝 Laporan Harian</a>
                    <a href="{{ route('rekap.index') }}" class="btn btn-outline-info w-100">📊 Rekap Laporan</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection