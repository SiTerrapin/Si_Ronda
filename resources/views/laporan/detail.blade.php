@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Detail Laporan</h2>

    <div class="card">
        <div class="card-header">
            {{ $laporan->petugas->nama_lengkap }} — {{ \Carbon\Carbon::parse($laporan->tanggal_lapor)->format('d M Y H:i') }}
        </div>
        <div class="card-body">
            <p>{{ $laporan->isi_laporan }}</p>
        </div>
    </div>

    <a href="{{ route('laporan.index') }}" class="btn btn-secondary mt-3">Kembali</a>
</div>
@endsection