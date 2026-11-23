@extends('layouts.public')

@section('title', 'Beranda')

@section('content')

    {{-- HERO SECTION --}}
    <section class="public-hero">

        <div class="public-hero-inner">

            <div class="public-hero-copy">

                <span class="public-eyebrow">
                    SDG 1 · TANPA KEMISKINAN
                </span>

                <h1>
                    Bantuan sosial<br>
                    yang tepat sasaran.
                </h1>

                <p>
                    SIBANSOS memusatkan pendataan calon penerima,
                    verifikasi kelayakan, dan riwayat penyaluran
                    dalam satu sistem. Warga dapat mengajukan diri
                    dan memantau statusnya tanpa harus datang
                    ke kantor desa.
                </p>

                <div class="public-hero-actions">

                    <a href="{{ route('register') }}" class="public-button public-button-primary">
                        Ajukan Pendaftaran
                    </a>

                    <a href="{{ route('login') }}" class="public-button public-button-outline">
                        Cek Status Saya
                    </a>

                </div>

                <div class="public-hero-stats">

                    <div>
                        <strong>{{ number_format($jumlahPenerima, 0, ',', '.') }}</strong>
                        <span>Penerima terdata</span>
                    </div>

                    <div>
                        <strong>{{ $jumlahRw + $jumlahRt }}</strong>
                        <span>Wilayah RT / RW</span>
                    </div>

                    <div>
                        <strong>{{ $jumlahProgram }}</strong>
                        <span>Program bantuan aktif</span>
                    </div>

                </div>

            </div>

            {{-- KOLASE FOTO HERO --}}
            <div class="public-hero-visual">

                <div class="public-photo public-photo-field">

                    @if (file_exists(public_path('images/landing/sawah.png')))
                        <img src="{{ asset('images/landing/sawah.png') }}" alt="Wilayah persawahan dan permukiman">
                    @else
                        <span class="public-photo-placeholder">
                            Tempat foto wilayah / desa
                        </span>
                    @endif

                    <small>
                        Wilayah pelayanan<br>
                        Desa dan kelurahan
                    </small>

                </div>

                <div class="public-photo public-photo-aid">

                    @if (file_exists(public_path('images/landing/bantuan-sembako.png')))
                        <img src="{{ asset('images/landing/bantuan-sembako.png') }}" alt="Penyaluran bantuan sembako">
                    @else
                        <span class="public-photo-placeholder">
                            Tempat foto penyaluran bantuan
                        </span>
                    @endif

                    <small>
                        Program bantuan sosial<br>
                        Penyaluran bantuan
                    </small>

                </div>

                <div class="public-photo public-photo-recipient">

                    @if (file_exists(public_path('images/landing/penerima.png')))
                        <img src="{{ asset('images/landing/penerima.png') }}" alt="Warga penerima manfaat">
                    @else
                        <span class="public-photo-placeholder">
                            Tempat foto warga penerima manfaat
                        </span>
                    @endif

                    <small>
                        Masyarakat penerima manfaat<br>
                        Pendataan terverifikasi
                    </small>

                </div>

                <div class="public-floating-stat">

                    <strong>{{ number_format($jumlahPenerima, 0, ',', '.') }}</strong>
                    <span>penerima terdata</span>

                </div>

            </div>

        </div>

    </section>

    {{-- CEK STATUS PENGAJUAN --}}
    <section class="public-track" id="cek-status">

        <div class="public-track-copy">

            <span>PEMANTAUAN PENGAJUAN</span>

            <strong>
                Pantau status pengajuan bantuan Anda dengan mudah
            </strong>

        </div>

        <div class="public-track-form">

            <p>
                Masuk ke akun SIBANSOS untuk melihat status verifikasi,
                informasi pengajuan, dan riwayat bantuan Anda.
            </p>

            <a href="{{ route('login') }}" class="public-track-button">
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
                Masuk untuk Cek Status
            </a>

        </div>

    </section>

    {{-- KATEGORI BANTUAN --}}
    <section class="public-program-section" id="program">

        <div class="public-section-heading public-section-heading-inline">

            <h2>Empat kategori bantuan</h2>

            <span>satu pintu pendataan</span>

        </div>

        <div class="public-program-grid">

            @forelse ($kategoriBantuan as $item)

                @php
                    $jenisAktif = $item->jenis->where('aktif', true);

                    $kodeProgram = match ((int) $item->id_kategori) {
                        1 => 'PKH',
                        2 => 'BPNT',
                        3 => 'BLT-DD',
                        4 => 'PIP',
                        default => 'PROGRAM',
                    };

                    $cardClass = match ((int) $item->id_kategori) {
                        1 => 'public-program-pkh',
                        2 => 'public-program-bpnt',
                        3 => 'public-program-blt',
                        4 => 'public-program-education',
                        default => 'public-program-education',
                    };
                @endphp

                <article class="public-program-card {{ $cardClass }}">

                    <span class="public-program-tag">
                        {{ $kodeProgram }}
                    </span>

                    <div>
                        <h3>{{ $kodeProgram }}</h3>

                        <p>
                            {{ $item->nama }}
                        </p>

                        <small>
                            {{ $jenisAktif->pluck('nama_program')->implode(', ') }}
                        </small>
                    </div>

                </article>

            @empty

                <p>
                    Informasi kategori bantuan belum tersedia.
                </p>

            @endforelse

        </div>

    </section>

    {{-- STATISTIK --}}
    <section class="public-stat-band">

        <div>
            <strong>{{ number_format($jumlahPenerima, 0, ',', '.') }}</strong>
            <span>Penerima terdata</span>
        </div>

        <div>
            <strong>{{ $jumlahRw }}</strong>
            <span>Wilayah RW</span>
        </div>

        <div>
            <strong>{{ $jumlahRt }}</strong>
            <span>Wilayah RT</span>
        </div>

        <div>
            <strong>{{ $jumlahProgram }}</strong>
            <span>Program bantuan aktif</span>
        </div>

    </section>

    {{-- ALUR PENDAFTARAN --}}
    <section class="public-steps-section" id="alur">

        <div class="public-section-heading public-section-heading-centered">

            <span class="public-eyebrow">
                ALUR PENDAFTARAN
            </span>

            <h2>
                Terdaftar dalam empat langkah.
            </h2>

            <p>
                Mulai dari melengkapi profil hingga memantau hasil
                pengajuan bantuan melalui akun SIBANSOS.
            </p>

        </div>

        <div class="public-steps-grid">

            {{-- LANGKAH 1 --}}
            <article class="public-step-card">

                <span class="public-step-number">1</span>

                <h3>Daftar & lengkapi profil</h3>

                <p>
                    Buat akun, isi data diri dan kondisi keluarga,
                    lalu unggah dokumen persyaratan.
                </p>

            </article>


            {{-- LANGKAH 2 --}}
            <article class="public-step-card">

                <span class="public-step-number">2</span>

                <h3>Verifikasi profil</h3>

                <p>
                    Petugas memeriksa identitas, dokumen,
                    dan kelengkapan data calon penerima.
                </p>

            </article>


            {{-- LANGKAH 3 --}}
            <article class="public-step-card">

                <span class="public-step-number">3</span>

                <h3>Ajukan bantuan</h3>

                <p>
                    Setelah profil terverifikasi, pilih program
                    bantuan yang tersedia dan kirim pengajuan.
                </p>

            </article>


            {{-- LANGKAH 4 --}}
            <article class="public-step-card">

                <span class="public-step-number">4</span>

                <h3>Verifikasi & pantau bantuan</h3>

                <p>
                    Petugas memproses pengajuan. Bantuan yang
                    telah diterima tercatat dalam riwayat.
                </p>

            </article>

        </div>

    </section>

    {{-- KEAMANAN DATA --}}
    <section class="public-trust-section">

        <div class="public-section-heading public-section-heading-centered">

            <span class="public-eyebrow">
                YANG DIJAGA SIBANSOS
            </span>

            <h2>
                Data yang bisa dipertanggungjawabkan.
            </h2>

        </div>

        <div class="public-trust-grid">

            <article class="public-trust-card">

                <span class="public-trust-icon">▣</span>

                <h3>NIK unik</h3>

                <p>
                    Satu NIK hanya boleh terdaftar sebagai satu penerima.
                    Sistem menolak duplikasi sejak input pertama.
                </p>

            </article>


            <article class="public-trust-card">

                <span class="public-trust-icon">◴</span>

                <h3>Riwayat utuh</h3>

                <p>
                    Data yang sudah punya riwayat bantuan tidak
                    dapat dihapus, hanya dinonaktifkan.
                </p>

            </article>


            <article class="public-trust-card">

                <span class="public-trust-icon">♙</span>

                <h3>Data terbatas</h3>

                <p>
                    NIK disamarkan pada daftar. Warga hanya
                    melihat datanya sendiri.
                </p>

            </article>


            <article class="public-trust-card">

                <span class="public-trust-icon">≡</span>

                <h3>Jejak audit</h3>

                <p>
                    Setiap perubahan mencatat siapa, kapan,
                    dan nilai sebelum-sesudahnya.
                </p>

            </article>

        </div>

    </section>


    {{-- FOOTER --}}
    <footer class="public-footer-main" id="kontak">

        <div class="public-footer-grid">

            <div class="public-footer-about">

                <a href="{{ route('landing') }}" class="public-footer-brand">

                    <span class="public-footer-mark">S</span>

                    <span>
                        <b>SIBANSOS</b>
                        <small>Pendataan Bantuan Sosial</small>
                    </span>

                </a>

                <p>
                    Sistem Pendataan Bantuan Sosial untuk tingkat
                    desa dan kelurahan. Dibangun untuk mendukung
                    SDG 1 Tanpa Kemiskinan.
                </p>

            </div>


            <div>

                <h3>Layanan</h3>

                <a href="{{ route('register') }}">
                    Ajukan Bantuan
                </a>

                <a href="#cek-status">
                    Cek Status
                </a>

                <a href="{{ route('login') }}">
                    Riwayat Bantuan
                </a>

                <a href="{{ route('login') }}">
                    Perbaikan Data
                </a>

            </div>


            <div>

                <h3>Informasi</h3>

                <a href="#program">
                    Kategori Bantuan
                </a>

                <a href="#alur">
                    Alur Pendaftaran
                </a>

                <a href="#kontak">
                    Syarat & Ketentuan
                </a>

                <a href="#kontak">
                    Kebijakan Privasi
                </a>

            </div>

            <div>

                <h3>Kontak</h3>

                <span>Informasi layanan SIBANSOS</span>

                <span>
                    Silakan masuk ke akun untuk mengakses
                    layanan dan informasi bantuan.
                </span>

                <a href="{{ route('login') }}">
                    Masuk ke SIBANSOS
                </a>

            </div>

        </div>


        <div class="public-footer-bottom">

            <span>
                © {{ date('Y') }} SIBANSOS · Kelompok 3 TI-B ·
                Universitas Brawijaya
            </span>

            <span>
                UU No. 27 Tahun 2022 · Pelindungan Data Pribadi
            </span>

        </div>

    </footer>

@endsection