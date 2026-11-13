<header class="topbar">
    <div class="navbar-heading">
        <span class="navbar-eyebrow">SISTEM INFORMASI BANTUAN SOSIAL</span>

        <h2>@yield('heading', 'Dashboard Admin')</h2>

        <p>Kelola pendataan dan penyaluran bantuan sosial.</p>
    </div>

    <div class="navbar-actions">
        <div class="navbar-date">
            <span class="date-icon">▦</span>
            <span>{{ now()->format('d/m/Y') }}</span>
        </div>

        <div class="navbar-user">
            <div class="navbar-avatar">
                {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
            </div>

            <div class="navbar-user-info">
                <b>{{ auth()->user()->nama }}</b>
            </div>
        </div>
    </div>
</header>