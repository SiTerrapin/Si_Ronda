
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('beranda') }}">Pos Ronda</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarNav" aria-controls="navbarNav" 
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('beranda') }}">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('jadwal') }}">Jadwal Ronda</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('anggota') }}">Anggota</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('kegiatan') }}">Kegiatan</a></li>
            </ul>
        </div>
        <div>
            <form action="{{route('logout.dat')}}" method="post">
                @csrf
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
        </div>
    </div>
</nav>