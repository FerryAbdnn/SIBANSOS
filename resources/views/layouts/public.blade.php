
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Beranda') - SIBANSOS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/landing-public.css') }}">
    <link rel="stylesheet" href="{{ asset('css/brand-logo.css') }}">

    @stack('styles')
</head>

<body class="public-body">

    <div class="public-announcement">
        Pendaftaran mandiri dibuka kembali!
        <a href="{{ route('register') }}">
            Cek kelayakan Anda sekarang
        </a>
    </div>

    <header class="public-navbar">

        @include('components.brand-logo', [
            'href' => route('landing'),
            'class' => 'public-brand'
        ])

        <nav class="public-nav">

            <a href="{{ route('landing') }}">Beranda</a>
            <a href="#program">Program</a>
            <a href="#cek-status">Cek Status</a>
            <a href="#alur">Alur Pengajuan</a>
            <a href="#kontak">Kontak</a>

        </nav>

        <div class="public-nav-actions">

            <a
                href="{{ route('login') }}"
                class="public-button public-button-outline"
            >
                Masuk
            </a>

            <a
                href="{{ route('register') }}"
                class="public-button public-button-primary"
            >
                Ajukan Bantuan
            </a>

        </div>

    </header>

    <main class="public-main">
        @yield('content')
    </main>

    @stack('scripts')

</body>

</html>
