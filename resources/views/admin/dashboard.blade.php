@extends('layouts.admin')

@section('title', 'Dashboard Admin')
@section('heading', 'Dashboard Admin')

@section('content')

    {{-- Sambutan --}}
    <section class="welcome">
        <div class="welcome-text">
            <span class="eyebrow light">RINGKASAN PENDATAAN</span>

            <h1>
                Selamat datang, {{ auth()->user()->nama }} 👋
            </h1>

            <p>
                Pantau dan kelola data calon penerima bantuan sosial
                secara terstruktur dan transparan.
            </p>
        </div>

        <a href="{{ route('admin.penerima.create') }}" class="btn white">
            <span>＋</span> Tambah Penerima
        </a>
    </section>

    {{-- Statistik --}}
    <section class="dashboard-stats">

        <div class="dashboard-stat">
            <div class="dashboard-stat-icon green">♙</div>
            <div>
                <span>Total Penerima</span>
                <strong>{{ number_format($stats['total']) }}</strong>
                <small>Seluruh data wilayah tugas</small>
            </div>
        </div>

        <div class="dashboard-stat">
            <div class="dashboard-stat-icon orange">◷</div>
            <div>
                <span>Menunggu Verifikasi</span>
                <strong>{{ number_format($stats['diajukan']) }}</strong>
                <small>Pengajuan perlu ditinjau</small>
            </div>
        </div>

        <div class="dashboard-stat">
            <div class="dashboard-stat-icon blue">✓</div>
            <div>
                <span>Terverifikasi</span>
                <strong>{{ number_format($stats['terverifikasi']) }}</strong>
                <small>Data telah disetujui</small>
            </div>
        </div>

        <div class="dashboard-stat">
            <div class="dashboard-stat-icon red">✕</div>
            <div>
                <span>Ditolak</span>
                <strong>{{ number_format($stats['ditolak']) }}</strong>
                <small>Pengajuan tidak disetujui</small>
            </div>
        </div>

    </section>

    {{-- Data Penerima Terbaru --}}
    <section class="panel dashboard-panel">

        <div class="panel-head">
            <div>
                <h3>Data Penerima Terbaru</h3>
                <p>Daftar calon penerima bantuan sosial yang terbaru.</p>
            </div>

            <a href="{{ route('admin.penerima.index') }}">
                Lihat Semua →
            </a>
        </div>

        <div class="table-wrap">
            <table>
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
                        <tr>
                            <td>
                                <b>{{ $p->nama }}</b>
                                <small class="sub">
                                    NIK
                                    ••••••••{{ substr(decrypt($p->nik), -4) }}
                                </small>
                            </td>

                            <td>
                                {{ $p->wilayah->nama ?? '-' }}
                            </td>

                            <td>
                                <span class="status {{ $p->status }}">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </td>

                            <td>
                                {{ $p->created_at->format('d/m/Y') }}
                            </td>

                            <td>
                                <a href="{{ route('admin.penerima.show', $p) }}">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty">
                                Belum ada data penerima.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </section>

    {{-- Kategori Bantuan --}}
    <section class="panel dashboard-panel">

        <div class="panel-head">
            <div>
                <h3>Kategori Bantuan</h3>
                <p>Ringkasan kategori program bantuan yang tersedia.</p>
            </div>

            <a href="{{ route('admin.kategori.index') }}">
                Kelola Kategori →
            </a>
        </div>

        <div class="category-grid">
            @forelse ($kategori as $item)
                <div class="category-item">
                    <div class="category-icon">▤</div>

                    <div>
                        <b>{{ $item->nama_kategori }}</b>
                        <small>
                            {{ $item->jenis_count }} jenis bantuan
                        </small>
                    </div>
                </div>
            @empty
                <p class="empty">
                    Belum ada kategori bantuan.
                </p>
            @endforelse
        </div>

    </section>

@endsection