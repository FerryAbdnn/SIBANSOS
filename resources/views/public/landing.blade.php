@extends('layouts.public')

@section('title', 'Beranda')

@section('content')

    <section class="landing-hero">
        <div class="landing-copy">
            <span class="eyebrow">SISTEM INFORMASI BANTUAN SOSIAL</span>

            <h1>
                Pendataan Bantuan Sosial yang
                <span>Transparan dan Terpercaya</span>
            </h1>

            <p>
                SIBANSOS membantu masyarakat mengajukan bantuan sosial
                dan memudahkan petugas mengelola data penerima secara
                terstruktur, aman, dan transparan.
            </p>

            <div class="landing-actions">
                <a href="{{ route('register') }}" class="btn primary">
                    Daftar Sekarang
                </a>

                <a href="{{ route('login') }}" class="btn outline">
                    Masuk ke Sistem
                </a>
            </div>
        </div>

        <div class="landing-visual">
            <div class="landing-visual-card">
                <div class="landing-icon">♡</div>
                <h3>Penyaluran Bantuan Tepat Sasaran</h3>
                <p>
                    Data penerima terkelola dan proses pengajuan
                    dapat dipantau secara berkala.
                </p>
            </div>
        </div>
    </section>

    <section class="landing-about" id="tentang">
        <span class="eyebrow">TENTANG SIBANSOS</span>
        <h2>Mendukung kesejahteraan masyarakat</h2>
        <p>
            SIBANSOS merupakan platform pendataan dan pengelolaan
            bantuan sosial yang mendukung upaya pengentasan kemiskinan
            sesuai tujuan pembangunan berkelanjutan SDG 1.
        </p>
    </section>

@endsection