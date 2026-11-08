<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-xxl">
        <a class="navbar-brand ms-3" href="/dashboard">
            <img src="{{ asset("assets/icon/LogoNav_sironda.png") }}" style="height: 32px; max-width: 120px; object-fit: contain;" class="img-fluid" alt="Logo SiRonda">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarContent">
            <ul class="navbar-nav nav-underline">
                <li class="nav-item me-4"><a class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}" href="/dashboard">Beranda</a></li>
                <li class="nav-item me-4"><a class="nav-link {{ Request::is('petugas') ? 'active' : '' }}" href="/petugas">Petugas</a></li>
                <li class="nav-item me-4"><a class="nav-link {{ Request::is('plot') ? 'active' : '' }}" href="/plot">Jadwal</a></li>
                <li class="nav-item me-4"><a class="nav-link {{ Request::is('laporan') ? 'active' : '' }}" href="/laporan">Laporan</a></li>
                <li class="nav-item me-4"><a class="nav-link {{ Request::is('rekap') ? 'active' : '' }}" href="/rekap">Rekap</a></li>

                @auth
                <li class="nav-item">
                    <span class="nav-link"><a href="#">👮 {{ Auth::user()->nama }}</a></span>
                </li>
                <li class="nav-item">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-light ms-4 mt-1">Logout</button>
                    </form>
                </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>