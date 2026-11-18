@extends('layouts.app')
@section('title', 'Jadwal Ronda - SiRonda')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h3 class="mb-0 fw-bold">📅 Manajemen Jadwal Ronda</h3>
        <small class="text-muted">Kartu segmen mingguan, presensi real-time, auto-rotate siklus, dan pengingat WhatsApp</small>
    </div>
    <div class="d-flex flex-wrap gap-2 no-print">
        <button onclick="window.print()" class="btn btn-outline-dark">🖨️ Cetak</button>
        @if(Auth::guard('petugas')->user()->isAdmin())
            <a href="{{ route('plot.create') }}" class="btn btn-success">+ Tambah Jadwal Manual</a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- Panel Kontrol Admin / Super Admin untuk Auto-Rotate Mingguan --}}
@if(Auth::guard('petugas')->user()->isAdmin())
    <div class="filter-panel mb-3 no-print">
        <div class="row g-3 align-items-center justify-content-between">
            <div class="col-lg-8">
                <form method="POST" action="{{ route('plot.autoConfig') }}" class="d-flex flex-wrap align-items-center gap-3">
                    @csrf
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="autoRotateSwitch" name="auto_rotate_enabled" value="1" {{ !empty($autoConfig['auto_rotate_enabled']) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold small" for="autoRotateSwitch">Auto-Rotate Mingguan</label>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <label class="small fw-semibold text-nowrap mb-0">Reset & Acak Otomatis Setelah Ronda Hari:</label>
                        <select name="reset_after_day" class="form-select form-select-sm" style="width: auto;">
                            @foreach($hariList as $hari)
                                <option value="{{ $hari }}" {{ ($autoConfig['reset_after_day'] ?? 'Sabtu') === $hari ? 'selected' : '' }}>{{ $hari }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-sm btn-outline-primary fw-semibold">Simpan Pengaturan</button>
                </form>
            </div>

            <div class="col-lg-4 text-lg-end">
                <form method="POST" action="{{ route('plot.schedule') }}" class="d-inline" onsubmit="return confirm('Acak jadwal sekarang akan menyusun ulang seluruh jadwal minggu ini. Lanjutkan?')">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">⚡ Acak Jadwal Sekarang</button>
                </form>
            </div>
        </div>
        @if(!empty($autoConfig['last_rotated_cycle']))
            <div class="small text-muted mt-2">
                Siklus rotasi otomatis terakhir: <strong>{{ $autoConfig['last_rotated_cycle'] }}</strong>
            </div>
        @endif
    </div>
@endif

{{-- Filter Hari --}}
<div class="filter-panel mb-4 no-print">
    <form method="GET" action="{{ route('plot.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
        <select name="hari" class="form-select" style="width: auto; min-width: 220px;">
            <option value="">Semua Hari (Senin – Minggu)</option>
            @foreach($hariList as $hari)
                <option value="{{ $hari }}" {{ $filterHari === $hari ? 'selected' : '' }}>{{ $hari }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('plot.index') }}" class="btn btn-outline-secondary">Reset</a>
    </form>
</div>

@php
    $currentUser = Auth::guard('petugas')->user();
    $nowJakarta = \Carbon\Carbon::now('Asia/Jakarta');
    $grouped = $jadwal->groupBy('nama_hari');
    $hariTampil = $filterHari ? [$filterHari] : $hariList;
@endphp

{{-- 100% Tampilan 7 Kartu Segmen Hari (Day Cards) --}}
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
                        $pesanWa = "Halo {$item->petugas->nama_lengkap}, pengingat jadwal ronda Anda pada hari {$item->nama_hari} di {$item->pos_jaga} ({$item->shift}). Mohon kehadirannya tepat waktu. Terima kasih - SiRonda.";
                        $waLink = $item->petugas ? $item->petugas->waUrl($pesanWa) : null;

                        $isOwner = ($item->petugas_id === $currentUser->id_petugas);
                        $canAttend = $currentUser->isAdmin() || $item->canAttendNow($nowJakarta);
                        $canLeave = $currentUser->isAdmin() || $item->canRequestLeaveNow($nowJakarta);
                        $reason = $item->presensiStatusReason($nowJakarta) ?? 'Di luar waktu presensi';
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

                        {{-- Tombol Aksi Cepat Compact --}}
                        <div class="d-flex flex-wrap align-items-center gap-1 mt-2 no-print">
                            @if($currentUser->isAdmin() || $isOwner)
                                @if($canAttend)
                                    <form method="POST" action="{{ route('plot.presensi', $item->id_plot) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status_kehadiran" value="Hadir">
                                        <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2" title="Tandai Hadir">✓ Hadir</button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" disabled title="{{ $reason }}">✓ Hadir</button>
                                @endif

                                @if($canLeave)
                                    <form method="POST" action="{{ route('plot.presensi', $item->id_plot) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status_kehadiran" value="Izin">
                                        <button type="submit" class="btn btn-sm btn-outline-warning py-0 px-2" title="Ajukan Izin">Izin</button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" disabled title="{{ $reason }}">Izin</button>
                                @endif
                            @endif

                            @if($currentUser->isAdmin())
                                @if($waLink)
                                    <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-success py-0 px-2" title="Kirim Pengingat WA">WA</a>
                                @endif
                                <a href="{{ route('plot.edit', $item->id_plot) }}" class="btn btn-sm btn-outline-primary py-0 px-2">Edit</a>
                                <form method="POST" action="{{ route('plot.destroy', $item->id_plot) }}" onsubmit="return confirm('Hapus slot jadwal ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">Hapus</button>
                                </form>
                            @endif
                        </div>

                        @if($isOwner && !$currentUser->isAdmin() && !$canAttend)
                            <div class="small text-muted mt-1" style="font-size: 0.72rem;">⏳ {{ $reason }}</div>
                        @endif
                    </div>
                @empty
                    <div class="p-4 text-center text-muted small">Belum ada jadwal pada hari {{ $hari }}.</div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>
@endsection
