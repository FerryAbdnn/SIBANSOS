@extends('layouts.admin')

@section('title', 'Profil Saya')
@section('heading', 'Profil Saya')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile-admin.css') }}">
@endpush

@section('content')

    @php
        $inisial = collect(preg_split('/\s+/', trim($user->nama)))
            ->filter()
            ->take(2)
            ->map(fn($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
            ->implode('');

        $hierarkiWilayah = collect([
            $wilayah?->induk?->induk,
            $wilayah?->induk,
            $wilayah,
        ])->filter()->unique('id_wilayah')->values();

        $labelWilayah = [
            'desa' => 'Desa',
            'kelurahan' => 'Kelurahan',
            'rw' => 'RW',
            'rt' => 'RT',
            'kecamatan' => 'Kecamatan',
        ];
    @endphp

    <div class="profil-admin">

        {{-- Header profil --}}
        <div class="profil-header">
            <div>
                <span class="profil-eyebrow">PENGATURAN AKUN</span>
                <h2>Profil Saya</h2>
                <p>
                    Informasi akun, wilayah penugasan, keamanan,
                    dan aktivitas administrasi Anda.
                </p>
            </div>
        </div>

        {{-- Kartu identitas --}}
        <section class="profil-identity">
            <div class="profil-avatar">
                {{ $inisial ?: 'AD' }}
            </div>

            <div class="profil-identity-info">
                <h3>{{ $user->nama }}</h3>

                <div class="profil-identity-meta">
                    <span class="profil-role">
                        <i class="fa-solid fa-user-shield"></i>
                        Administrator
                    </span>

                    <span>
                        <i class="fa-solid fa-location-dot"></i>
                        {{ $wilayah?->nama ?? 'Wilayah belum ditentukan' }}
                    </span>
                </div>

                <p>
                    <i class="fa-regular fa-envelope"></i>
                    {{ $user->email }}
                </p>
            </div>

            <div class="profil-account-status {{ $user->aktif ? 'is-active' : 'is-inactive' }}">
                <span></span>
                {{ $user->aktif ? 'Akun Aktif' : 'Akun Nonaktif' }}
            </div>
        </section>

        {{-- Statistik singkat --}}
        <div class="profil-stat-grid">

            <div class="profil-stat-card">
                <div class="profil-stat-icon">
                    <i class="fa-solid fa-people-group"></i>
                </div>
                <div>
                    <span>Total Penerima</span>
                    <strong>{{ number_format($jumlahPenerima, 0, ',', '.') }}</strong>
                    <small>Dalam cakupan wilayah</small>
                </div>
            </div>

            <div class="profil-stat-card">
                <div class="profil-stat-icon">
                    <i class="fa-solid fa-map"></i>
                </div>
                <div>
                    <span>Wilayah RW</span>
                    <strong>{{ number_format($jumlahRw, 0, ',', '.') }}</strong>
                    <small>Wilayah terjangkau</small>
                </div>
            </div>

            <div class="profil-stat-card">
                <div class="profil-stat-icon">
                    <i class="fa-solid fa-house-chimney"></i>
                </div>
                <div>
                    <span>Wilayah RT</span>
                    <strong>{{ number_format($jumlahRt, 0, ',', '.') }}</strong>
                    <small>Wilayah terjangkau</small>
                </div>
            </div>

        </div>

        {{-- Konten dua kolom --}}
        <div class="profil-content-grid">

            {{-- Kolom kiri --}}
            <div class="profil-column">

                {{-- Informasi akun --}}
                <section class="profil-card">
                    <div class="profil-card-heading">
                        <div class="profil-card-icon">
                            <i class="fa-solid fa-id-badge"></i>
                        </div>
                        <div>
                            <h3>Informasi Akun</h3>
                            <p>Informasi identitas akun administrator.</p>
                        </div>
                    </div>

                    <div class="profil-detail-list">

                        <div class="profil-detail-item">
                            <span>Nama lengkap</span>
                            <strong>{{ $user->nama }}</strong>
                        </div>

                        <div class="profil-detail-item">
                            <span>Alamat email</span>
                            <strong>{{ $user->email }}</strong>
                        </div>

                        <div class="profil-detail-item">
                            <span>ID pengguna</span>
                            <strong>#{{ $user->id_user }}</strong>
                        </div>

                        <div class="profil-detail-item">
                            <span>Hak akses</span>
                            <strong>Administrator</strong>
                        </div>

                        <div class="profil-detail-item">
                            <span>Tanggal bergabung</span>
                            <strong>
                                {{ $user->created_at?->translatedFormat('d F Y') ?? 'Tidak tersedia' }}
                            </strong>
                        </div>

                        <div class="profil-detail-item">
                            <span>Status akun</span>
                            <strong class="{{ $user->aktif ? 'text-active' : 'text-inactive' }}">
                                {{ $user->aktif ? 'Aktif' : 'Nonaktif' }}
                            </strong>
                        </div>

                    </div>
                </section>

                {{-- Wilayah penugasan --}}
                <section class="profil-card">
                    <div class="profil-card-heading">
                        <div class="profil-card-icon">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <div>
                            <h3>Wilayah Penugasan</h3>
                            <p>Cakupan wilayah kerja administrator.</p>
                        </div>
                    </div>

                    @if ($wilayah)
                        <div class="profil-region-list">
                            @foreach ($hierarkiWilayah as $item)
                                <div class="profil-region-item">
                                    <div class="profil-region-marker">
                                        <i class="fa-solid fa-house-flag"></i>
                                    </div>
                                    <div>
                                        <span>
                                            {{ $labelWilayah[strtolower($item->tipe)] ?? ucfirst($item->tipe) }}
                                        </span>
                                        <strong>{{ $item->nama }}</strong>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="profil-region-summary">
                            <div>
                                <span>RW dalam cakupan</span>
                                <strong>{{ $jumlahRw }} RW</strong>
                            </div>
                            <div>
                                <span>RT dalam cakupan</span>
                                <strong>{{ $jumlahRt }} RT</strong>
                            </div>
                            <div>
                                <span>Penerima terdata</span>
                                <strong>{{ number_format($jumlahPenerima, 0, ',', '.') }} orang</strong>
                            </div>
                        </div>
                    @else
                        <div class="profil-empty">
                            <i class="fa-solid fa-map-location-dot"></i>
                            <strong>Wilayah belum ditentukan</strong>
                            <span>Hubungi pengelola untuk memperbarui penugasan wilayah.</span>
                        </div>
                    @endif
                </section>

            </div>

            {{-- Kolom kanan --}}
            <div class="profil-column">

                {{-- Keamanan akun --}}
                <section class="profil-card">
                    <div class="profil-card-heading">
                        <div class="profil-card-icon">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <h3>Keamanan Akun</h3>
                            <p>Informasi perlindungan akun administrator.</p>
                        </div>
                    </div>

                    <div class="profil-security-list">

                        <div class="profil-security-item">
                            <div class="profil-security-icon">
                                <i class="fa-solid fa-key"></i>
                            </div>
                            <div>
                                <strong>Perlindungan kata sandi</strong>
                                <span>Kata sandi disimpan dalam bentuk hash.</span>
                            </div>
                            <i class="fa-solid fa-circle-check security-check"></i>
                        </div>

                        <div class="profil-security-item">
                            <div class="profil-security-icon">
                                <i class="fa-solid fa-user-shield"></i>
                            </div>
                            <div>
                                <strong>Hak akses administrator</strong>
                                <span>Akses halaman dibatasi berdasarkan peran akun.</span>
                            </div>
                            <i class="fa-solid fa-circle-check security-check"></i>
                        </div>

                        <div class="profil-security-item">
                            <div class="profil-security-icon">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <div>
                                <strong>Waktu login terakhir</strong>
                                <span>Belum tersedia dalam pencatatan aplikasi.</span>
                            </div>
                        </div>

                    </div>

                    <div class="profil-security-note">
                        <i class="fa-solid fa-circle-info"></i>
                        Jaga kerahasiaan kata sandi dan jangan membagikan
                        akses administrator kepada pihak lain.
                    </div>
                </section>

                {{-- Aktivitas admin --}}
                <section class="profil-card">
                    <div class="profil-card-heading">
                        <div class="profil-card-icon">
                            <i class="fa-solid fa-chart-line"></i>
                        </div>
                        <div>
                            <h3>Aktivitas Saya</h3>
                            <p>Rekap aktivitas yang tercatat atas akun ini.</p>
                        </div>
                    </div>

                    <div class="profil-activity-stats">
                        <div>
                            <span>Pengajuan dibuat</span>
                            <strong>{{ number_format($jumlahPengajuan, 0, ',', '.') }}</strong>
                        </div>
                        <div>
                            <span>Pengajuan diverifikasi</span>
                            <strong>{{ number_format($jumlahVerifikasi, 0, ',', '.') }}</strong>
                        </div>
                        <div>
                            <span>Pengajuan ditolak</span>
                            <strong>{{ number_format($jumlahPenolakan, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="profil-activity-heading">
                        <strong>Aktivitas terbaru</strong>
                        <span>5 aktivitas terakhir</span>
                    </div>

                    @forelse ($aktivitasTerbaru as $aktivitas)
                        @php
                            $labelAktivitas = match ($aktivitas->aksi) {
                                'pengajuan' => 'Membuat pengajuan bantuan',
                                'verifikasi' => 'Memverifikasi pengajuan',
                                'tolak' => 'Menolak pengajuan',
                                default => ucfirst(str_replace('_', ' ', $aktivitas->aksi)),
                            };
                        @endphp

                        <div class="profil-activity-item">
                            <div class="profil-activity-dot"></div>
                            <div>
                                <strong>{{ $labelAktivitas }}</strong>
                                <span>
                                    {{ $aktivitas->tabel }}
                                    @if ($aktivitas->id_record)
                                        · ID {{ $aktivitas->id_record }}
                                    @endif
                                </span>
                                <small>
                                    {{ $aktivitas->created_at?->translatedFormat('d M Y, H:i') ?? 'Waktu tidak tersedia' }}
                                </small>
                            </div>
                        </div>
                    @empty
                        <div class="profil-empty profil-empty-activity">
                            <i class="fa-regular fa-clipboard"></i>
                            <strong>Belum ada aktivitas</strong>
                            <span>Aktivitas administrator akan muncul di sini ketika tercatat.</span>
                        </div>
                    @endforelse

                </section>

            </div>
        </div>

    </div>

@endsection