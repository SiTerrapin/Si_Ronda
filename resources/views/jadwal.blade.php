@extends('layouts.app')
@section('title', 'Jadwal Ronda Mingguan - SiRonda')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0 fw-bold">📅 Jadwal Ronda Mingguan</h3>
        <small class="text-muted">Kartu segmen per hari (Senin – Minggu) untuk seluruh warga</small>
    </div>
    <button onclick="window.print()" class="btn btn-outline-dark no-print">🖨️ Cetak Jadwal</button>
</div>

<div class="filter-panel mb-4 no-print">
    <form method="GET" action="{{ route('public.jadwal') }}" class="row g-2 align-items-center">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control" placeholder="Cari nama petugas, pos jaga, atau RT/Blok..." value="{{ $search }}">
        </div>
        <div class="col-md-4">
            <select name="hari" class="form-select">
                <option value="">Semua Hari (Senin – Minggu)</option>
                @foreach($hariList as $hari)
                    <option value="{{ $hari }}" {{ $filterHari === $hari ? 'selected' : '' }}>{{ $hari }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
            <a href="{{ route('public.jadwal') }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </form>
</div>

@php
    $grouped = $jadwal->groupBy('nama_hari');
    $hariTampil = $filterHari ? [$filterHari] : $hariList;
@endphp

<div class="schedule-grid">
    @foreach($hariTampil as $hari)
        @php
            $items = $grouped->get($hari, collect());
            $isToday = ($hari === $hariIni);
            $headerShift = $items->first()?->shift ?? 'Malam (22:00 - 04:00)';
            $headerTanggal = $items->first()?->tanggal_ronda ? $items->first()->tanggal_ronda->format('d M Y') : null;
        @endphp
        <div class="day-card {{ $isToday ? 'is-today' : '' }}">
            <div class="day-card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold fs-6">{{ $hari }}</span>
                        @if($isToday)
                            <span class="badge bg-light text-success fw-bold badge-pulse">Malam Ini</span>
                        @endif
                    </div>
                    <span class="badge bg-white bg-opacity-25 text-white">{{ $items->count() }} Petugas</span>
                </div>
                <div class="small opacity-75 mt-1 d-flex justify-content-between align-items-center">
                    <span>🕒 {{ $headerShift }}</span>
                    @if($headerTanggal)
                        <span>{{ $headerTanggal }}</span>
                    @endif
                </div>
            </div>

            <div class="flex-grow-1">
                @forelse($items as $item)
                    @php
                        $badgeKehadiran = $item->status_kehadiran === 'Hadir' ? 'success' : ($item->status_kehadiran === 'Izin' ? 'warning text-dark' : ($item->status_kehadiran === 'Alfa' ? 'danger' : 'secondary'));
                    @endphp
                    <div class="day-slot-item">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="fw-semibold">
                                    {{ $item->petugas->nama_lengkap ?? '-' }}
                                    @if($item->is_leader === '1')
                                        <span class="badge bg-warning text-dark ms-1">Komandan</span>
                                    @endif
                                </div>
                                @if($item->petugas && $item->petugas->alamat)
                                    <small class="text-muted">{{ $item->petugas->alamat }}</small>
                                @endif
                            </div>
                            <span class="badge bg-{{ $badgeKehadiran }}">{{ $item->status_kehadiran }}</span>
                        </div>
                        <div class="mt-2">
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary">{{ $item->pos_jaga }}</span>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-muted small">Belum ada petugas terjadwal pada hari {{ $hari }}.</div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
@endsection
