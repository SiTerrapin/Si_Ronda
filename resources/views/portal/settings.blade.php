@extends('layouts.app')
@section('title', 'Pengaturan Gambar Portal Warga - SiRonda')

@section('content')
<div class="container" style="max-width: 820px;">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h3 class="mb-0 fw-bold">🎨 Pengaturan Tampilan & Gambar Portal Warga</h3>
            <small class="text-muted">Khusus Super Admin — Kelola gambar banner beranda dan ilustrasi halaman kegiatan</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('beranda') }}" class="btn btn-outline-primary btn-sm">Lihat Portal Warga</a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="modern-card p-4 mb-4">
        <form method="POST" action="{{ route('portal.settings.update') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold small">Judul Hero Banner</label>
                <input type="text" name="hero_title" class="form-control @error('hero_title') is-invalid @enderror" value="{{ old('hero_title', $settings['hero_title']) }}">
                @error('hero_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold small">Subjudul Hero Banner</label>
                <textarea name="hero_subtitle" rows="2" class="form-control @error('hero_subtitle') is-invalid @enderror">{{ old('hero_subtitle', $settings['hero_subtitle']) }}</textarea>
                @error('hero_subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Gambar Hero Background (Beranda)</label>
                    <div class="mb-2">
                        <img src="{{ asset($settings['hero_background']) }}" alt="Preview Hero Background" class="img-fluid rounded border w-100" style="height: 150px; object-fit: cover;">
                    </div>
                    <input type="file" name="hero_background" class="form-control @error('hero_background') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">JPG/PNG/WEBP, maksimal 2 MB.</small>
                    @error('hero_background')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small">Gambar Ilustrasi Kegiatan Ronda</label>
                    <div class="mb-2">
                        <img src="{{ asset($settings['kegiatan_image']) }}" alt="Preview Gambar Kegiatan" class="img-fluid rounded border w-100" style="height: 150px; object-fit: cover;">
                    </div>
                    <input type="file" name="kegiatan_image" class="form-control @error('kegiatan_image') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">JPG/PNG/WEBP, maksimal 2 MB.</small>
                    @error('kegiatan_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary px-4 fw-semibold">Simpan Pengaturan Portal</button>
            </div>
        </form>

        <form method="POST" action="{{ route('portal.settings.reset') }}" class="mt-3" onsubmit="return confirm('Kembalikan semua gambar & teks Portal Warga ke default?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">↺ Reset ke Gambar Default</button>
        </form>
    </div>
</div>
@endsection
