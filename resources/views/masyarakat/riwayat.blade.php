@extends('layouts.masyarakat')

@section('title', 'Riwayat Bantuan')
@section('heading', 'Riwayat Bantuan')

@section('content')

    @php
        $totalNominal = $items->sum(fn ($i) => (float) ($i->jumlah ?? 0));
    @endphp

    <div class="page-head">
        <div class="page-head-text">
            <h1>Riwayat Bantuan</h1>
            <p>Bantuan sosial yang pernah Anda terima berdasarkan catatan penyaluran</p>
        </div>

        <div class="page-head-actions">
            <a href="{{ route('masyarakat.status') }}" class="btn btn-outline">
                <x-icon name="file" :size="14" />
                Status pengajuan
            </a>
        </div>
    </div>

    @if (!$penerima)
        <div class="card">
            <div class="empty" style="padding:56px 24px;">
                <div class="empty-icon"><x-icon name="history" :size="22" /></div>
                <strong>Data penerima belum tersedia</strong>
                <p>Riwayat bantuan akan ditampilkan setelah data penerima terhubung dengan akun Anda.</p>

                <a href="{{ route('masyarakat.pengajuan.create') }}" class="btn btn-primary">
                    Ajukan Bantuan
                </a>
            </div>
        </div>
    @else
        <div class="kpi-grid">

            <div class="kpi">
                <div class="kpi-top">
                    <span class="kpi-label">Total diterima</span>
                    <span class="kpi-icon"><x-icon name="wallet" :size="16" /></span>
                </div>

                <strong class="kpi-value">Rp {{ number_format($totalNominal, 0, ',', '.') }}</strong>
                <small class="kpi-caption">{{ $items->total() }} catatan penyaluran</small>
            </div>

            <div class="kpi">
                <div class="kpi-top">
                    <span class="kpi-label">Status data</span>
                    <span class="kpi-icon gray"><x-icon name="user" :size="16" /></span>
                </div>

                <strong class="kpi-value" style="font-size:18px;">
                    {{ [
                        'diajukan' => 'Menunggu',
                        'terverifikasi' => 'Terverifikasi',
                        'ditolak' => 'Ditolak',
                        'nonaktif' => 'Nonaktif',
                    ][$penerima->status] ?? ucfirst($penerima->status) }}
                </strong>

                <small class="kpi-caption">Status pendataan Anda</small>
            </div>

            <div class="kpi">
                <div class="kpi-top">
                    <span class="kpi-label">Penyaluran terakhir</span>
                    <span class="kpi-icon amber"><x-icon name="calendar" :size="16" /></span>
                </div>

                <strong class="kpi-value" style="font-size:18px;">
                    {{ $items->first()?->tanggal_terima?->format('d M Y') ?? '—' }}
                </strong>

                <small class="kpi-caption">
                    {{ $items->first()?->jenis?->nama_program ?? 'Belum ada penyaluran' }}
                </small>
            </div>

            <div class="kpi">
                <div class="kpi-top">
                    <span class="kpi-label">Nomor penerima</span>
                    <span class="kpi-icon gray"><x-icon name="id-card" :size="16" /></span>
                </div>

                <strong class="kpi-value" style="font-size:16px;">
                    PNR-{{ $penerima->created_at?->format('Y') ?? date('Y') }}-{{ str_pad($penerima->id_penerima, 5, '0', STR_PAD_LEFT) }}
                </strong>

                <small class="kpi-caption">
                    Terdaftar {{ $penerima->created_at?->translatedFormat('M Y') ?? '-' }}
                </small>
            </div>

        </div>

        <div class="card">

            <div class="card-head">
                <div>
                    <h3>Daftar riwayat penyaluran</h3>
                    <p>Bantuan yang tercatat telah disalurkan kepada Anda.</p>
                </div>

                <div class="card-head-actions">
                    <span class="badge badge-green">{{ $items->total() }} catatan</span>
                </div>
            </div>

            <div class="tbl-wrap">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Program</th>
                            <th>Kategori</th>
                            <th>Penyelenggara</th>
                            <th class="num">Nominal</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>{{ $item->tanggal_terima?->format('d M Y') ?? '-' }}</td>

                                <td class="strong">{{ $item->jenis?->nama_program ?? 'Program tidak tersedia' }}</td>

                                <td>
                                    <span class="badge badge-gray">
                                        {{ $item->jenis?->kategori?->nama ?? '-' }}
                                    </span>
                                </td>

                                <td class="muted">{{ $item->jenis?->penyelenggara ?? '-' }}</td>

                                <td class="num strong">
                                    @if (!is_null($item->jumlah))
                                        Rp {{ number_format((float) $item->jumlah, 0, ',', '.') }}
                                        @if ($item->satuan && $item->satuan !== 'Rupiah')
                                            <span class="muted" style="font-weight:500;font-size:11px;">
                                                {{ $item->satuan }}
                                            </span>
                                        @endif
                                    @else
                                        &mdash;
                                    @endif
                                </td>

                                <td class="muted">{{ $item->keterangan ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty">
                                        <div class="empty-icon"><x-icon name="gift" :size="20" /></div>
                                        <strong>Belum ada riwayat bantuan</strong>
                                        <p>Bantuan yang sudah disalurkan akan tercatat di sini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <x-pagination :paginator="$items" />
            @endif

            <div class="card-foot">
                <x-icon name="shield" :size="13" />
                Riwayat penyaluran bersifat permanen dan tidak dapat dihapus.
            </div>

        </div>
    @endif

@endsection
