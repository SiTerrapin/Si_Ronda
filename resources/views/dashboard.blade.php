@extends('layouts.app')
@section('title', 'Dashboard - SiRonda')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h3 class="mb-1 fw-bold">{{ $sapaan }}, {{ $petugas->nama_lengkap }}!</h3>
        <div class="text-muted small">
            Hari ini: <strong class="text-dark">{{ $hariIni }}</strong>
            <span class="mx-1">•</span>
            Peran: <span class="badge bg-primary">{{ strtoupper($petugas->role) }}</span>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- 4 Summary Cards dengan Left Border Accent --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card accent-info p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Petugas Aktif</span>
                    <div class="stat-value mt-1">{{ $jumlahPetugas }}</div>
                    <small class="text-muted">Anggota siap bertugas</small>
                </div>
                <span class="stat-icon bg-info bg-opacity-10 text-info">👥</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card accent-blue p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Jaga Malam Ini ({{ $hariIni }})</span>
                    <div class="stat-value mt-1">{{ $jumlahJadwalMalamIni }}</div>
                    <small class="text-muted">Petugas terjadwal hari ini</small>
                </div>
                <span class="stat-icon bg-primary bg-opacity-10 text-primary">🛡️</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card accent-emerald p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Laporan Bulan Ini</span>
                    <div class="stat-value mt-1">{{ $jumlahLaporanBulanIni }}</div>
                    <small class="text-muted">Total laporan patroli masuk</small>
                </div>
                <span class="stat-icon bg-success bg-opacity-10 text-success">📝</span>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="stat-card accent-amber p-3 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Persentase Kehadiran</span>
                    <div class="stat-value mt-1">{{ $persentaseKehadiran }}%</div>
                    <small class="text-muted">Akumulasi kehadiran ronda</small>
                </div>
                <span class="stat-icon bg-warning bg-opacity-10 text-warning">📊</span>
            </div>
        </div>
    </div>
</div>

{{-- Aktivitas Pribadi & Navigasi Cepat --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="modern-card p-4 h-100">
            <h5 class="fw-bold mb-3">🕒 Aktivitas & Jadwal Jaga Anda</h5>
            <div class="d-flex gap-4 mb-2">
                <div><span class="text-muted small">Jadwal Hari Ini:</span> <strong>{{ $jadwalHariIni }} kali</strong></div>
                <div><span class="text-muted small">Laporan Hari Ini:</span> <strong>{{ $laporanHariIni }} laporan</strong></div>
            </div>

            @if($laporanHariIni == 0)
                <div class="alert alert-danger py-2 mb-3">⚠️ Anda belum mengirim laporan hari ini.</div>
            @endif

            @if($jadwalSaya->count() > 0)
                @php $nowDash = \Carbon\Carbon::now('Asia/Jakarta'); @endphp
                <h6 class="text-primary fw-semibold mt-3 mb-2">Daftar Jadwal Jaga Anda:</h6>
                <div class="list-group list-group-flush">
                    @foreach($jadwalSaya as $j)
                        @php
                            $canAttendDash = $petugas->isAdmin() || $j->canAttendNow($nowDash);
                            $canLeaveDash = $petugas->isAdmin() || $j->canRequestLeaveNow($nowDash);
                            $reasonDash = $j->presensiStatusReason($nowDash) ?? 'Di luar waktu presensi';
                        @endphp
                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <strong>{{ $j->nama_hari }}</strong> — {{ $j->pos_jaga }}
                                <div class="small text-muted">{{ $j->shift }}</div>
                                <span class="badge mt-1 bg-{{ $j->status_kehadiran === 'Hadir' ? 'success' : ($j->status_kehadiran === 'Izin' ? 'warning text-dark' : ($j->status_kehadiran === 'Alfa' ? 'danger' : 'secondary')) }}">
                                    {{ $j->status_kehadiran }}
                                </span>
                                @if(!$petugas->isAdmin() && !$canAttendDash)
                                    <div class="small text-muted mt-1" style="font-size: 0.72rem;">⏳ {{ $reasonDash }}</div>
                                @endif
                            </div>
                            <div class="d-flex gap-1">
                                @if($canAttendDash)
                                    <form method="POST" action="{{ route('plot.presensi', $j->id_plot) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status_kehadiran" value="Hadir">
                                        <button type="submit" class="btn btn-sm btn-success">Hadir</button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="{{ $reasonDash }}">Hadir</button>
                                @endif
                                @if($canLeaveDash)
                                    <form method="POST" action="{{ route('plot.presensi', $j->id_plot) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status_kehadiran" value="Izin">
                                        <button type="submit" class="btn btn-sm btn-warning">Izin</button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="{{ $reasonDash }}">Izin</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted small mb-0 mt-2">Anda belum memiliki jadwal jaga yang diplot.</p>
            @endif
        </div>
    </div>

    <div class="col-md-6">
        <div class="modern-card p-4 h-100">
            <h5 class="fw-bold mb-3">⚡ Navigasi Cepat</h5>
            <div class="d-grid gap-2">
                <a href="{{ route('plot.index') }}" class="btn btn-outline-primary text-start">📅 Lihat Jadwal Ronda Mingguan</a>
                <a href="{{ route('laporan.index') }}" class="btn btn-outline-success text-start">📝 Buat / Lihat Laporan Patroli</a>
                <a href="{{ route('petugas.index') }}" class="btn btn-outline-dark text-start">👥 Daftar Petugas Ronda</a>
                @if($petugas->isAdmin())
                    <a href="{{ route('rekap.index') }}" class="btn btn-outline-info text-start">📊 Kelola & Cetak Rekap Harian</a>
                @endif
                <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary text-start">👤 Profil & Pengaturan Akun</a>
                @if($petugas->isSuperAdmin())
                    <a href="{{ route('portal.settings') }}" class="btn btn-warning text-start fw-semibold">🎨 Ubah Gambar Portal Warga</a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Quick View Berdampingan (col-lg-6) --}}
