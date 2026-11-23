
@php
    $user = auth()->user();
@endphp

<aside class="sidebar">
    @include('components.brand-logo', [
        'href' => route('dashboard'),
        'class' => 'brand'
    ])

    <div class="side-label">OPERASIONAL</div>

    <a href="{{ route('dashboard') }}" class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span>▦</span> Dashboard
    </a>

    <a href="{{ route('admin.penerima.index') }}"
        class="menu-link {{ request()->routeIs('admin.penerima.*') ? 'active' : '' }}">
        <span>♙</span> Data Penerima
    </a>

    <a href="{{ route('admin.pengajuan.index') }}"
        class="menu-link {{ request()->routeIs('admin.pengajuan.*') ? 'active' : '' }}">
        <span>✓</span> Verifikasi Pengajuan
    </a>

    <a href="{{ route('admin.riwayat.index') }}"
        class="menu-link {{ request()->routeIs('admin.riwayat.*') ? 'active' : '' }}">
        <span>◷</span> Riwayat Penyaluran
    </a>

    <div class="side-label">PENGELOLAAN DATA</div>

    <a href="{{ route('admin.kategori.index') }}"
        class="menu-link {{ request()->routeIs('admin.kategori.*') ? 'active' : '' }}">
        <span>▤</span> Kategori Bantuan
    </a>

    <a href="{{ route('admin.jenis.index') }}"
        class="menu-link {{ request()->routeIs('admin.jenis.*') ? 'active' : '' }}">
        <span>▣</span> Jenis Bantuan
    </a>

    <a href="{{ route('admin.wilayah.index') }}"
        class="menu-link {{ request()->routeIs('admin.wilayah.*') ? 'active' : '' }}">
        <span>⌖</span> Manajemen Wilayah
    </a>

    <a href="{{ route('admin.perbaikan.index') }}"
        class="menu-link {{ request()->routeIs('admin.perbaikan.*') ? 'active' : '' }}">
        <span>✎</span> Perbaikan Data
    </a>

    <div class="side-label">SISTEM</div>

    <a href="{{ route('profile') }}"
        class="menu-link {{ request()->routeIs('profile') ? 'active' : '' }}">
        <span>♙</span> Profil
    </a>

    <div class="sidebar-bottom">
        <div class="user-chip">
            <div class="avatar">
                {{ strtoupper(substr($user->nama ?? 'A', 0, 1)) }}
            </div>
            <div>
                <b>{{ $user->nama ?? 'Administrator' }}</b>
                <small>{{ ucfirst($user->peran ?? 'admin') }}</small>
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
