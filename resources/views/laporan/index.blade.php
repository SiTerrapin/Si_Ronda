@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Laporan Ronda</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('laporan.store') }}">
        @csrf
        <div class="mb-3">
            <label for="isi_laporan" class="form-label">Isi Laporan</label>
            <textarea name="isi_laporan" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Kirim Laporan</button>
    </form>

    <hr>

    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Petugas</th>
                <th>Isi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal_lapor)->format('d M Y H:i') }}</td>
                    <td>{{ $item->petugas->nama_lengkap }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($item->isi_laporan, 50) }}</td>
                    <td><a href="{{ route('laporan.detail', $item->id_lapor) }}" class="btn btn-sm btn-info">Detail</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection