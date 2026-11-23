<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Masuk · SIBANSOS</title>

    <link rel="stylesheet" href="{{ asset('css/auth-login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth-common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/brand-logo.css') }}">
</head>

<body>

    <main class="login-page">

        <!-- PANEL KIRI: FORM LOGIN -->
        <section class="login-main">
            <div class="login-container">

                @include('components.brand-logo', [
                    'href' => url('/'),
                    'class' => 'login-brand'
                ])

                <h1 class="login-heading">Masuk ke SIBANSOS</h1>

                <p class="login-description">
                    Silakan masuk menggunakan akun yang telah terdaftar
                    untuk mengakses layanan bantuan sosial.
                </p>

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <span class="login-role-label">Masuk sebagai</span>

                    <div class="login-role-options">

                        <label class="login-role-option">
                            <input
                                type="radio"
                                name="jenis_login"
                                value="admin"
                                {{ old('jenis_login', 'admin') === 'admin' ? 'checked' : '' }}
                            >

                            <span>
                                <strong>Admin / Petugas</strong>
                                <small>Pengelolaan data bantuan</small>
                            </span>
                        </label>

                        <label class="login-role-option">
                            <input
                                type="radio"
                                name="jenis_login"
                                value="masyarakat"
                                {{ old('jenis_login') === 'masyarakat' ? 'checked' : '' }}
                            >

                            <span>
                                <strong>Masyarakat</strong>
                                <small>Layanan warga</small>
                            </span>
                        </label>

                    </div>

                    <div class="login-field">
                        <label for="email">Alamat email</label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            autocomplete="username"
                            required
                            autofocus
                        >

                        <small class="login-field-help">
                            Gunakan alamat email yang terdaftar pada SIBANSOS.
                        </small>
                    </div>

                    <div class="login-field">
                        <label for="password">Kata sandi</label>

                        <div class="login-password-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Masukkan kata sandi"
                                autocomplete="current-password"
                                required
                            >

                            <button
                                type="button"
                                class="login-password-toggle"
                                id="togglePassword"
                                aria-label="Tampilkan kata sandi"
                                title="Tampilkan atau sembunyikan kata sandi"
                            >
                                <svg id="eyeIcon" width="19" height="19"
                                    viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2.5 12C4.7 7.9 8 5.8 12 5.8C16 5.8 19.3 7.9 21.5 12C19.3 16.1 16 18.2 12 18.2C8 18.2 4.7 16.1 2.5 12Z"
                                        stroke="currentColor" stroke-width="1.6"/>
                                    <circle cx="12" cy="12" r="3"
                                        stroke="currentColor" stroke-width="1.6"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="login-error" role="alert">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <button class="login-submit" type="submit">
                        Masuk ke akun
                    </button>

                </form>

                <div class="login-register-divider">
                    <span>Belum memiliki akun?</span>
                </div>

                <a class="login-register-button" href="{{ route('register') }}">
                    Daftar sebagai warga
                </a>

                <p class="login-privacy">
                    Dengan masuk, Anda menggunakan layanan resmi
                    Sistem Pendataan Bantuan Sosial (SIBANSOS).
                </p>

            </div>
        </section>

        <!-- PANEL KANAN: INFORMASI SIBANSOS -->
        <aside class="login-aside">

            <div class="login-aside-content">

                <span class="login-aside-label">
                    SDG 1 · TANPA KEMISKINAN
                </span>

                <h2 class="login-aside-heading">
                    Satu data,<br>
                    satu pintu.
                </h2>

                <p class="login-aside-description">
                    SIBANSOS membantu pemerintah desa mengelola
                    pendataan, pengajuan, dan penyaluran bantuan sosial
                    secara lebih terarah, transparan, dan terintegrasi.
                </p>

                <div class="login-aside-divider"></div>

                <div class="login-stats">

                    <div class="login-stat">
                        <strong>01</strong>
                        <span>Pendataan warga terpusat</span>
                    </div>

                    <div class="login-stat">
                        <strong>02</strong>
                        <span>Verifikasi pengajuan</span>
                    </div>

                    <div class="login-stat">
                        <strong>03</strong>
                        <span>Pemantauan bantuan</span>
                    </div>

                </div>

                <div class="login-demo">
                    <p class="login-demo-title">
                        LAYANAN SIBANSOS
                    </p>

                    <div class="login-demo-item">
                        <span>Untuk Admin / Petugas</span>
                        <strong>Kelola dan verifikasi data bantuan sosial</strong>
                    </div>

                    <div class="login-demo-item">
                        <span>Untuk Masyarakat</span>
                        <strong>Ajukan bantuan dan pantau status pengajuan</strong>
                    </div>
                </div>

            </div>

        </aside>

    </main>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';

            passwordInput.type = isPassword ? 'text' : 'password';

            this.setAttribute(
                'aria-label',
                isPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'
            );
        });
    </script>

</body>
</html>