<div class="row g-3">
    <div class="col-lg-6">
        <div class="modern-card overflow-hidden h-100">
            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold">🛡️ Jadwal Ronda Malam Ini ({{ $hariIni }})</span>
                <a href="{{ route('plot.index') }}" class="btn btn-sm btn-outline-primary">Selengkapnya</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Petugas</th>
                            <th>Pos</th>
                            <th>Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwalMalamIniList as $item)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $item->petugas->nama_lengkap ?? '-' }}</span>
                                    @if($item->is_leader === '1')
                                        <span class="badge bg-warning text-dark ms-1">Leader</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-primary bg-opacity-10 text-primary">{{ $item->pos_jaga }}</span></td>
                                <td><span class="badge bg-{{ $item->status_kehadiran === 'Hadir' ? 'success' : ($item->status_kehadiran === 'Izin' ? 'warning text-dark' : ($item->status_kehadiran === 'Alfa' ? 'danger' : 'secondary')) }}">{{ $item->status_kehadiran }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">Belum ada petugas terjadwal malam ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="modern-card overflow-hidden h-100">
            <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold">📝 5 Laporan Kejadian Terbaru</span>
                <a href="{{ route('laporan.index') }}" class="btn btn-sm btn-outline-primary">Selengkapnya</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>Petugas</th>
                            <th>Situasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporanTerbaru as $lap)
                            <tr>
                                <td class="small">{{ \Carbon\Carbon::parse($lap->tanggal_lapor)->format('d/m H:i') }}</td>
                                <td class="fw-semibold">{{ $lap->petugas->nama_lengkap ?? '-' }}</td>
                                <td><span class="badge bg-{{ $lap->kategori_situasi === 'Aman' ? 'success' : ($lap->kategori_situasi === 'Mencurigakan' ? 'warning text-dark' : 'danger') }}">{{ $lap->kategori_situasi }}</span></td>
                                <td><span class="badge bg-{{ $lap->status_verifikasi === 'Diverifikasi' ? 'info text-dark' : 'secondary' }}">{{ $lap->status_verifikasi }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada laporan masuk.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
