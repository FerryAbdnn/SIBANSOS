{{--
|--------------------------------------------------------------------------
| Sidebar Masyarakat
|--------------------------------------------------------------------------
--}}

@php
    $wargaUser = auth()->user();

    $penerimaWarga = null;

    try {
        $penerimaWarga = \App\Models\Penerima::with('riwayat.jenis.kategori')
            ->where('id_user', $wargaUser->id_user)
            ->first();
    } catch (\Throwable $e) {
        $penerimaWarga = null;
    }

    $kategoriWarga = optional(
        optional(optional($penerimaWarga)->riwayat)->first()
    )->jenis;

    $labelKategori = optional(optional($kategoriWarga)->kategori)->nama;

    $inisial = collect(explode(' ', trim($wargaUser->nama)))
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
            <small>Pendaftaran Bantuan Sosial</small>
        </span>
    </a>

    <div class="nav-label">Layanan Saya</div>

    <a href="{{ route('dashboard') }}"
       class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <x-icon name="grid" :size="17" />
        Dashboard
    </a>

    <a href="{{ route('masyarakat.pengajuan.create') }}"
       class="nav-link {{ request()->routeIs('masyarakat.pengajuan.*') ? 'active' : '' }}">
        <x-icon name="send" :size="17" />
        Pengajuan Bantuan
    </a>

    <a href="{{ route('masyarakat.status') }}"
       class="nav-link {{ request()->routeIs('masyarakat.status') ? 'active' : '' }}">
        <x-icon name="file" :size="17" />
        Status Pengajuan
    </a>

    <a href="{{ route('masyarakat.riwayat') }}"
       class="nav-link {{ request()->routeIs('masyarakat.riwayat') ? 'active' : '' }}">
        <x-icon name="history" :size="17" />
        Riwayat Bantuan
    </a>

    <a href="{{ route('masyarakat.perbaikan.create') }}"
       class="nav-link {{ request()->routeIs('masyarakat.perbaikan.*') ? 'active' : '' }}">
        <x-icon name="edit" :size="17" />
        Perbaikan Data
    </a>

    <div class="nav-label">Akun</div>

    <a href="{{ route('profile') }}"
       class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}">
        <x-icon name="user" :size="17" />
        Profil Saya
    </a>

    <div class="sidebar-bottom">
        <div class="sidebar-user">
            <div class="avatar">{{ $inisial ?: 'W' }}</div>

            <div>
                <b>{{ $penerimaWarga->nama ?? $wargaUser->nama }}</b>
                <small>
                    {{ $labelKategori ? 'Penerima ' . $labelKategori : 'Masyarakat' }}
                </small>
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
