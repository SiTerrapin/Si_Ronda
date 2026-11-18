@extends('layouts.app')
@section('title', 'Laporan Patroli - SiRonda')

@section('content')
<div class="mb-3">
    <h3 class="mb-0 fw-bold">📝 Laporan Patroli & Kejadian Malam</h3>
    <small class="text-muted">Catat kondisi keamanan saat bertugas dan pantau status verifikasi Admin</small>
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

{{-- Segmen Form Kirim Laporan --}}
<div class="filter-panel mb-4">
    <h6 class="fw-bold mb-3">➕ Kirim Laporan Kejadian / Patroli Baru</h6>
    <form method="POST" action="{{ route('laporan.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold small">Kategori Situasi</label>
                <select name="kategori_situasi" class="form-select @error('kategori_situasi') is-invalid @enderror">
                    @foreach(['Aman', 'Mencurigakan', 'Darurat/Bencana', 'Kriminalitas'] as $kat)
                        <option value="{{ $kat }}" {{ old('kategori_situasi', 'Aman') === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
                @error('kategori_situasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold small">Terkait Jadwal <span class="text-muted">(opsional)</span></label>
                <select name="plot_id" class="form-select @error('plot_id') is-invalid @enderror">
                    <option value="">-- Umum / Tidak terkait slot --</option>
                    @foreach($jadwalSaya as $j)
                        <option value="{{ $j->id_plot }}" {{ old('plot_id') == $j->id_plot ? 'selected' : '' }}>
                            {{ $j->nama_hari }} ({{ $j->pos_jaga }})
                        </option>
                    @endforeach
                </select>
                @error('plot_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-semibold small">Foto Bukti <span class="text-muted">(opsional, maks 2MB)</span></label>
                <input type="file" name="foto_bukti" class="form-control @error('foto_bukti') is-invalid @enderror" accept="image/*">
                @error('foto_bukti')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="isi_laporan" class="form-label fw-semibold small">Deskripsi Kejadian / Kondisi Lingkungan</label>
            <textarea id="isi_laporan" name="isi_laporan" class="form-control @error('isi_laporan') is-invalid @enderror" rows="3" placeholder="Tuliskan kondisi patroli, jam kejadian, atau hal mencurigakan..." required>{{ old('isi_laporan') }}</textarea>
            @error('isi_laporan')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary px-4">Kirim Laporan</button>
    </form>
</div>

{{-- Segmen Filter Laporan --}}
<div class="filter-panel mb-3">
    <form method="GET" action="{{ route('laporan.index') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
            <select name="kategori" class="form-select">
                <option value="">Semua Kategori Situasi</option>
                @foreach(['Aman', 'Mencurigakan', 'Darurat/Bencana', 'Kriminalitas'] as $kat)
                    <option value="{{ $kat }}" {{ $kategori === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select">
                <option value="">Semua Status Verifikasi</option>
                <option value="Pending" {{ $status === 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Diverifikasi" {{ $status === 'Diverifikasi' ? 'selected' : '' }}>Diverifikasi</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
            <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

{{-- Daftar Laporan --}}
<div class="modern-card overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover table-bordered mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Tanggal & Jam</th>
                    <th>Petugas</th>
                    <th>Kategori</th>
                    <th>Isi Laporan</th>
                    <th>Verifikasi</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($laporan as $item)
                    <tr>
                        <td class="small">{{ \Carbon\Carbon::parse($item->tanggal_lapor)->format('d M Y H:i') }}</td>
                        <td class="fw-semibold">{{ $item->petugas->nama_lengkap ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $item->kategori_situasi === 'Aman' ? 'success' : ($item->kategori_situasi === 'Mencurigakan' ? 'warning text-dark' : 'danger') }}">
                                {{ $item->kategori_situasi }}
                            </span>
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($item->isi_laporan, 60) }}</td>
                        <td>
                            <span class="badge bg-{{ $item->status_verifikasi === 'Diverifikasi' ? 'success' : 'warning text-dark' }}">
                                {{ $item->status_verifikasi === 'Diverifikasi' ? '✔️ Diverifikasi' : '⏳ Pending' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('laporan.detail', $item->id_lapor) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                                @if(Auth::guard('petugas')->user()->isAdmin())
                                    <form method="POST" action="{{ route('laporan.verify', $item->id_lapor) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status_verifikasi" value="{{ $item->status_verifikasi === 'Diverifikasi' ? 'Pending' : 'Diverifikasi' }}">
                                        <button type="submit" class="btn btn-sm btn-{{ $item->status_verifikasi === 'Diverifikasi' ? 'outline-secondary' : 'success' }}">
                                            {{ $item->status_verifikasi === 'Diverifikasi' ? 'Batalkan' : 'Verifikasi' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada laporan patroli yang sesuai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
