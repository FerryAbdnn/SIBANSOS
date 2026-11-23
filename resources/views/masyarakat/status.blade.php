@extends('layouts.masyarakat')

@section('title', 'Status Pengajuan')
@section('heading', 'Status Pengajuan Saya')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/status-masyarakat.css') }}">
@endpush

@section('content')
    @php
        $pengajuanList = $p
            ? $p->pengajuanBantuan->sortByDesc('created_at')->values()
            : collect();

        $terbaru = $pengajuanList->first();

        $statusLabel = [
            'diajukan' => 'Menunggu verifikasi',
            'terverifikasi' => 'Terverifikasi',
            'ditolak' => 'Ditolak',
        ];

        $statusClass = [
            'diajukan' => 'waiting',
            'terverifikasi' => 'verified',
            'ditolak' => 'rejected',
        ];

        $dokumenLabel = [
            'ktp' => 'Kartu Tanda Penduduk',
            'kk' => 'Kartu Keluarga',
            'surat_keterangan' => 'Surat Keterangan',
            'lainnya' => 'Dokumen pendukung',
        ];

        $formatRupiah = function ($angka) {
            return 'Rp ' . number_format((float) $angka, 0, ',', '.');
        };

        $formatKondisiRumah = function ($kondisi) {
            return match ($kondisi) {
                'permanen' => 'Permanen',
                'semi_permanen' => 'Semi permanen',
                'tidak_layak' => 'Tidak layak',
                default => '-',
            };
        };
    @endphp

    <div class="status-page">

        <header class="status-page-heading">
            <div>
                <span class="status-eyebrow">LAYANAN MASYARAKAT</span>
                <h1>Status Pengajuan</h1>
                <p>
                    Pantau perkembangan pengajuan bantuan dan periksa
                    kembali data yang telah Anda kirimkan.
                </p>
            </div>

            <a href="{{ route('masyarakat.pengajuan.create') }}"
                class="status-new-button">
                <span aria-hidden="true">＋</span>
                Ajukan Bantuan
            </a>
        </header>

        @if (!$p)
            <section class="status-empty">
                <div class="status-empty-icon" aria-hidden="true">⌕</div>
                <h2>Data pengajuan belum tersedia</h2>
                <p>
                    Akun Anda belum terhubung dengan data penerima bantuan.
                    Silakan mulai pengajuan bantuan untuk mengisi data.
                </p>
                <a href="{{ route('masyarakat.pengajuan.create') }}"
                    class="status-primary-button">
                    Mulai Pengajuan
                </a>
            </section>
        @else

            {{-- KARTU PENGAJUAN TERBARU --}}
            @if ($terbaru)
                @php
                    $statusTerbaru = $terbaru->status;

                    // Status penerima saat ini.
                    $penerimaNonaktif = $p->status === 'nonaktif';

                    // Timeline selalu mengikuti status pengajuan yang sebenarnya.
                    $statusTimeline = $statusTerbaru;

                    // Riwayat penyaluran tetap ditampilkan berdasarkan pengajuan terkait.
                    $riwayatPenyaluran = $p->riwayat->firstWhere(
                        'id_pengajuan',
                        $terbaru->id_pengajuan
                    );

                    $snapshotTerbaru = $terbaru->data_penerima ?? [];

                    $hariMenunggu = $terbaru->created_at
                        ? $terbaru->created_at->diffForHumans()
                        : '-';
                @endphp

                <section class="status-latest-card">
                    <div class="status-latest-top">
                        <div>
                            <span class="status-latest-label">
                                PENGAJUAN TERBARU
                            </span>
                            <h2>
                                {{ $terbaru->jenisBantuan->nama_program ?? 'Program bantuan' }}
                            </h2>
                            <p class="status-latest-description">
                                Informasi perkembangan pengajuan bantuan Anda.
                            </p>
                        </div>

                        <span class="status-pill status-pill-light {{ $statusClass[$statusTerbaru] ?? 'waiting' }}">
                            {{ $statusLabel[$statusTerbaru] ?? ucfirst($statusTerbaru) }}
                        </span>
                    </div>

                    <div class="status-latest-divider"></div>

                    <div class="status-latest-meta">
                        <div>
                            <span>Nomor pengajuan</span>
                            <strong>#{{ $terbaru->id_pengajuan }}</strong>
                        </div>

                        <div>
                            <span>Tanggal pengajuan</span>
                            <strong>
                                {{ $terbaru->created_at?->format('d M Y, H:i') ?? '-' }}
                            </strong>
                        </div>

                        <div>
                            <span>Waktu pengajuan</span>
                            <strong>{{ $hariMenunggu }}</strong>
                        </div>
                    </div>

                    @if ($statusTerbaru === 'ditolak' && $terbaru->catatan_verifikasi)
                        <div class="status-latest-note">
                            <strong>Catatan petugas</strong>
                            <p>{{ $terbaru->catatan_verifikasi }}</p>
                        </div>
                    @elseif ($statusTerbaru === 'diajukan')
                        <div class="status-latest-note">
                            <strong>Menunggu pemeriksaan</strong>
                            <p>
                                Pengajuan Anda telah tercatat. Silakan menunggu
                                pemeriksaan dan verifikasi dari petugas.
                            </p>
                        </div>
                    @elseif ($statusTerbaru === 'terverifikasi')
                        <div class="status-latest-note">
                            <strong>Pengajuan telah diverifikasi</strong>
                            <p>
                                Pengajuan Anda telah diverifikasi.
                                Informasi penerimaan bantuan akan tercatat
                                pada riwayat setelah penyaluran dicatat petugas.
                            </p>
                        </div>
                    @endif
                </section>

                {{-- PROGRES PENGAJUAN --}}
                <section class="status-section status-process-section">
                    <div class="status-section-heading">
                        <div>
                            <span class="status-eyebrow">PERKEMBANGAN</span>
                            <h2>Proses Pengajuan</h2>
                            <p>Urutan tahapan administrasi pengajuan bantuan.</p>
                        </div>
                    </div>

                    <div class="status-timeline">
                        @if ($penerimaNonaktif)
    <div class="status-notice">
        <strong>Status penerima saat ini nonaktif</strong>
        <p>
            Pengajuan terakhir tetap ditampilkan sebagai riwayat.
            Anda dapat mengajukan kembali bantuan sesuai ketentuan
            program yang berlaku.
        </p>
    </div>
