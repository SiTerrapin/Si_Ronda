<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Sironda')</title>
    {{-- Vite CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset("assets/CSS/style.css")}}">
</head>
<body class="d-flex flex-column min-vh-100">
    @if (!in_array(Request::path(), ['login', 'register']))
        @include('partials.navbar')
    @endif

    <div class="container-xxl mt-4 text-light flex-grow-1">
        @yield('content')
    </div>

    @include('partials.footer')
</body>
</html>
