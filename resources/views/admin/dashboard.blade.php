@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')

    {{-- SAMBUTAN --}}
    <section class="hero-card">

        <div>
            <span class="eyebrow">Ringkasan pendataan</span>

            <h2>Selamat datang, {{ auth()->user()->nama }}</h2>

            <p>
                Pantau dan kelola data calon penerima bantuan sosial secara
                terstruktur dan transparan.
            </p>
        </div>

        <a href="{{ route('admin.penerima.create') }}" class="btn">
            <x-icon name="plus" :size="15" />
            Tambah Penerima
        </a>

    </section>

    {{-- KPI --}}
    <div class="kpi-grid">

        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Total penerima</span>
                <span class="kpi-icon"><x-icon name="users" :size="16" /></span>
            </div>

            <strong class="kpi-value">{{ number_format($stats['total'], 0, ',', '.') }}</strong>
            <small class="kpi-caption">Seluruh data wilayah tugas</small>
        </div>

        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Terverifikasi</span>
                <span class="kpi-icon"><x-icon name="check-square" :size="16" /></span>
            </div>

            <strong class="kpi-value">{{ number_format($stats['terverifikasi'], 0, ',', '.') }}</strong>
            <small class="kpi-caption">
                @if ($stats['total'] > 0)
                    {{ number_format($stats['terverifikasi'] / $stats['total'] * 100, 1, ',', '.') }}% dari total
                @else
                    Belum ada data
                @endif
            </small>
        </div>

        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Menunggu verifikasi</span>
                <span class="kpi-icon amber"><x-icon name="clock" :size="16" /></span>
            </div>

            <strong class="kpi-value">{{ number_format($stats['diajukan'], 0, ',', '.') }}</strong>
            <small class="kpi-caption">
                {{ $stats['diajukan'] > 0 ? 'Perlu tindakan petugas' : 'Tidak ada data menunggu' }}
            </small>
        </div>

        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Nonaktif</span>
                <span class="kpi-icon rose"><x-icon name="x" :size="16" /></span>
            </div>

            <strong class="kpi-value">{{ number_format($stats['nonaktif'], 0, ',', '.') }}</strong>
            <small class="kpi-caption">Punya riwayat bantuan</small>
        </div>

    </div>

    {{-- ALERT --}}
    @if ($stats['diajukan'] > 0)
        <div class="alert alert-warning">
            <x-icon name="alert" :size="16" />

            <div>
                <strong>{{ $stats['diajukan'] }} data menunggu verifikasi.</strong>
                Selesaikan verifikasi agar status penerima tetap akurat.
                <a href="{{ route('admin.pengajuan.index') }}" style="font-weight:700;text-decoration:underline;">
                    Lihat daftar
                </a>
            </div>
        </div>
    @endif

    {{-- GRAFIK + PANEL --}}
    <div class="grid-2" style="margin-bottom:18px;">

        <div class="card">
            <div class="card-head">
                <div>
                    <h3>Penerima per kategori bantuan</h3>
                    <p>Berdasarkan riwayat penyaluran yang sudah tercatat</p>
                </div>

                <div class="card-head-actions">
                    <span class="badge badge-outline">
                        <x-icon name="calendar" :size="12" />
                        Semester ini
                    </span>
                </div>
            </div>

            <div class="card-body">
                @php
                    $maks = max(1, (int) $sebaranKategori->max('jumlah'));
                @endphp

                @if ($sebaranKategori->where('jumlah', '>', 0)->count() > 0)
                    <div class="bars">
                        @foreach ($sebaranKategori as $s)
                            <div class="bar-col">
                                <b>{{ number_format($s->jumlah, 0, ',', '.') }}</b>

                                @php
                                    $tinggi = (int) $s->jumlah > 0
                                        ? max(10, round($s->jumlah / $maks * 130))
                                        : 3;
                                @endphp

                                <div class="bar {{ (int) $s->jumlah === 0 ? 'empty' : ($loop->first ? 'top' : '') }}"
                                     style="height: {{ $tinggi }}px;"></div>

                                <span>{{ $s->nama }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty">
                        <div class="empty-icon"><x-icon name="bar-chart" :size="20" /></div>
                        <strong>Belum ada penyaluran tercatat</strong>
                        <p>Grafik akan muncul setelah riwayat bantuan dicatat.</p>
                    </div>
                @endif
            </div>

            <div class="card-foot">
                <x-icon name="info" :size="13" />
                Total {{ number_format($stats['total'], 0, ',', '.') }} penerima terdata pada wilayah tugas Anda.
            </div>
        </div>

        <div class="stack">

            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>Sebaran wilayah</h3>
                        <p>{{ $sebaranWilayah->count() }} wilayah penugasan</p>
                    </div>
                </div>

                <div class="card-body" style="padding-top:6px;padding-bottom:6px;">
                    <div class="dot-list">
                        @forelse ($sebaranWilayah as $w)
                            <div class="dot-row">
                                <i style="opacity: {{ 1 - $loop->index * 0.2 }};"></i>
                                <span>{{ $w->label }}</span>
                                <b>{{ number_format($w->jumlah, 0, ',', '.') }}</b>
                            </div>
                        @empty
                            <div class="dot-row">
                                <span class="muted">Belum ada data wilayah.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="cta-card">
                <span class="eyebrow">Menunggu tindakan</span>

                <b>{{ number_format($stats['diajukan'], 0, ',', '.') }} data</b>

                <p>
                    @if ($stats['diajukan'] > 0)
                        Perlu ditindaklanjuti petugas
                    @else
                        Tidak ada data menunggu
                    @endif
                </p>

                <a href="{{ route('admin.penerima.index', ['status' => 'diajukan']) }}" class="btn">
                    Tinjau data
                </a>
            </div>

        </div>

    </div>

    {{-- TABEL DATA MENUNGGU VERIFIKASI --}}
    <div class="card">

        <div class="card-head">
            <div>
                <h3>Data menunggu verifikasi</h3>
                <p>
                    @if ($penerimaMenunggu->isEmpty())
                        Semua data sudah diverifikasi &middot; diurutkan dari yang terbaru
                    @else
                        {{ number_format($stats['diajukan'], 0, ',', '.') }} data perlu ditinjau &middot;
                        diurutkan dari yang terbaru
                    @endif
                </p>
            </div>

            <div class="card-head-actions">
                <a href="{{ route('admin.penerima.index', ['status' => 'diajukan']) }}" class="link-quiet">
                    Lihat semua
                    <x-icon name="arrow-right" :size="13" />
                </a>
            </div>
        </div>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>RT / RW</th>
                        <th>Kategori</th>
                        <th>Terdaftar</th>
                        <th class="num">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($penerimaMenunggu as $p)
                        @php
                            $kode = 'PNR-' . ($p->created_at?->format('Y') ?? date('Y'))
                                . '-' . str_pad($p->id_penerima, 5, '0', STR_PAD_LEFT);

                            $rt = $p->wilayah;
                            $rw = $rt && $rt->id_induk
                                ? \App\Models\Wilayah::find($rt->id_induk)
                                : null;
                        @endphp

                        <tr>
                            <td class="mono">{{ $kode }}</td>

                            <td class="strong">{{ $p->nama }}</td>

                            <td class="mono">{{ $p->nik_masked }}</td>

                            <td>{{ $rt->label ?? '-' }} / {{ $rw->label ?? '-' }}</td>

                            <td>
                                <span class="badge badge-gray">
                                    {{ $p->jenisBantuan->kategori->nama ?? 'Belum ditetapkan' }}
                                </span>
                            </td>

                            <td class="muted">{{ $p->created_at?->diffForHumans() ?? '-' }}</td>

                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.penerima.show', $p->id_penerima) }}"
                                       class="icon-action" title="Tinjau data">
                                        <x-icon name="eye" :size="14" />
                                    </a>

                                    <a href="{{ route('admin.penerima.edit', $p->id_penerima) }}"
                                       class="icon-action" title="Edit data">
                                        <x-icon name="edit" :size="14" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty">
                                    <div class="empty-icon"><x-icon name="check" :size="20" /></div>
                                    <strong>Tidak ada data menunggu verifikasi</strong>
                                    <p>Seluruh data penerima pada wilayah tugas Anda sudah ditinjau.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

@endsection
