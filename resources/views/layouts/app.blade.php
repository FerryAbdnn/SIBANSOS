<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        @yield('title', 'SIBANSOS') - Sistem Pendataan Bantuan Sosial
    </title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body>

    <div class="shell">

        {{-- Sidebar --}}
        <aside class="sidebar">

            {{-- Brand --}}
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-mark">S</span>

                <span>
                    <b>SIBANSOS</b>
                    <small>Sistem Bantuan Sosial</small>
                </span>
            </a>

            {{-- Main Menu --}}
            <div class="side-label">MENU UTAMA</div>

            <a href="{{ route('dashboard') }}">
                ▦ &nbsp; Dashboard
            </a>

            {{-- Role-based Menu --}}
            @if (auth()->user()->peran === 'admin')

                <div class="side-label">PENGELOLAAN</div>

                <a href="{{ route('admin.penerima.index') }}">
                    ♙ &nbsp; Data Penerima
                </a>

                <a href="{{ route('admin.pengajuan.index') }}">
                    ✓ &nbsp; Verifikasi Pengajuan
                </a>

                <a href="{{ route('admin.kategori.index') }}">
                    ▤ &nbsp; Kategori Bantuan
                </a>

                <a href="{{ route('admin.jenis.index') }}">
                    ▣ &nbsp; Jenis Bantuan
                </a>

                <a href="{{ route('admin.riwayat.index') }}">
                    ◷ &nbsp; Riwayat Bantuan
                </a>

                <a href="{{ route('admin.perbaikan.index') }}">
                    ✎ &nbsp; Perbaikan Data
                </a>

            @else

                <a href="{{ route('masyarakat.pengajuan.create') }}">
                    ＋ &nbsp; Ajukan Bantuan
                </a>

                <a href="{{ route('masyarakat.status') }}">
                    ◷ &nbsp; Status Pengajuan
                </a>

            @endif

            {{-- Profile Menu --}}
            <a href="{{ route('profile') }}">
                ♙ &nbsp; Profil
            </a>

            {{-- Sidebar Bottom --}}
            <div class="side-bottom">

                <div class="user-chip">

                    <div class="avatar">
                        {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                    </div>

                    <div>
                        <b>{{ auth()->user()->nama }}</b>
                        <small>{{ ucfirst(auth()->user()->peran) }}</small>
                    </div>

                </div>

                {{-- Logout Form --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="logout" type="submit">
                        ↪ Keluar
                    </button>
                </form>

            </div>

        </aside>

        {{-- Main Content --}}
        <main class="main">

            {{-- Topbar --}}
            <header class="topbar">

                <div>
                    <span class="eyebrow">
                        SISTEM INFORMASI BANTUAN SOSIAL
                    </span>

                    <h2>
                        @yield('heading', 'Dashboard')
                    </h2>
                </div>

                <div class="top-user">

                    {{ now()->translatedFormat('l, d F Y') }}

                    <span class="avatar">
                        {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                    </span>

                </div>

            </header>

            {{-- Page Content --}}
            <section class="content">

                {{-- Success Notification --}}
                @if (session('success'))
                    <div class="alert success">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Validation Error Notification --}}
                @if ($errors->any())
                    <div class="alert danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Content from Child View --}}
                @yield('content')

            </section>

            {{-- Footer --}}
            <footer>
                © {{ date('Y') }} SIBANSOS ·
                Mendukung SDG 1 Tanpa Kemiskinan
            </footer>

        </main>

    </div>

</body>

</html>