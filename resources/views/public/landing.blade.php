@extends('layouts.public')

@section('title', 'Beranda')

@section('content')

    {{-- HERO --}}
    <section class="hero">
        <div class="hero-inner">

            <div>
                <span class="eyebrow">SDG 1 &middot; Tanpa Kemiskinan</span>

                <h1>
                    Bantuan sosial<br>yang tepat sasaran.
                </h1>

                <p class="hero-lead">
                    SIBANSOS memusatkan pendaftaran calon penerima, verifikasi kelayakan,
                    dan riwayat penyaluran dalam satu sistem. Warga dapat mengajukan diri
                    dan memantau statusnya tanpa harus datang ke kantor desa.
                </p>

                <div class="hero-actions">
                    <a href="{{ route('register') }}" class="btn btn-primary">
                        Ajukan Pendaftaran
                    </a>

                    <a href="{{ route('login') }}" class="btn btn-outline">
                        Cek Status Saya
                    </a>
                </div>

                <div class="hero-stats">
                    <div>
                        <b>{{ number_format(\App\Models\Penerima::count(), 0, ',', '.') }}</b>
                        <span>Penerima terdata</span>
                    </div>

                    <div>
                        <b>{{ \App\Models\Wilayah::whereIn('tipe', ['rt', 'rw'])->count() }}</b>
                        <span>RT / RW terjangkau</span>
                    </div>

                    <div>
                        <b>{{ \App\Models\KategoriBantuan::count() }}</b>
                        <span>Kategori bantuan</span>
                    </div>
                </div>
            </div>

            <div class="hero-collage">
                <div class="hero-tile">
                    <b>Sawah Sumber Makmur</b>
                    <small>Potret wilayah penerima manfaat</small>
                </div>

                <div class="hero-tile">
                    <b>Penyaluran sembako</b>
                    <small>Tahap II &middot; 2026</small>
                </div>

                <div class="hero-tile">
                    <b>Warga penerima</b>
                    <small>RT 02 / RW 03</small>
                </div>

                <div class="hero-float">
                    <b>{{ number_format(\App\Models\Penerima::count(), 0, ',', '.') }}</b>
                    <span>penerima terdata</span>
                </div>
            </div>

        </div>
    </section>

    {{-- PITA CEK STATUS --}}
    <section class="status-band">
        <div class="status-band-inner">

            <div class="status-band-text">
                <span class="eyebrow">Lacak tanpa login</span>
                <b>Cek status pengajuan dengan NIK dan nomor pengajuan</b>
            </div>

            <form class="status-band-form" method="GET" action="{{ route('login') }}">
                <input type="text" name="nik" inputmode="numeric" maxlength="16"
                       placeholder="NIK 16 digit">

                <input type="text" name="nomor" placeholder="No. pengajuan">

                <button type="submit" class="btn">Cek Status</button>
            </form>

        </div>
    </section>

    {{-- PROGRAM --}}
    <section class="section" id="program">
        <div class="wrap">

            <div class="section-head">
                <h2>Kategori bantuan <span>satu pintu pendataan</span></h2>

                <p>
                    Setiap kategori memiliki kriteria dan besaran yang berbeda.
                    Sistem mencocokkan profil warga dengan kriteria yang tersedia.
                </p>
            </div>

            @php
                $kategoriPublik = \App\Models\KategoriBantuan::withCount('jenis')->take(4)->get();

                $gradien = ['', 'p2', 'p3', 'p4'];
            @endphp

            <div class="program-grid">
                @foreach ($kategoriPublik as $i => $k)
                    <div class="program-card {{ $gradien[$i] ?? '' }}">
                        <span class="program-tag">{{ strtoupper($k->nama) }}</span>

                        <h3>{{ strtoupper(substr($k->nama, 0, 6)) }}</h3>

                        <p>{{ $k->deskripsi ?: 'Program bantuan ' . $k->nama }}</p>

                        <b>{{ $k->jenis_count }} jenis bantuan</b>
                    </div>
                @endforeach
            </div>

            <div class="program-wide">
                <span class="ic"><x-icon name="heart" :size="17" /></span>

                <div>
                    <b>Kesehatan</b>
                    <p>Bantuan biaya pengobatan &amp; pendampingan lansia</p>
                </div>

                <span class="tag">Kategori ke-5</span>
            </div>

        </div>
    </section>

    {{-- STATISTIK --}}
    <section class="section" style="padding-top:0;">
        <div class="wrap">
            <div class="metric-strip">
                <div>
                    <b>{{ number_format(\App\Models\Penerima::count(), 0, ',', '.') }}</b>
                    <span>Penerima terdata</span>
                </div>

                <div>
                    <b>{{ \App\Models\Wilayah::whereIn('tipe', ['rt', 'rw'])->count() }}</b>
                    <span>RT / RW terjangkau</span>
                </div>

                <div>
                    <b>{{ \App\Models\Penerima::where('status', 'terverifikasi')->count() }}</b>
                    <span>Terverifikasi petugas</span>
                </div>

                <div>
                    <b>0</b>
                    <span>Data ganda terdeteksi</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ALUR --}}
    <section class="section cream" id="alur">
        <div class="wrap">

            <div class="section-head">
                <span class="eyebrow">Alur Pendaftaran</span>

                <h2>Terdaftar dalam empat langkah.</h2>

                <p>
                    Tanpa antre di kantor desa. Semua tahap dapat dipantau
                    dari akun Anda.
                </p>
            </div>

            <div class="step-grid">
                <div class="step-card">
                    <span class="step-num">1</span>
                    <b>Daftar &amp; unggah data</b>
                    <p>Buat akun dengan NIK, lalu isi formulir dan unggah foto KTP serta KK.</p>
                </div>

                <div class="step-card">
                    <span class="step-num">2</span>
                    <b>Verifikasi petugas</b>
                    <p>Petugas desa memeriksa kelengkapan dokumen dan kondisi ekonomi Anda.</p>
                </div>

                <div class="step-card">
                    <span class="step-num">3</span>
                    <b>Penetapan kategori</b>
                    <p>Sistem mencocokkan profil Anda dengan kriteria kategori bantuan yang tersedia.</p>
                </div>

                <div class="step-card">
                    <span class="step-num">4</span>
                    <b>Pantau penyaluran</b>
                    <p>Setiap bantuan yang Anda terima tercatat pada riwayat dan dapat dilihat kapan saja.</p>
                </div>
            </div>

        </div>
    </section>

    {{-- JAMINAN DATA --}}
    <section class="section" id="jaminan">
        <div class="wrap">

            <div class="section-head">
                <span class="eyebrow">Yang dijaga SIBANSOS</span>

                <h2>Data yang bisa dipertanggungjawabkan.</h2>
            </div>

            <div class="guard-grid">
                <div class="guard-card">
                    <span class="guard-icon"><x-icon name="id-card" :size="17" /></span>
                    <b>NIK unik</b>
                    <p>Satu NIK hanya boleh terdaftar sebagai satu penerima. Sistem menolak duplikasi sejak input pertama.</p>
                </div>

                <div class="guard-card">
                    <span class="guard-icon"><x-icon name="clock" :size="17" /></span>
                    <b>Riwayat utuh</b>
                    <p>Data yang sudah punya riwayat bantuan tidak dapat dihapus, hanya dinonaktifkan.</p>
                </div>

                <div class="guard-card">
                    <span class="guard-icon"><x-icon name="shield" :size="17" /></span>
                    <b>Data terbatas</b>
                    <p>NIK disamarkan pada daftar. Warga hanya melihat datanya sendiri.</p>
                </div>

                <div class="guard-card">
                    <span class="guard-icon"><x-icon name="filter" :size="17" /></span>
                    <b>Jejak audit</b>
                    <p>Setiap perubahan mencatat siapa, kapan, dan nilai sebelum-sesudahnya.</p>
                </div>
            </div>

        </div>
    </section>

@endsection
