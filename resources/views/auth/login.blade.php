<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Masuk · SIBANSOS</title>

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

        <h1>Selamat datang kembali</h1>

        <p class="muted">
            Masuk untuk mengakses layanan SIBANSOS.
        </p>

        <form method="POST" action="{{ route('login.post') }}">

            @csrf

            <label>Email</label>

            <input type="email" name="email" value="{{ old('email') }}" required autofocus>

            <label>Kata sandi</label>

            <input type="password" name="password" required>

            @if ($errors->any())
                <p class="error">
                    {{ $errors->first() }}
                </p>
            @endif

            <button class="btn primary full" type="submit">
                Masuk ke akun
            </button>

        </form>

        <p class="center muted">
            Belum memiliki akun?
            <a href="{{ route('register') }}">Daftar sekarang</a>
        </p>

    </div>

</body>

</html>