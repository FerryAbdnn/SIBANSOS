<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard Admin') - SIBANSOS</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>

<body>

    <div class="app-shell">
        @include('components.sidebar-admin')

        <main class="main-content">
            @include('components.navbar')

            <section class="page-content">

                @include('components.alert')

                @yield('content')

            </section>

            <footer class="app-footer">
                © {{ date('Y') }} SIBANSOS ·
                Mendukung SDG 1 Tanpa Kemiskinan
            </footer>
        </main>
    </div>

    @stack('scripts')
</body>

</html>