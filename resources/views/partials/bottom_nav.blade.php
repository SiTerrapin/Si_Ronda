<nav class="mobile-bottom-nav d-lg-none no-print" aria-label="Navigasi Bawah Mobile">
    @auth('petugas')
        @php $userBottom = Auth::guard('petugas')->user(); @endphp
        <a href="{{ route('dashboard') }}" class="mobile-bottom-item {{ Request::is('dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('plot.index') }}" class="mobile-bottom-item {{ Request::is('plot*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            <span>Jadwal</span>
        </a>
        <a href="{{ route('laporan.index') }}" class="mobile-bottom-item {{ Request::is('laporan*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg>
            <span>Laporan</span>
        </a>
        @if($userBottom->isAdmin())
            <a href="{{ route('rekap.index') }}" class="mobile-bottom-item {{ Request::is('rekap*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
                <span>Rekap</span>
            </a>
        @else
            <a href="{{ route('petugas.index') }}" class="mobile-bottom-item {{ Request::is('petugas*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span>Petugas</span>
            </a>
        @endif
        <a href="{{ route('profile.index') }}" class="mobile-bottom-item {{ Request::is('profile*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>Profil</span>
        </a>
    @else
        <a href="{{ route('beranda') }}" class="mobile-bottom-item {{ Request::is('/') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/></svg>
            <span>Beranda</span>
        </a>
        <a href="{{ route('public.jadwal') }}" class="mobile-bottom-item {{ Request::is('jadwal') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            <span>Jadwal</span>
        </a>
        <a href="{{ route('public.anggota') }}" class="mobile-bottom-item {{ Request::is('anggota') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <span>Anggota</span>
        </a>
        <a href="{{ route('public.kegiatan') }}" class="mobile-bottom-item {{ Request::is('kegiatan') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M7 10h6M7 14h10"/></svg>
            <span>Kegiatan</span>
        </a>
        <a href="{{ route('login') }}" class="mobile-bottom-item {{ Request::is('login') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
            <span>Masuk</span>
        </a>
    @endauth
</nav>
