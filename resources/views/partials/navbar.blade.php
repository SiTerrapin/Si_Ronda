<nav class="navbar navbar-expand-lg navbar-dark shadow-sm sticky-top">
    <div class="container-xxl d-flex justify-content-between align-items-center">
        <a class="navbar-brand ms-1 d-flex align-items-center gap-2 fw-bold" href="{{ Auth::guard('petugas')->check() ? route('dashboard') : route('beranda') }}">
            <img src="{{ asset('assets/icon/LogoNav_sironda.png') }}" style="height: 32px; max-width: 120px; object-fit: contain;" class="img-fluid" alt="Logo SiRonda">
        </a>

        {{-- Menu Desktop (>= 992px) --}}
        <div class="d-none d-lg-flex align-items-center gap-3">
            <ul class="navbar-nav nav-underline flex-row align-items-center gap-3">
                @auth('petugas')
                    @php $userNav = Auth::guard('petugas')->user(); @endphp
                    <li class="nav-item"><a class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link {{ Request::is('petugas*') ? 'active' : '' }}" href="{{ route('petugas.index') }}">Petugas</a></li>
                    <li class="nav-item"><a class="nav-link {{ Request::is('plot*') ? 'active' : '' }}" href="{{ route('plot.index') }}">Jadwal</a></li>
                    <li class="nav-item"><a class="nav-link {{ Request::is('laporan*') ? 'active' : '' }}" href="{{ route('laporan.index') }}">Laporan</a></li>
                    @if($userNav->isAdmin())
                        <li class="nav-item"><a class="nav-link {{ Request::is('rekap*') ? 'active' : '' }}" href="{{ route('rekap.index') }}">Rekap</a></li>
                    @endif
                    @if($userNav->isSuperAdmin())
                        <li class="nav-item"><a class="nav-link {{ Request::is('portal-settings*') ? 'active' : '' }}" href="{{ route('portal.settings') }}">Portal UI</a></li>
                    @endif
                    <li class="nav-item"><a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="{{ route('beranda') }}">Portal Warga</a></li>
                @else
                    <li class="nav-item"><a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="{{ route('beranda') }}">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link {{ Request::is('jadwal') ? 'active' : '' }}" href="{{ route('public.jadwal') }}">Jadwal Ronda</a></li>
                    <li class="nav-item"><a class="nav-link {{ Request::is('anggota') ? 'active' : '' }}" href="{{ route('public.anggota') }}">Anggota</a></li>
                    <li class="nav-item"><a class="nav-link {{ Request::is('kegiatan') ? 'active' : '' }}" href="{{ route('public.kegiatan') }}">Kegiatan</a></li>
                @endauth
            </ul>
        </div>

        {{-- Kanan Atas (Tampil di Desktop & Mobile: Dark Mode Toggle + Profil/Login/Logout) --}}
        <div class="d-flex align-items-center gap-2">
            <button type="button" onclick="toggleSirondaTheme()" class="btn btn-sm btn-outline-light" title="Ganti Mode Gelap/Terang">
                <span class="theme-toggle-icon">🌙</span>
            </button>

            @auth('petugas')
                @php $userNav = Auth::guard('petugas')->user(); @endphp
                <a href="{{ route('profile.index') }}" class="btn btn-sm btn-outline-info text-decoration-none d-flex align-items-center gap-1" title="Profil Akun">
                    <span class="d-none d-sm-inline">{{ $userNav->nama_lengkap }}</span>
                    <span class="badge bg-info text-dark">{{ strtoupper($userNav->role) }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-sm btn-success px-3 fw-semibold">Login</a>
            @endauth
        </div>
    </div>
</nav>
