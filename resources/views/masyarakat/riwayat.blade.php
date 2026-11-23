
@extends('layouts.masyarakat')

@section('title', 'Riwayat Bantuan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/riwayat-masyarakat.css') }}">
@endpush

@section('content')

    <x-page-header
        title="Riwayat Bantuan Warga"
        description="Pantau bantuan sosial yang pernah Anda terima dan rincian penyalurannya."
    />

    @if (!$penerima)

        <div class="panel">
            <div class="empty">
                <h3>Data penerima belum tersedia</h3>
                <p>Riwayat bantuan akan ditampilkan setelah data penerima terhubung dengan akun Anda.</p>

                <a href="{{ route('masyarakat.pengajuan.create') }}" class="btn primary">
                    Ajukan Bantuan
                </a>
            </div>
        </div>

    @else

        @php
            $statusLabel = [
                'diajukan' => 'Menunggu Verifikasi',
                'terverifikasi' => 'Terverifikasi',
                'ditolak' => 'Ditolak',
                'nonaktif' => 'Nonaktif',
            ];

            $statusPenerima = $statusLabel[$penerima->status]
                ?? ucfirst($penerima->status ?? '-');
        @endphp

        <div class="riwayat-page">

            {{-- IDENTITAS PENERIMA --}}
            <section class="riwayat-identity">
                <div>
                    <span class="riwayat-eyebrow">INFORMASI PENERIMA</span>
                    <h3>{{ $penerima->nama }}</h3>
                    <p>
                        NIK
                        ••••••••••••{{ substr(decrypt($penerima->nik), -4) }}
                    </p>
                </div>

                <span class="riwayat-status {{ $penerima->status }}">
                    {{ $statusPenerima }}
                </span>
            </section>

            {{-- RINGKASAN --}}
            <section class="riwayat-summary">

                <article class="riwayat-summary-card">
                    <span class="summary-icon">
                        <i class="riwayat-symbol">▤</i>
                    </span>
                    <div>
                        <small>Total Penyaluran</small>
                        <strong>{{ number_format($totalPenyaluran, 0, ',', '.') }}</strong>
                        <span>Catatan bantuan diterima</span>
                    </div>
                </article>

                <article class="riwayat-summary-card">
                    <span class="summary-icon">
                        <i class="riwayat-symbol">▦</i>
                    </span>
                    <div>
                        <small>Kategori Utama</small>
                        <strong class="summary-text">
                            {{ $kategoriUtama ?? 'Belum ada' }}
                        </strong>
                        <span>Kategori paling sering diterima</span>
                    </div>
                </article>

                <article class="riwayat-summary-card">
                    <span class="summary-icon">
                        <i class="riwayat-symbol">◷</i>
                    </span>
                    <div>
                        <small>Penyaluran Terakhir</small>
                        <strong class="summary-text">
                            {{ $tanggalTerakhir?->format('d M Y') ?? 'Belum ada' }}
                        </strong>
                        <span>Tanggal bantuan diterima</span>
                    </div>
                </article>

            </section>

            {{-- KONTEN UTAMA --}}
            <section class="riwayat-content-grid">

                <div class="riwayat-main-column">

                    {{-- TABEL PENYALURAN --}}
                    <div class="panel riwayat-panel">
                        <div class="riwayat-panel-heading">
                            <div>
                                <h3>Riwayat Penyaluran Bantuan</h3>
                                <p>Daftar bantuan yang tercatat telah disalurkan kepada Anda.</p>
                            </div>

                            <span class="riwayat-count">
                                {{ $items->total() }} Catatan
                            </span>
                        </div>

                        <div class="table-wrap riwayat-table-wrap">
                            <table class="riwayat-table">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Program Bantuan</th>
                                        <th>Tanggal Diterima</th>
                                        <th>Jumlah</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($items as $item)
                                        <tr>
                                            <td>
                                                {{ $items->firstItem() + $loop->index }}
                                            </td>

                                            <td>
                                                <strong>
                                                    {{ $item->jenis?->nama_program ?? 'Program tidak tersedia' }}
                                                </strong>
                                                <span class="sub">
                                                    {{ $item->jenis?->kategori?->nama ?? 'Kategori tidak tersedia' }}
                                                </span>
                                            </td>

                                            <td>
                                                {{ $item->tanggal_terima?->format('d/m/Y') ?? '-' }}
                                            </td>

                                            <td>
                                                @if (!is_null($item->jumlah))
                                                    {{ number_format((float) $item->jumlah, 0, ',', '.') }}
                                                    {{ $item->satuan }}
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            <td>
                                                {{ $item->keterangan ?: '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="empty">
                                                Belum ada riwayat bantuan yang tercatat.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if ($items->hasPages())
                            <div class="riwayat-pagination">
                                <x-pagination :paginator="$items" />
                            </div>
                        @endif
                    </div>

                    {{-- DETAIL PENYALURAN TERAKHIR --}}
                    <div class="panel riwayat-panel">
                        <div class="riwayat-panel-heading">
                            <div>
                                <h3>Detail Penyaluran Terakhir</h3>
                                <p>Informasi bantuan terakhir yang tercatat diterima.</p>
                            </div>
                        </div>

                        @if ($riwayatTerbaru)
                            <div class="riwayat-last-detail">
                                <div>
                                    <small>Program Bantuan</small>
                                    <strong>
                                        {{ $riwayatTerbaru->jenis?->nama_program ?? '-' }}
                                    </strong>
                                </div>

                                <div>
                                    <small>Kategori</small>
                                    <strong>
                                        {{ $riwayatTerbaru->jenis?->kategori?->nama ?? '-' }}
                                    </strong>
                                </div>

                                <div>
                                    <small>Tanggal Diterima</small>
                                    <strong>
                                        {{ $riwayatTerbaru->tanggal_terima?->format('d M Y') ?? '-' }}
                                    </strong>
                                </div>

                                <div>
                                    <small>Jumlah Bantuan</small>
                                    <strong>
                                        @if (!is_null($riwayatTerbaru->jumlah))
                                            {{ number_format((float) $riwayatTerbaru->jumlah, 0, ',', '.') }}
                                            {{ $riwayatTerbaru->satuan }}
                                        @else
                                            -
                                        @endif
                                    </strong>
                                </div>

                                <div class="last-detail-note">
                                    <small>Keterangan</small>
                                    <p>{{ $riwayatTerbaru->keterangan ?: 'Tidak ada keterangan tambahan.' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="riwayat-empty-detail">
                                Belum ada detail penyaluran bantuan.
                            </div>
                        @endif
                    </div>

                </div>

                {{-- SIDEBAR --}}
                <aside class="riwayat-side-column">

                    <div class="panel riwayat-panel">
                        <div class="riwayat-panel-heading">
                            <div>
                                <h3>Bantuan Berdasarkan Kategori</h3>
                                <p>Proporsi catatan penyaluran setiap kategori.</p>
                            </div>
                        </div>

                        @forelse ($kategoriStats as $stat)
                            <div class="category-stat">
                                <div class="category-stat-heading">
                                    <strong>{{ $stat['nama'] }}</strong>
                                    <span>{{ $stat['jumlah'] }} penyaluran</span>
                                </div>

                                <div class="category-progress">
                                    <div style="width: {{ $stat['persentase'] }}%"></div>
                                </div>

                                <small>{{ $stat['persentase'] }}% dari seluruh penyaluran</small>
                            </div>
                        @empty
                            <div class="riwayat-empty-detail">
                                Belum ada data kategori bantuan.
                            </div>
                        @endforelse
                    </div>

                    <div class="panel riwayat-help-panel">
                        <div class="riwayat-help-icon">?</div>
                        <h3>Butuh Bantuan?</h3>
                        <p>
                            Jika terdapat ketidaksesuaian pada catatan penyaluran,
                            silakan hubungi petugas desa atau kelurahan untuk
                            mendapatkan penjelasan lebih lanjut.
                        </p>
                        <a href="{{ route('masyarakat.status') }}" class="btn primary">
                            Lihat Status Pengajuan
                        </a>
                    </div>

                </aside>

            </section>

        </div>

    @endif

@endsection
