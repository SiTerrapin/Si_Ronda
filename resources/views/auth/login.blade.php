@extends('layouts.app')
@section('title', 'Login Petugas')

@section('content')
<div class="container mt-5">
    <h3 class="mb-4">Login Petugas</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <input type="text" name="username" class="form-control mb-3" placeholder="Username" value="{{ old('username') }}" required>
        <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="mt-3 text-center">
        <a href="{{ route('register') }}">Belum punya akun?</a>
    </div>
</div>
@endsection