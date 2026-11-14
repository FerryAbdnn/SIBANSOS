<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Daftar &middot; SIBANSOS</title>

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

                <h1>Buat akun warga</h1>

                <p class="auth-lead">
                    Akun dipakai untuk mengajukan bantuan, memantau status, dan melihat
                    riwayat bantuan yang Anda terima.
                </p>

                <form method="POST" action="{{ route('register.post') }}">
                    @csrf

                    <div class="auth-fields cols-2">

                        <div class="field">
                            <label for="nama">Nama Lengkap <span class="req">*</span></label>

                            <input class="input" type="text" id="nama" name="nama"
                                value="{{ old('nama') }}" placeholder="Sesuai KTP" required autofocus>

                            @error('nama')
                                <small class="err">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="email">Email <span class="req">*</span></label>

                            <input class="input" type="email" id="email" name="email"
                                value="{{ old('email') }}" placeholder="nama@mail.id" required>

                            <small class="hint">Dipakai untuk masuk ke akun.</small>

                            @error('email')
                                <small class="err">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="password">Kata Sandi <span class="req">*</span></label>

                            <input class="input" type="password" id="password" name="password"
                                placeholder="Minimal 8 karakter" required>

                            <small class="hint">Minimal 8 karakter, kombinasi huruf dan angka.</small>

                            @error('password')
                                <small class="err">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="password_confirmation">
                                Ulangi Kata Sandi <span class="req">*</span>
                            </label>

                            <input class="input" type="password" id="password_confirmation"
                                name="password_confirmation" placeholder="Ulangi kata sandi" required>
                        </div>

                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger" style="margin-top:14px;">
                            <x-icon name="alert" :size="16" />
                            <div>{{ $errors->first() }}</div>
                        </div>
                    @endif

                    <label class="checkline" style="margin:18px 0; align-items:flex-start;">
                        <input type="checkbox" name="setuju" value="1" required
                            @checked(old('setuju')) style="margin-top:2px;">

                        <span>
                            Saya menyetujui data pribadi saya digunakan untuk keperluan
                            pendataan bantuan sosial sesuai <b>UU No. 27 Tahun 2022.</b>
                            Data tidak dibagikan ke pihak lain tanpa persetujuan.
                        </span>
                    </label>

                    <button class="btn btn-primary btn-block" type="submit">
                        Buat Akun
                    </button>

                    <p class="muted" style="text-align:center; font-size:12px; margin-top:14px;">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" style="font-weight:700;">Masuk di sini</a>
                    </p>
                </form>

                <p class="auth-legal">
                    NIK hanya bisa dipakai untuk satu akun. Data identitas disimpan
                    terenkripsi dan hanya dapat dilihat petugas berwenang.
                </p>

            </div>
        </div>

        {{-- KOLOM INFORMASI --}}
        <aside class="auth-hero">

            <span class="eyebrow">Setelah akun aktif</span>

            <h2>Tiga hal yang bisa Anda lakukan.</h2>

            <div class="auth-hero-list">

                <div class="auth-hero-item">
                    <span class="ic"><x-icon name="send" :size="16" /></span>

                    <div>
                        <b>Ajukan bantuan mandiri</b>
                        <p>Isi formulir dari rumah, unggah foto KTP dan KK.</p>
                    </div>
                </div>

                <div class="auth-hero-item">
                    <span class="ic"><x-icon name="file" :size="16" /></span>

                    <div>
                        <b>Pantau status real-time</b>
                        <p>Diajukan, terverifikasi, ditolak, atau nonaktif.</p>
                    </div>
                </div>

                <div class="auth-hero-item">
                    <span class="ic"><x-icon name="history" :size="16" /></span>

                    <div>
                        <b>Lihat riwayat bantuan</b>
                        <p>Jenis, tanggal, dan nilai bantuan yang pernah diterima.</p>
                    </div>
                </div>

            </div>

            <p class="auth-hero-note">
                NIK hanya bisa dipakai untuk satu akun. Jika NIK Anda sudah terdaftar,
                gunakan fitur <b>Lupa sandi</b> atau hubungi petugas desa.
            </p>

        </aside>

    </div>

</body>

</html>
