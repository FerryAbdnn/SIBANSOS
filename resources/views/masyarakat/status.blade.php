@extends('layouts.masyarakat')

@section('title', 'Status Pengajuan')
@section('heading', 'Status Pengajuan')

@section('content')

    @php
        $statusBadge = [
            'diajukan' => 'badge-amber',
            'terverifikasi' => 'badge-green',
            'ditolak' => 'badge-red',
        ];

        $statusLabel = [
            'diajukan' => 'Menunggu',
            'terverifikasi' => 'Terverifikasi',
            'ditolak' => 'Ditolak',
        ];

        $pengajuanList = $p
            ? $p->pengajuanBantuan->sortByDesc('created_at')
            : collect();

        $terakhir = $pengajuanList->first();
    @endphp

    <div class="page-head">
        <div class="page-head-text">
            <h1>Status Pengajuan</h1>
            <p>Pantau tahapan pengajuan dan riwayat keputusan petugas</p>
        </div>

        <div class="page-head-actions">
            <a href="{{ route('masyarakat.pengajuan.create') }}" class="btn btn-primary">
                <x-icon name="send" :size="14" />
                Ajukan Bantuan
            </a>
        </div>
    </div>

    @if (!$p)
        <div class="card">
            <div class="empty" style="padding:56px 24px;">
                <div class="empty-icon"><x-icon name="file" :size="22" /></div>
                <strong>Belum ada pengajuan</strong>
                <p>Data pengajuan akan muncul setelah Anda mengirim formulir pendaftaran bantuan.</p>

                <a href="{{ route('masyarakat.pengajuan.create') }}" class="btn btn-primary">
                    Mulai Pengajuan
                </a>
            </div>
        </div>
    @else
        <div class="grid-side" style="grid-template-columns: minmax(0,1fr) 340px;">

            <div class="stack">

                {{-- BANNER STATUS --}}
                <div class="warga-hero" style="margin-bottom:0;">

                    <span class="eyebrow" style="color:#A9C39C;">Pengajuan terakhir</span>

                    <h2 style="margin-top:6px;">
                        @if ($terakhir)
                            @if ($terakhir->status === 'diajukan')
                                Diajukan &mdash; menunggu verifikasi petugas
                            @elseif ($terakhir->status === 'terverifikasi')
                                Terverifikasi &mdash; data diterima petugas
                            @else
                                Ditolak &mdash; periksa catatan petugas
                            @endif
                        @else
                            {{ $statusLabel[$p->status] ?? ucfirst($p->status) }}
                        @endif
                    </h2>

                    <p>
                        @if ($terakhir)
                            Dikirim {{ $terakhir->created_at?->translatedFormat('d F Y') }}
                            pukul {{ $terakhir->created_at?->format('H:i') }}.
                            Petugas desa akan memeriksa kelengkapan dokumen dan kondisi ekonomi Anda.
                        @else
                            Data penerima Anda sudah tercatat pada sistem.
                        @endif
                    </p>

                    <div class="auth-metrics" style="margin-top:24px;gap:28px;">
                        <div>
                            <b style="font-size:15px;font-weight:700;">
                                {{ $terakhir
                                    ? 'PGA-' . ($terakhir->created_at?->format('Y') ?? date('Y')) . '-' . str_pad($terakhir->id_pengajuan, 4, '0', STR_PAD_LEFT)
                                    : '—' }}
                            </b>
                            <span>Nomor pengajuan</span>
                        </div>

                        <div>
                            <b style="font-size:15px;font-weight:700;">
                                {{ $terakhir?->created_at?->format('d M Y') ?? '—' }}
                            </b>
                            <span>Tanggal kirim</span>
                        </div>

                        <div>
                            <b style="font-size:15px;font-weight:700;">
                                {{ $terakhir->jenisBantuan->kategori->nama ?? '—' }}
                            </b>
                            <span>Kategori diajukan</span>
                        </div>

                        <div>
                            <b style="font-size:15px;font-weight:700;">
                                {{ $terakhir?->created_at?->diffForHumans(null, true) ?? '—' }}
                            </b>
                            <span>Sudah menunggu</span>
                        </div>
                    </div>

                </div>

                {{-- TIMELINE --}}
                <div class="card">
                    <div class="card-head">
                        <div>
                            <h3>Tahapan pengajuan</h3>
                            <p>Diperbarui otomatis setiap ada perubahan status</p>
                        </div>

                        <div class="card-head-actions">
                            <span class="badge {{ $statusBadge[$terakhir->status ?? $p->status] ?? 'badge-gray' }}">
                                {{ $statusLabel[$terakhir->status ?? $p->status] ?? ucfirst($p->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="timeline">

                            <div class="tl-item">
                                <span class="tl-node done"><x-icon name="check" :size="11" /></span>

                                <div class="tl-body">
                                    <b>Pengajuan dikirim</b>
                                    <small>
                                        {{ $terakhir?->created_at?->translatedFormat('d M Y') }}
                                        &middot; {{ $terakhir?->created_at?->format('H:i') }} &middot; oleh Anda
                                    </small>
                                </div>
                            </div>

                            <div class="tl-item">
                                <span class="tl-node {{ $terakhir && $terakhir->dokumen->count() > 0 ? 'done' : 'current' }}">
                                    @if ($terakhir && $terakhir->dokumen->count() > 0)
                                        <x-icon name="check" :size="11" />
                                    @endif
                                </span>

                                <div class="tl-body">
                                    <b>Berkas diterima sistem</b>
                                    <small>
                                        {{ $terakhir ? $terakhir->dokumen->count() : 0 }}
                                        dokumen tersimpan dan siap diperiksa
                                    </small>
                                </div>
                            </div>

                            <div class="tl-item">
                                <span class="tl-node {{ $terakhir?->tanggal_verifikasi ? 'done' : 'current' }}">
                                    @if ($terakhir?->tanggal_verifikasi)
                                        <x-icon name="check" :size="11" />
                                    @endif
                                </span>

                                <div class="tl-body">
                                    <b>Pemeriksaan petugas desa</b>
                                    <small>
                                        @if ($terakhir?->tanggal_verifikasi)
                                            {{ \Carbon\Carbon::parse($terakhir->tanggal_verifikasi)->translatedFormat('d M Y') }}
                                            &middot; diputuskan petugas
                                        @else
                                            Sedang berlangsung &middot; biasanya 3&ndash;7 hari kerja
                                        @endif
                                    </small>
                                </div>
                            </div>

                            <div class="tl-item">
                                <span class="tl-node {{ $terakhir?->status === 'terverifikasi' ? 'done' : 'pending' }}">
                                    @if ($terakhir?->status === 'terverifikasi')
                                        <x-icon name="check" :size="11" />
                                    @endif
                                </span>

                                <div class="tl-body">
                                    <b>Penetapan kategori bantuan</b>
                                    <small>
                                        {{ $terakhir?->status === 'terverifikasi'
                                            ? 'Kategori ditetapkan: ' . ($terakhir->jenisBantuan->nama_program ?? '-')
                                            : 'Menunggu hasil pemeriksaan' }}
                                    </small>
                                </div>
                            </div>

                            <div class="tl-item">
                                <span class="tl-node {{ $p->riwayat->isNotEmpty() ? 'done' : 'pending' }}">
                                    @if ($p->riwayat->isNotEmpty())
                                        <x-icon name="check" :size="11" />
                                    @endif
                                </span>

                                <div class="tl-body">
                                    <b>Pencatatan sebagai penerima</b>
                                    <small>
                                        {{ $p->riwayat->isNotEmpty()
                                            ? 'Nomor penerima diterbitkan setelah disetujui'
                                            : 'Nomor penerima diterbitkan setelah disetujui' }}
                                    </small>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card-foot">
                        <x-icon name="info" :size="13" />
                        Anda akan menerima notifikasi WhatsApp saat status pengajuan berubah.
                    </div>
                </div>

                {{-- RIWAYAT PENGAJUAN --}}
                <div class="card">
                    <div class="card-head">
                        <div>
                            <h3>Riwayat pengajuan</h3>
                            <p>Semua pengajuan yang pernah Anda kirim</p>
                        </div>
                    </div>

                    <div class="tbl-wrap">
                        <table class="tbl">
                            <thead>
                                <tr>
                                    <th>Nomor</th>
                                    <th>Tanggal</th>
                                    <th>Kategori</th>
                                    <th>Status</th>
                                    <th>Catatan petugas</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($pengajuanList as $a)
                                    <tr>
                                        <td class="mono">
                                            PGA-{{ $a->created_at?->format('Y') ?? date('Y') }}-{{ str_pad($a->id_pengajuan, 4, '0', STR_PAD_LEFT) }}
                                        </td>

                                        <td>{{ $a->created_at?->format('d M Y') ?? '-' }}</td>

                                        <td>{{ $a->jenisBantuan->nama_program ?? '-' }}</td>

                                        <td>
                                            <span class="badge {{ $statusBadge[$a->status] ?? 'badge-gray' }}">
                                                {{ $statusLabel[$a->status] ?? ucfirst($a->status) }}
                                            </span>
                                        </td>

                                        <td class="muted">{{ $a->catatan_verifikasi ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty" style="padding:28px;">
                                                <strong>Belum ada riwayat pengajuan</strong>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- SIDEBAR --}}
            <aside class="stack">

                <div class="card">
                    <div class="card-head">
                        <div>
                            <h3>Data yang Anda kirim</h3>
                        </div>
                    </div>

                    <div class="card-body" style="padding-top:4px;">

                        @php
                            $snapshot = $terakhir->data_penerima ?? [];
                        @endphp

                        <div class="info-list">
                            <div class="info-row">
                                <small>No. KK</small>
                                <strong class="mono">
                                    {{ $p->no_kk_decrypted ? substr($p->no_kk_decrypted, 0, 4) . '••••' . substr($p->no_kk_decrypted, -4) : '-' }}
                                </strong>
                            </div>

                            <div class="info-row">
                                <small>Penghasilan</small>
                                <strong>
                                    Rp {{ number_format((float) ($snapshot['penghasilan_bulanan'] ?? $p->penghasilan_bulanan), 0, ',', '.') }}
                                </strong>
                            </div>

                            <div class="info-row">
                                <small>Tanggungan</small>
                                <strong>{{ $snapshot['jumlah_tanggungan'] ?? $p->jumlah_tanggungan }} orang</strong>
                            </div>

                            <div class="info-row">
                                <small>Kondisi rumah</small>
                                <strong>
                                    {{ [
                                        'permanen' => 'Permanen',
                                        'semi_permanen' => 'Semi permanen',
                                        'tidak_layak' => 'Tidak layak',
                                    ][$snapshot['kondisi_rumah'] ?? $p->kondisi_rumah] ?? '-' }}
                                </strong>
                            </div>

                            <div class="info-row">
                                <small>Disabilitas</small>
                                <strong>{{ ($snapshot['disabilitas'] ?? $p->disabilitas) ? 'Ya' : 'Tidak' }}</strong>
                            </div>

                            <div class="info-row">
                                <small>Lansia</small>
                                <strong>{{ ($snapshot['lansia'] ?? $p->lansia) ? 'Ya' : 'Tidak' }}</strong>
                            </div>
                        </div>

                        @if ($terakhir && $terakhir->dokumen->isNotEmpty())
                            <div style="display:grid;gap:8px;margin-top:14px;">
                                @foreach ($terakhir->dokumen->take(3) as $dok)
                                    <div class="doc-item">
                                        <span class="ic"><x-icon name="file" :size="14" /></span>

                                        <div style="min-width:0;">
                                            <b>{{ \Illuminate\Support\Str::limit($dok->nama_file, 24) }}</b>
                                            <small>{{ strtoupper($dok->jenis_dokumen) }}</small>
                                        </div>

                                        <span class="badge badge-green" style="margin-left:auto;flex:none;">Terbaca</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <b style="font-size:13px;">Butuh bantuan?</b>

                        <p class="muted" style="font-size:12px;margin-top:4px;line-height:1.6;">
                            Jika proses melebihi 7 hari kerja, hubungi petugas desa
                            untuk menanyakan status pengajuan Anda.
                        </p>

                        <div style="display:grid;gap:8px;margin-top:12px;">
                            <a href="https://wa.me/6281234567890" target="_blank" rel="noopener"
                               class="btn btn-outline btn-block">
                                <x-icon name="phone" :size="13" />
                                Chat petugas
                            </a>

                            <a href="{{ route('masyarakat.perbaikan.create') }}"
                               class="btn btn-outline btn-block">
                                <x-icon name="edit" :size="13" />
                                Perbaiki data
                            </a>
                        </div>
                    </div>
                </div>

            </aside>

        </div>
    @endif

@endsection
