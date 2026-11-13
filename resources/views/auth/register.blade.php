<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Daftar · SIBANSOS</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body class="auth-bg">

    <div class="auth-card">

        <a class="brand" href="/">
            <span class="brand-mark">S</span>

            <span>
                <b>SIBANSOS</b>
                <small>Sistem Pendataan Bantuan Sosial</small>
            </span>
        </a>

        <h1>Buat akun masyarakat</h1>

        <p class="muted">
            Daftarkan akun untuk mengajukan bantuan sosial.
        </p>

        <form method="POST" action="{{ route('register.post') }}">
            @csrf

            <label>Nama lengkap</label>
            <input name="nama" value="{{ old('nama') }}" required>

            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>

            <label>Kata sandi</label>
            <input type="password" name="password" required>

            <label>Konfirmasi kata sandi</label>
            <input type="password" name="password_confirmation" required>

            @if($errors->any())
                <p class="error">
                    {{ $errors->first() }}
                </p>
            @endif

            <button class="btn primary full">
                Buat akun
            </button>
        </form>

        <p class="center muted">
            Sudah punya akun?
            <a href="{{ route('login') }}">Masuk</a>
        </p>

    </div>

</body>

</html>