@endif

                        <div class="status-timeline-item completed">
                            <div class="status-timeline-marker">✓</div>
                            <div class="status-timeline-content">
                                <strong>Pengajuan dikirim</strong>
                                <p>
                                    Pengajuan tercatat pada
                                    {{ $terbaru->created_at?->format('d M Y, H:i') ?? '-' }}.
                                </p>
                            </div>
                        </div>

                        <div class="status-timeline-item {{ $terbaru->dokumen->isNotEmpty() ? 'completed' : 'pending' }}">
    <div class="status-timeline-marker">
        {{ $terbaru->dokumen->isNotEmpty() ? '✓' : '2' }}
    </div>

    <div class="status-timeline-content">
        <strong>Dokumen pendukung tercatat</strong>
        <p>
            {{ $terbaru->dokumen->count() }}
            dokumen terhubung dengan pengajuan ini.
        </p>
    </div>
</div>

                        <div class="status-timeline-item {{ in_array($statusTimeline, ['terverifikasi', 'ditolak']) ? 'completed' : 'current' }}">
                            <div class="status-timeline-marker">3</div>

                            <div class="status-timeline-content">
                                <strong>Pemeriksaan petugas</strong>
                                <p>
                                    @if ($statusTimeline === 'diajukan')
                                        Pengajuan menunggu pemeriksaan petugas.
                                    @else
                                        Pengajuan telah diproses oleh petugas.
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="status-timeline-item {{ in_array($statusTimeline, ['terverifikasi', 'ditolak']) ? 'completed' : 'pending' }}">
                            <div class="status-timeline-marker">
                                {{ in_array($statusTimeline, ['terverifikasi', 'ditolak']) ? '✓' : '4' }}
                            </div>
                            <div class="status-timeline-content">
                                <strong>Keputusan verifikasi</strong>
                                <p>
                                    @if ($statusTimeline === 'terverifikasi')
                                        Pengajuan telah diverifikasi.
                                    @elseif ($statusTimeline === 'ditolak')
                                        Pengajuan tidak disetujui.
                                    @else
                                        Menunggu keputusan verifikasi.
                                    @endif

                                    @if ($terbaru->tanggal_verifikasi)
                                        <span>
                                            {{ $terbaru->tanggal_verifikasi->format('d M Y, H:i') }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="status-timeline-item {{ $riwayatPenyaluran ? 'completed' : 'pending' }}">
                            <div class="status-timeline-marker">
                                {{ $riwayatPenyaluran ? '✓' : '5' }}
                            </div>

                            <div class="status-timeline-content">
                                <strong>Penerimaan bantuan</strong>

                                <p>
                                    @if ($riwayatPenyaluran)
                                        Bantuan tercatat diterima pada
                                        {{ $riwayatPenyaluran->tanggal_terima?->format('d M Y') ?? '-' }}.
                                    @else
                                        Belum ada catatan penyaluran bantuan untuk pengajuan ini.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            @else
                <section class="status-empty status-empty-compact">
                    <h2>Belum ada riwayat pengajuan</h2>
                    <p>
                        Anda belum pernah mengirimkan pengajuan bantuan melalui akun ini.
                    </p>
                    <a href="{{ route('masyarakat.pengajuan.create') }}"
                        class="status-primary-button">
                        Ajukan Bantuan
                    </a>
                </section>
            @endif

            {{-- DATA DAN DOKUMEN --}}
            @if ($terbaru)
                @php
                    $data = $terbaru->data_penerima ?? [];
                @endphp

                <div class="status-detail-grid">
                    <section class="status-section">
                        <div class="status-section-heading">
                            <div>
                                <span class="status-eyebrow">DATA PENGAJUAN</span>
                                <h2>Informasi yang Dikirim</h2>
                                <p>
                                    Data berdasarkan saat pengajuan dibuat.
                                </p>
                            </div>
                        </div>

                        <div class="status-data-list">
                            <div class="status-data-row">
                                <span>Nama lengkap</span>
                                <strong>{{ $p->nama ?? '-' }}</strong>
                            </div>

                            <div class="status-data-row">
                                <span>Alamat</span>
                                <strong>{{ $data['alamat'] ?? $p->alamat ?? '-' }}</strong>
                            </div>

                            <div class="status-data-row">
                                <span>Wilayah RT</span>
                                <strong>{{ $data['nama_rt'] ?? '-' }}</strong>
                            </div>

                            <div class="status-data-row">
                                <span>Wilayah RW</span>
                                <strong>{{ $data['nama_rw'] ?? '-' }}</strong>
                            </div>

                            <div class="status-data-row">
                                <span>Nomor WhatsApp</span>
                                <strong>{{ $data['nomor_whatsapp'] ?? '-' }}</strong>
                            </div>

                            <div class="status-data-row">
                                <span>Penghasilan bulanan</span>
                                <strong>
                                    {{ isset($data['penghasilan_bulanan']) ? $formatRupiah($data['penghasilan_bulanan']) : '-' }}
                                </strong>
                            </div>

                            <div class="status-data-row">
                                <span>Jumlah tanggungan</span>
                                <strong>
                                    {{ isset($data['jumlah_tanggungan']) ? $data['jumlah_tanggungan'] . ' orang' : '-' }}
                                </strong>
                            </div>

                            <div class="status-data-row">
                                <span>Kondisi rumah</span>
                                <strong>
                                    {{ $formatKondisiRumah($data['kondisi_rumah'] ?? null) }}
                                </strong>
                            </div>

                            <div class="status-data-row">
                                <span>Disabilitas</span>
                                <strong>{{ !empty($data['disabilitas']) ? 'Ya' : 'Tidak' }}</strong>
                            </div>

                            <div class="status-data-row">
                                <span>Lansia</span>
                                <strong>{{ !empty($data['lansia']) ? 'Ya' : 'Tidak' }}</strong>
                            </div>
                        </div>
                    </section>

                    <div class="status-side-column">
                        <section class="status-section">
                            <div class="status-section-heading">
                                <div>
                                    <span class="status-eyebrow">LAMPIRAN</span>
                                    <h2>Dokumen Pendukung</h2>
                                    <p>
                                        Dokumen yang tersimpan pada pengajuan ini.
                                    </p>
                                </div>
                            </div>

                            @forelse ($terbaru->dokumen as $dokumen)
                                <div class="status-document-row">
                                    <span class="status-document-icon" aria-hidden="true">▤</span>
                                    <div>
                                        <strong>
                                            {{ $dokumenLabel[$dokumen->jenis_dokumen] ?? ucfirst(str_replace('_', ' ', $dokumen->jenis_dokumen)) }}
                                        </strong>
                                        <span>{{ $dokumen->nama_file }}</span>
                                    </div>
                                    <span class="status-document-check" title="Tersimpan">✓</span>
                                </div>
                            @empty
                                <p class="status-muted">
                                    Belum ada dokumen yang tercatat pada pengajuan ini.
                                </p>
                            @endforelse
                        </section>

                        <section class="status-help-card">
                            <span class="status-help-icon" aria-hidden="true">?</span>
                            <h2>Butuh Bantuan?</h2>
                            <p>
                                Jika ada pertanyaan mengenai status pengajuan,
                                silakan hubungi petugas desa atau kelurahan.
                            </p>
                            <a href="{{ route('masyarakat.perbaikan.create') }}">
                                Buka halaman Perbaikan Data
                                <span aria-hidden="true">→</span>
                            </a>
                        </section>
                    </div>
                </div>
            @endif

            {{-- RIWAYAT SELURUH PENGAJUAN --}}
            <section class="status-section status-history-section">
                <div class="status-section-heading">
                    <div>
                        <span class="status-eyebrow">REKAM JEJAK</span>
                        <h2>Riwayat Pengajuan</h2>
                        <p>Daftar pengajuan bantuan yang pernah Anda kirimkan.</p>
                    </div>
                    <span class="status-total">
                        {{ $pengajuanList->count() }} pengajuan
                    </span>
                </div>

                @if ($pengajuanList->isNotEmpty())
                    <div class="status-table-wrap">
                        <table class="status-table">
                            <thead>
                                <tr>
                                    <th>Program bantuan</th>
                                    <th>Tanggal pengajuan</th>
                                    <th>Status</th>
                                    <th>Catatan petugas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pengajuanList as $item)
                                    <tr>
                                        <td>
                                            <strong>
                                                {{ $item->jenisBantuan->nama_program ?? '-' }}
                                            </strong>
                                            <span class="status-table-number">
                                                #{{ $item->id_pengajuan }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $item->created_at?->format('d/m/Y H:i') ?? '-' }}
                                        </td>
                                        <td>
                                            <span class="status-pill {{ $statusClass[$item->status] ?? 'waiting' }}">
                                                {{ $statusLabel[$item->status] ?? ucfirst($item->status) }}
                                            </span>
                                        </td>
                                        <td class="status-table-note">
                                            {{ $item->catatan_verifikasi ?: '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="status-muted">
                        Belum ada riwayat pengajuan bantuan.
                    </p>
                @endif
            </section>

            {{-- RIWAYAT BANTUAN DITERIMA --}}
            <section class="status-section status-history-section">
                <div class="status-section-heading">
                    <div>
                        <span class="status-eyebrow">PENYALURAN</span>
                        <h2>Riwayat Bantuan Diterima</h2>
                        <p>Catatan bantuan yang benar-benar telah diterima.</p>
                    </div>
                </div>

                @forelse ($p->riwayat->sortByDesc('tanggal_terima') as $riwayat)
                    <div class="status-aid-row">
                        <div>
                            <strong>{{ $riwayat->jenis->nama_program ?? '-' }}</strong>
                            <span>
                                {{ $riwayat->tanggal_terima?->format('d M Y') ?? '-' }}
                            </span>
                        </div>

                        @if ($riwayat->jumlah !== null)
                            <strong class="status-aid-amount">
                                {{ $formatRupiah($riwayat->jumlah) }}
                            </strong>
                        @endif
                    </div>
                @empty
                    <p class="status-muted">
                        Belum ada riwayat bantuan yang diterima.
                    </p>
                @endforelse
            </section>
        @endif
    </div>
@endsection
