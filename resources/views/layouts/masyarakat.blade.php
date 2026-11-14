<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Portal Masyarakat') - SIBANSOS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="stylesheet" href="{{ asset('css/design.css') }}">
    @stack('styles')
</head>

<body>

    <div class="app-shell">
        @include('components.sidebar-masyarakat')

        <main class="main-content">
            @include('components.navbar')

            <section class="page-content">

                @include('components.alert')

                @yield('content')

            </section>

            <footer class="app-footer">
                <span>&copy; {{ date('Y') }} SIBANSOS &middot; Layanan Bantuan Sosial Masyarakat</span>
                <span>UU No. 27 Tahun 2022 &middot; Perlindungan Data Pribadi</span>
            </footer>
        </main>
    </div>

    @include('components.sidebar-script')

    @stack('scripts')
</body>

</html>
