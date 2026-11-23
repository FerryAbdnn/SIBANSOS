
<header class="topbar">
    @php
        $user = auth()->user();
        $isAdmin = $user && $user->peran === 'admin';
    @endphp

    <div class="navbar-heading">
        <span class="navbar-eyebrow">
            SISTEM INFORMASI BANTUAN SOSIAL
        </span>

        <h2>@yield('heading', 'Dashboard')</h2>

        <p>
            {{ $isAdmin
                ? 'Kelola pendataan dan penyaluran bantuan sosial.'
                : 'Pantau pengajuan dan informasi bantuan sosial Anda.' }}
        </p>
    </div>

    <div class="navbar-actions">
        <div class="navbar-date">
            <span class="date-icon">▦</span>
            <span>{{ now()->format('d/m/Y') }}</span>
        </div>

        <div class="navbar-user">
            <div class="navbar-avatar">
                {{ strtoupper(substr($user->nama ?? 'U', 0, 1)) }}
            </div>

            <div class="navbar-user-info">
                <b>{{ $user->nama ?? 'Pengguna' }}</b>
                <small>{{ ucfirst($user->peran ?? 'pengguna') }}</small>
            </div>
        </div>
    </div>
</header>
