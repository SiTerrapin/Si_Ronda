<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background-color: #f8f9fa;">

    {{-- Navbar --}}
    @include('partials.navbar')

    {{-- Konten Halaman --}}
    <div class="container mt-4">
        @yield('content')
    </div>

    {{-- Footer --}}
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p>&copy; 2025 Pos Ronda Kampung Aman. Dibuat dengan semangat gotong royong.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>