@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Rekap Laporan Petugas</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Petugas</th>
                <th>Total Laporan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekap as $item)
                <tr>
                    <td>{{ $item->nama_lengkap }}</td>
                    <td>{{ $item->total_laporan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection