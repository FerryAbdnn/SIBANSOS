{{--
|--------------------------------------------------------------------------
| Sidebar Admin
|--------------------------------------------------------------------------
--}}

@php
    $adminUser = auth()->user();

    $menunggu = 0;

    try {
        $q = \App\Models\PengajuanBantuan::where('status', 'diajukan');

        if ($adminUser->id_wilayah) {
            $ids = \App\Support\WilayahScope::ids((int) $adminUser->id_wilayah);
            $q->whereIn('data_penerima->id_wilayah', $ids);
        }

        $menunggu = $q->count();
    } catch (\Throwable $e) {
        $menunggu = 0;
    }

    $inisial = collect(explode(' ', trim($adminUser->nama)))
        ->filter()
        ->take(2)
        ->map(fn ($k) => strtoupper(substr($k, 0, 1)))
        ->implode('');
@endphp

<aside class="sidebar" id="sidebar">

    <a href="{{ route('dashboard') }}" class="brand">
        <span class="brand-mark">
            <x-icon name="shield" :size="20" />
        </span>

        <span>
            <b>SIBANSOS</b>
            <small>Pendataan Bantuan Sosial</small>
        </span>
    </a>

    <div class="nav-label">Operasional</div>

    <a href="{{ route('dashboard') }}"
       class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <x-icon name="grid" :size="17" />
        Dashboard
    </a>

    <a href="{{ route('admin.penerima.index') }}"
       class="nav-link {{ request()->routeIs('admin.penerima.*') ? 'active' : '' }}">
        <x-icon name="users" :size="17" />
        Data Penerima
    </a>

    <a href="{{ route('admin.pengajuan.index') }}"
       class="nav-link {{ request()->routeIs('admin.pengajuan.*') ? 'active' : '' }}">
        <x-icon name="check-square" :size="17" />
        Verifikasi Pengajuan

        @if ($menunggu > 0)
            <span class="nav-badge">{{ $menunggu }}</span>
        @endif
    </a>

    <a href="{{ route('admin.riwayat.index') }}"
       class="nav-link {{ request()->routeIs('admin.riwayat.*') ? 'active' : '' }}">
        <x-icon name="history" :size="17" />
        Riwayat Penyaluran
    </a>

    <a href="{{ route('admin.perbaikan.index') }}"
       class="nav-link {{ request()->routeIs('admin.perbaikan.*') ? 'active' : '' }}">
        <x-icon name="edit" :size="17" />
        Perbaikan Data
    </a>

    <div class="nav-label">Master Data</div>

    <a href="{{ route('admin.kategori.index') }}"
       class="nav-link {{ request()->routeIs('admin.kategori.*') ? 'active' : '' }}">
        <x-icon name="layers" :size="17" />
        Kategori Bantuan
    </a>

    <a href="{{ route('admin.jenis.index') }}"
       class="nav-link {{ request()->routeIs('admin.jenis.*') ? 'active' : '' }}">
        <x-icon name="list" :size="17" />
        Jenis Bantuan
    </a>

    <div class="nav-label">Sistem</div>

    <a href="{{ route('profile') }}"
       class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}">
        <x-icon name="user" :size="17" />
        Profil Saya
    </a>

    <div class="sidebar-bottom">
        <div class="sidebar-user">
            <div class="avatar">{{ $inisial ?: 'A' }}</div>

            <div>
                <b>{{ $adminUser->nama }}</b>
                <small>Admin Desa</small>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf

            <button type="submit" class="logout-btn">
                <x-icon name="logout" :size="15" />
                Keluar
            </button>
        </form>
    </div>

</aside>
