<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'SIBANSOS') - Sistem Bantuan Sosial</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>

<body class="public-body">

    <header class="public-navbar">
        <a href="{{ route('landing') }}" class="brand">
            <span class="brand-mark">S</span>
            <span>
                <b>SIBANSOS</b>
                <small>Sistem Pendataan Bantuan Sosial</small>
            </span>
        </a>

        <nav>
            <a href="{{ route('landing') }}">Beranda</a>
            <a href="#tentang">Tentang</a>
            <a href="{{ route('login') }}" class="btn outline">Masuk</a>
            <a href="{{ route('register') }}" class="btn primary">Daftar</a>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="public-footer">
        © {{ date('Y') }} SIBANSOS · Mendukung SDG 1 Tanpa Kemiskinan
    </footer>

    @stack('scripts')
</body>

</html>