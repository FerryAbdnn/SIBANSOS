<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Masuk &middot; SIBANSOS</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="stylesheet" href="{{ asset('css/design.css') }}">
</head>

<body>

    <div class="auth-page">

        {{-- KOLOM FORM --}}
        <div class="auth-form-side">

            <a class="brand" href="{{ route('landing') }}">
                <span class="brand-mark">
                    <x-icon name="shield" :size="20" />
                </span>

                <span>
                    <b>SIBANSOS</b>
                    <small>Pendaftaran Bantuan Sosial</small>
                </span>
            </a>

            <div class="auth-form-inner">

                <h1>Masuk ke SIBANSOS</h1>

                <p class="auth-lead">
                    Admin mengelola data wilayah penugasannya, warga hanya melihat
                    data dan pengajuan miliknya sendiri.
                </p>

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <label class="field-label">Masuk sebagai</label>

                    <div class="role-group">
                        <label class="role-option">
                            <input type="radio" name="peran" value="admin"
                                @checked(old('peran', 'admin') === 'admin')>

                            <span class="ic"><x-icon name="id-card" :size="15" /></span>

                            <span>
                                <b>Admin / Petugas</b>
                                <small>Desa, kelurahan, RW, RT</small>
                            </span>
                        </label>

                        <label class="role-option">
                            <input type="radio" name="peran" value="masyarakat"
                                @checked(old('peran') === 'masyarakat')>

                            <span class="ic"><x-icon name="user" :size="15" /></span>

                            <span>
                                <b>Masyarakat</b>
                                <small>Pemohon bantuan</small>
                            </span>
                        </label>
                    </div>

                    <div class="auth-fields">

                        <div class="field">
                            <label for="email">Email <span class="req">*</span></label>

                            <input class="input" type="email" id="email" name="email"
                                value="{{ old('email') }}" placeholder="nama@desa-sukamaju.id"
                                required autofocus autocomplete="email">

                            <small class="hint">Gunakan email yang terdaftar pada akun Anda.</small>

                            @error('email')
                                <small class="err">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="password">Kata Sandi <span class="req">*</span></label>

                            <input class="input" type="password" id="password" name="password"
                                placeholder="Masukkan kata sandi" required autocomplete="current-password">

                            @error('password')
                                <small class="err">{{ $message }}</small>
                            @enderror
                        </div>

                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger" style="margin-top:14px;">
                            <x-icon name="alert" :size="16" />
                            <div>{{ $errors->first() }}</div>
                        </div>
                    @endif

                    <div class="auth-row">
                        <label class="checkline">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                            Ingat saya di perangkat ini
                        </label>

                        <span class="muted" style="font-size:11.5px;">Lupa sandi? Hubungi petugas desa</span>
                    </div>

                    <button class="btn btn-primary btn-block" type="submit">
                        Masuk
                    </button>

                    <div class="auth-divider">belum punya akun?</div>

                    <a href="{{ route('register') }}" class="btn btn-outline btn-block">
                        Daftar sebagai warga
                    </a>
                </form>

                <p class="auth-legal">
                    Data identitas dilindungi sesuai UU No. 27 Tahun 2022 tentang
                    Pelindungan Data Pribadi.
                </p>

            </div>
        </div>

        {{-- KOLOM INFORMASI --}}
        <aside class="auth-hero">

            <span class="eyebrow">SDG 1 &mdash; Tanpa Kemiskinan</span>

            <h2>Satu data, satu pintu.</h2>

            <p class="auth-hero-lead">
                Pendataan calon penerima, verifikasi kelayakan, dan riwayat penyaluran
                terintegrasi dalam satu sistem di tingkat desa dan kelurahan.
            </p>

            <div class="auth-metrics">
                <div>
                    <b>{{ number_format(\App\Models\Penerima::count(), 0, ',', '.') }}</b>
                    <span>Penerima terdaftar</span>
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

            <div class="auth-demo">
                <div class="auth-demo-label">
                    <x-icon name="info" :size="12" />
                    Akun demo
                </div>

                <div class="auth-demo-row">
                    <span>Admin</span>
                    <b>admin@sibansos.local &middot; password123</b>
                </div>

                <div class="auth-demo-row">
                    <span>Warga</span>
                    <b>warga@sibansos.local &middot; password123</b>
                </div>
            </div>

        </aside>

    </div>

</body>

</html>
