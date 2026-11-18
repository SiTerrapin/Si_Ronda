<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SiRonda - Sistem Informasi Ronda Terpadu')</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/icon/favicon.png') }}">
    <script>
        (function () {
            var saved = localStorage.getItem('sironda_theme');
            if (saved === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    {{-- Vite CSS & JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    @if (!in_array(Request::path(), ['login', 'register']))
        @include('partials.navbar')
    @endif

    <main class="container-xxl mt-4 flex-grow-1 pb-5">
        @yield('content')
    </main>

    @include('partials.footer')

    @if (!in_array(Request::path(), ['login', 'register']))
        @include('partials.bottom_nav')
    @endif

    <script>
        function toggleSirondaTheme() {
            var root = document.documentElement;
            var isDark = root.getAttribute('data-theme') === 'dark';
            var next = isDark ? 'light' : 'dark';
            if (next === 'dark') {
                root.setAttribute('data-theme', 'dark');
            } else {
                root.removeAttribute('data-theme');
            }
            localStorage.setItem('sironda_theme', next);
            updateThemeIcons(next);
        }

        function updateThemeIcons(theme) {
            var icons = document.querySelectorAll('.theme-toggle-icon');
            icons.forEach(function (el) {
                el.textContent = theme === 'dark' ? '☀️' : '🌙';
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
            updateThemeIcons(current);
        });
    </script>
</body>
</html>
