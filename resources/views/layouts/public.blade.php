<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'SIBANSOS') &middot; Sistem Pendataan Bantuan Sosial</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="stylesheet" href="{{ asset('css/design.css') }}">
    @stack('styles')
</head>

<body class="public-body">

    <div class="top-strip">
        Pendaftaran mandiri dibuka kembali &middot;
        <a href="{{ auth()->check() ? route('dashboard') : route('register') }}">
            Cek kelayakan Anda sekarang
        </a>
    </div>

    <header class="public-nav">
        <div class="public-nav-inner">

            <a href="{{ route('landing') }}" class="brand">
                <span class="brand-mark">
                    <x-icon name="shield" :size="20" />
                </span>

                <span>
                    <b>SIBANSOS</b>
                    <small>Pendaftaran Bantuan Sosial</small>
                </span>
            </a>

            <nav class="public-nav-links">
                <a href="{{ route('landing') }}">Beranda</a>
                <a href="#program">Program</a>
                <a href="#alur">Alur Pengajuan</a>
                <a href="#jaminan">Jaminan Data</a>
                <a href="#kontak">Kontak</a>
            </nav>

            <div class="public-nav-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        Buka Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Ajukan Bantuan</a>
                @endauth
            </div>

        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="public-footer" id="kontak">
        <div class="wrap">

            <div class="footer-grid">

                <div>
                    <div class="brand">
                        <span class="brand-mark">
                            <x-icon name="shield" :size="20" />
                        </span>

                        <span>
                            <b>SIBANSOS</b>
                            <small>Pendaftaran Bantuan Sosial</small>
                        </span>
                    </div>

                    <p class="footer-about">
                        Sistem Pendataan Bantuan Sosial untuk tingkat desa dan kelurahan.
                        Dibangun untuk mendukung SDG 1 Tanpa Kemiskinan.
                    </p>
                </div>

                <div class="footer-col">
                    <h4>Layanan</h4>
                    <a href="{{ route('register') }}">Ajukan Bantuan</a>
                    <a href="{{ route('login') }}">Cek Status</a>
                    <a href="{{ route('login') }}">Riwayat Bantuan</a>
                    <a href="{{ route('login') }}">Perbarui Data</a>
                </div>

                <div class="footer-col">
                    <h4>Informasi</h4>
                    <a href="#program">Kategori Bantuan</a>
                    <a href="#alur">Alur Pendaftaran</a>
                    <a href="#jaminan">Jaminan Data</a>
                    <a href="#kontak">Kebijakan Privasi</a>
                </div>

                <div class="footer-col">
                    <h4>Kontak</h4>
                    <span>Kantor Desa Sukamaju</span>
                    <span>Kecamatan Lowokwaru, Malang</span>
                    <span>0812 3456 7890</span>
                    <span>halo@sibansos.id</span>
                </div>

            </div>

            <div class="footer-bottom">
                <span>
                    &copy; {{ date('Y') }} SIBANSOS &middot; Kelompok 4 TI-B &middot;
                    Universitas Brawijaya
                </span>

                <span>UU No. 27 Tahun 2022 &middot; Perlindungan Data Pribadi</span>
            </div>

        </div>
    </footer>

    @stack('scripts')
</body>

</html>
