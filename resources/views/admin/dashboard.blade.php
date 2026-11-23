@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('heading', 'Dashboard Admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard-admin.css') }}">
@endpush

@section('content')

    <div class="admin-dashboard">

        {{-- HEADER DASHBOARD --}}
        <section class="admin-dashboard-header">
            <div class="admin-dashboard-heading">
                <span class="admin-dashboard-eyebrow">
                    RINGKASAN PENDATAAN
                </span>

                <h1>
                    Selamat datang, {{ auth()->user()->nama }}
                    <span>👋</span>
                </h1>

                <p>
                    Pantau dan kelola data calon penerima bantuan sosial
                    secara terstruktur dan transparan.
                </p>
            </div>

            <div class="admin-dashboard-actions">
                <a href="{{ route('admin.penerima.export') }}" class="admin-dashboard-btn admin-dashboard-btn-outline">
                    <i class="fa-solid fa-file-export"></i>
                    Export Data
                </a>

                <a href="{{ route('admin.penerima.create') }}" class="admin-dashboard-btn admin-dashboard-btn-primary">
                    <i class="fa-solid fa-plus"></i>
                    Tambah Penerima
                </a>
            </div>
        </section>

        {{-- STATISTIK --}}
        <section class="admin-dashboard-stats">

            <article class="admin-dashboard-stat">
                <div class="admin-dashboard-stat-icon green">
                    <i class="fa-solid fa-users"></i>
                </div>

                <div class="admin-dashboard-stat-info">
                    <span>Total Penerima</span>
                    <strong>{{ number_format($stats['total']) }}</strong>
                    <small>Seluruh data wilayah tugas</small>
                </div>
            </article>

            <article class="admin-dashboard-stat">
                <div class="admin-dashboard-stat-icon blue">
                    <i class="fa-solid fa-circle-check"></i>
                </div>

                <div class="admin-dashboard-stat-info">
                    <span>Terverifikasi</span>
                    <strong>{{ number_format($stats['terverifikasi']) }}</strong>
                    <small>Pengajuan telah disetujui</small>
                </div>
            </article>

            <article class="admin-dashboard-stat">
                <div class="admin-dashboard-stat-icon orange">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div class="admin-dashboard-stat-info">
                    <span>Menunggu Verifikasi</span>
                    <strong>{{ number_format($stats['diajukan']) }}</strong>
                    <small>Pengajuan perlu ditinjau</small>
                </div>
            </article>

            <article class="admin-dashboard-stat">
                <div class="admin-dashboard-stat-icon red">
                    <i class="fa-solid fa-user-slash"></i>
                </div>

                <div class="admin-dashboard-stat-info">
                    <span>Penerima Nonaktif</span>
                    <strong>{{ number_format($stats['nonaktif']) }}</strong>
                    <small>Data penerima berstatus nonaktif</small>
                </div>
            </article>

        </section>

        {{-- DATA PENERIMA TERBARU --}}
        <section class="admin-dashboard-panel">

            <div class="admin-dashboard-panel-head">
                <div>
                    <span class="admin-dashboard-section-label">
                        PEMBARUAN DATA
                    </span>

                    <h2>Data Penerima Terbaru</h2>

                    <p>
                        Daftar penerima yang terakhir ditambahkan
                        dalam wilayah tugas Anda.
                    </p>
                </div>

                <a href="{{ route('admin.penerima.index') }}" class="admin-dashboard-link">
                    Lihat Semua
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="admin-dashboard-table-wrap">
                <table class="admin-dashboard-table">
                    <thead>
                        <tr>
                            <th>Nama Penerima</th>
                            <th>Wilayah</th>
                            <th>Status</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($recent as $p)
                            @php
                                $statusTampil = $p->status === 'nonaktif'
                                    ? 'nonaktif'
                                    : ($p->pengajuanTerbaru?->status ?? $p->status);
                            @endphp

                            <tr>
                                <td>
                                    <div class="admin-dashboard-person">
                                        <div class="admin-dashboard-avatar">
                                            {{ strtoupper(substr($p->nama ?? 'P', 0, 1)) }}
                                        </div>

                                        <div>
                                            <strong>{{ $p->nama }}</strong>
                                            <small>
                                                NIK
                                                ••••••••{{ substr($p->nik_decrypted ?? '', -4) }}
                                            </small>
                                        </div>
                                    </div>
                                </td>


                                <td>
                                    @php
                                        $wilayah = $p->wilayah;
                                        $rw = $wilayah?->induk;
                                        $desa = $rw?->induk;

                                        $tipe = strtolower($wilayah?->tipe ?? '');

                                        $rtNama = $tipe === 'rt'
                                            ? $wilayah?->nama
                                            : null;

                                        $rwNama = $tipe === 'rw'
                                            ? $wilayah?->nama
                                            : ($rw?->nama);

                                        $desaNama = $tipe === 'desa' || $tipe === 'kelurahan'
                                            ? $wilayah?->nama
                                            : ($desa?->nama);
                                    @endphp

                                    <div class="admin-dashboard-region">
                                        <i class="fa-solid fa-location-dot"></i>

                                        <div>
                                            <strong>
                                                {{ $rtNama ? 'RT ' . $rtNama : 'RT -' }}
                                                ·
                                                {{ $rwNama ? 'RW ' . $rwNama : 'RW -' }}
                                            </strong>

                                            <small>
                                                {{ $desaNama ?? 'Desa/Kelurahan belum tersedia' }}
                                            </small>
                                        </div>
                                    </div>
                                </td>


                                <td>
                                    <span class="admin-dashboard-status {{ $statusTampil }}">
                                        {{ ucfirst($statusTampil) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $p->created_at?->format('d/m/Y') ?? '-' }}
                                </td>

                                <td>
                                    <a href="{{ route('admin.penerima.show', $p) }}" class="admin-dashboard-detail-link">
                                        Detail
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="admin-dashboard-empty">
                                        <i class="fa-regular fa-folder-open"></i>
                                        <strong>Belum ada data penerima</strong>
                                        <span>Data penerima terbaru akan ditampilkan di sini.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </section>


        {{-- KATEGORI BANTUAN --}}
        <section class="admin-dashboard-panel">

            <div class="admin-dashboard-panel-head">
                <div>
                    <span class="admin-dashboard-section-label">
                        PROGRAM BANTUAN
                    </span>

                    <h2>Kategori Bantuan</h2>

                    <p>
                        Pilih kategori untuk melihat detail dan daftar
                        program bantuan yang tersedia.
                    </p>
                </div>

                <a href="{{ route('admin.kategori.index') }}" class="admin-dashboard-link">
                    Kelola Kategori
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            <div class="admin-dashboard-category-grid">

                @forelse ($kategori as $item)
                    <a href="{{ route('admin.kategori.show', $item) }}" class="admin-dashboard-category">

                        <div class="admin-dashboard-category-icon">
                            <i class="fa-solid fa-hand-holding-heart"></i>
                        </div>

                        <div class="admin-dashboard-category-info">
                            <strong>{{ $item->nama }}</strong>

                            <span>
                                {{ $item->jenis_count }}
                                jenis bantuan tersedia
                            </span>

                            <div class="admin-dashboard-category-programs">
                                @forelse ($item->jenis as $jenis)
                                    <span>{{ $jenis->nama_program }}</span>
                                @empty
                                    <span>Belum ada program bantuan</span>
                                @endforelse
                            </div>
                        </div>

                        <i class="fa-solid fa-chevron-right admin-dashboard-category-arrow"></i>

                    </a>
                @empty
                    <div class="admin-dashboard-empty">
                        <i class="fa-regular fa-folder-open"></i>
                        <strong>Belum ada kategori bantuan</strong>
                    </div>
                @endforelse

            </div>

        </section>

    </div>

@endsection