{{--
|--------------------------------------------------------------------------
| Topbar
|--------------------------------------------------------------------------
| Variabel opsional:
|   $topbarTitle       -> judul kecil di kiri (default dari @yield('heading'))
|   $topbarSearch      -> placeholder pencarian
|   $topbarSearchRoute -> route tujuan form pencarian
|--------------------------------------------------------------------------
--}}

@php
    $topbarTitle = $topbarTitle ?? trim($__env->yieldContent('heading'));

    $isAdmin = auth()->user()->peran === 'admin';

    $searchPlaceholder = $topbarSearch
        ?? ($isAdmin
            ? 'Cari NIK, nama, atau kode penerima...'
            : 'Cari riwayat atau pengajuan saya...');

    $searchRoute = $topbarSearchRoute
        ?? ($isAdmin ? route('admin.penerima.index') : null);

    $searchValue = $isAdmin ? request('q') : null;
@endphp

<header class="topbar">

    <button type="button" class="icon-btn sidebar-toggle" id="sidebarToggle" aria-label="Buka menu">
        <x-icon name="list" :size="17" />
    </button>

    <div class="topbar-title">
        {{ $topbarTitle ?: 'SIBANSOS' }}
    </div>

    <div class="topbar-actions">

        @if ($searchRoute)
            <form method="GET" action="{{ $searchRoute }}" class="search-box" role="search">
                <x-icon name="search" :size="15" />

                <input type="search" name="q" value="{{ $searchValue }}"
                       placeholder="{{ $searchPlaceholder }}" aria-label="Pencarian">
            </form>
        @else
            <div class="search-box" aria-hidden="true">
                <x-icon name="search" :size="15" />

                <input type="search" placeholder="{{ $searchPlaceholder }}" disabled>
            </div>
        @endif

        <button type="button" class="icon-btn" aria-label="Notifikasi">
            <x-icon name="bell" :size="17" />
            <span class="dot"></span>
        </button>

        <a href="{{ route('profile') }}" class="icon-btn" aria-label="Pengaturan">
            <x-icon name="settings" :size="17" />
        </a>

    </div>

</header>
