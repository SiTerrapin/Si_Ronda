@extends('layouts.app')
@section('title', 'Registrasi Petugas')

@section('content')
<div class="container mt-5">
    <h3 class="mb-4">Registrasi Petugas</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <input type="text" name="username" class="form-control mb-3" placeholder="Username" value="{{ old('username') }}" required>

        <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
        <input type="password" name="password_confirmation" class="form-control mb-3" placeholder="Konfirmasi Password" required>

        <input type="text" name="nama_lengkap" class="form-control mb-3" placeholder="Nama Lengkap" value="{{ old('nama_lengkap') }}" required>

        <select name="gender" class="form-control mb-3" required>
            <option value="">Pilih Gender</option>
            <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Laki-laki</option>
            <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Perempuan</option>
        </select>

        <button type="submit" class="btn btn-success w-100">Daftar</button>
    </form>

    <div class="mt-3 text-center">
        <a href="{{ route('login') }}">Sudah punya akun?</a>
    </div>
</div>
@endsection