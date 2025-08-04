@extends('layouts.app')
@section('content')
@php
    if (!session()->has('nama')) {
        return redirect('/login')->withErrors(['email' => 'Silakan login dulu.']);
    }
@endphp
<!-- Hero Section -->
<header class="bg-primary text-white text-center py-5">
    <h1>Selamat Datang di Portal Ronda Malam</h1>
    <p class="lead">Bersama kita jaga kampung, eratkan silaturahmi.</p>
</header>

<!-- Konten Utama -->
<div class="container my-5">
    <div class="row text-center">
        <div class="col-md-4">
            <img src="https://via.placeholder.com/150" class="rounded-circle mb-3" alt="Petugas Ronda">
            <h3>Petugas Minggu Ini</h3>
            <p>Pak RT, Pak Budi, dan Mas Iwan bertugas menjaga keamanan lingkungan.</p>
        </div>
        <div class="col-md-4">
            <img src="https://via.placeholder.com/150" class="rounded-circle mb-3" alt="Pos Ronda">
            <h3>Pos Ronda</h3>
            <p>Pos kami selalu ramai dengan cerita, kopi hangat, dan tawa warga setiap malam.</p>
        </div>
        <div class="col-md-4">
            <img src="https://via.placeholder.com/150" class="rounded-circle mb-3" alt="Agenda">
            <h3>Agenda Kampung</h3>
            <p>Gotong royong, ronda, dan acara bulanan menjaga solidaritas di lingkungan kita.</p>
        </div>
    </div>
</div>
@endsection
