@extends('layouts.app')
@section('title', 'Data Petugas')

@section('content')
<div class="container mt-4 ">
    <h4 class="mb-3">Daftar Petugas Aktif</h4>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Nama Lengkap</th>
                <th>Username</th>
                <th>Gender</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($petugasAktif as $p)
                <tr>
                    <td>{{ $p->nama_lengkap }}</td>
                    <td>{{ $p->username }}</td>
                    <td>{{ $p->gender == 'M' ? 'Laki-laki' : 'Perempuan' }}</td>
                    <td><span class="badge bg-success">Aktif</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Belum ada petugas aktif.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection