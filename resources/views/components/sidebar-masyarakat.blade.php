
@php
    $user = auth()->user();
@endphp

<aside class="sidebar">

    @include('components.brand-logo', [
        'href' => route('dashboard'),
        'class' => 'brand'
    ])

    <div class="side-label">MENU UTAMA</div>

    <a href="{{ route('dashboard') }}"
        class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span>▦</span>
        Dashboard
    </a>

    <div class="side-label">LAYANAN BANTUAN</div>

    <a href="{{ route('masyarakat.pengajuan.create') }}"
        class="menu-link {{ request()->routeIs('masyarakat.pengajuan.*') ? 'active' : '' }}">
        <span>＋</span>
        Pengajuan Bantuan
    </a>

    <a href="{{ route('masyarakat.status') }}"
        class="menu-link {{ request()->routeIs('masyarakat.status') ? 'active' : '' }}">
        <span>◷</span>
        Status Pengajuan
    </a>

    <a href="{{ route('masyarakat.perbaikan.create') }}"
        class="menu-link {{ request()->routeIs('masyarakat.perbaikan.*') ? 'active' : '' }}">
        <span>✎</span>
        Perbaikan Data
    </a>

    <a href="{{ route('masyarakat.riwayat') }}"
        class="menu-link {{ request()->routeIs('masyarakat.riwayat') ? 'active' : '' }}">
        <span>◷</span>
        Riwayat Bantuan
    </a>

    <div class="side-label">AKUN SAYA</div>

    <a href="{{ route('profile') }}"
        class="menu-link {{ request()->routeIs('profile') ? 'active' : '' }}">
        <span>♙</span>
        Profil
    </a>

    <div class="sidebar-bottom">

        <div class="user-chip">
            <div class="avatar">
                {{ strtoupper(substr($user->nama ?? 'M', 0, 1)) }}
            </div>

            <div>
                <b>{{ $user->nama ?? 'Masyarakat' }}</b>
                <small>{{ ucfirst($user->peran ?? 'masyarakat') }}</small>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout">
                ↪ Keluar
            </button>
        </form>

    </div>

</aside>
