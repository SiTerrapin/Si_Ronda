@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Jadwal Ronda</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('plot.schedule') }}">
        @csrf
        <button type="submit" class="btn btn-primary mb-3">Jadwalkan Otomatis</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Hari</th>
                <th>Nama Petugas</th>
                <th>Leader</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jadwal as $item)
                <tr>
                    <td>{{ $item->nama_hari }}</td>
                    <td>{{ $item->petugas->nama_lengkap }}</td>
                    <td>{{ $item->is_leader === '1' ? '✔️' : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection