@extends('layouts.masyarakat')

@section('title', 'Dashboard Masyarakat')
@section('heading', 'Status Pendaftaran')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard-masyarakat.css') }}">
@endpush

@section('content')

    @php
        $pengajuan = $p?->pengajuanTerbaru;
        $riwayatTerakhir = $p?->riwayat?->first();
        $perbaikanTerakhir = $p?->pengajuanPerbaikan?->first();

        $status = $pengajuan?->status ?? 'belum_mengajukan';

        $statusLabel = [
            'diajukan' => 'Menunggu Verifikasi',
            'terverifikasi' => 'Terverifikasi',
            'ditolak' => 'Ditolak',
            'nonaktif' => 'Nonaktif',
            'belum_mengajukan' => 'Belum Mengajukan',
        ][$status] ?? ucfirst($status);

        $statusClass = [
            'diajukan' => 'waiting',
            'terverifikasi' => 'verified',
            'ditolak' => 'rejected',
            'nonaktif' => 'inactive',
            'belum_mengajukan' => 'empty',
        ][$status] ?? 'empty';

        $nik = $p?->nik
            ? preg_replace('/\D/', '', (string) $p->nik)
            : '';

        $nikTampil = $nik
            ? substr($nik, 0, 4) . str_repeat('*', max(0, strlen($nik) - 8)) . substr($nik, -4)
            : '-';

        $rt = $p?->wilayah;
        $rw = $rt?->induk;
        $desa = $rw?->induk;

        $lokasi = collect([
            $rt ? 'RT ' . $rt->nama : null,
            $rw ? 'RW ' . $rw->nama : null,
            $desa?->nama,
        ])->filter()->implode(' · ');

        $dokumenLabel = [
            'ktp' => 'KTP',
            'kk' => 'Kartu Keluarga',
            'surat_keterangan' => 'Surat Keterangan',
            'lainnya' => 'Dokumen lainnya',
        ];
    @endphp

    <div class="citizen-dashboard">

        {{-- HEADER HALAMAN --}}
        <div class="dashboard-heading">
            <div>
                <span class="dashboard-eyebrow">PORTAL MASYARAKAT</span>
                <h1>Status Pendaftaran</h1>
                <p>Ringkasan status pendataan dan bantuan sosial Anda.</p>
            </div>

            <div class="dashboard-actions">
                <a href="{{ route('masyarakat.perbaikan.create') }}" class="dashboard-btn dashboard-btn-outline">
                    <span>✎</span> Perbaiki Data
                </a>

                <a href="{{ route('masyarakat.pengajuan.create') }}" class="dashboard-btn dashboard-btn-primary">
                    <span>＋</span> Ajukan Bantuan
                </a>
            </div>
        </div>

        @if($p)

            {{-- BANNER STATUS --}}
            <section class="citizen-status-banner">

                <div class="banner-content">
                    <div class="banner-status">
                        <span class="banner-status-dot"></span>
                        {{ $statusLabel }}
                        @if($pengajuan?->jenisBantuan)
                            · {{ $pengajuan->jenisBantuan->nama_program }}
                        @endif
                    </div>

                    <span class="banner-caption">DATA PENERIMA BANTUAN SOSIAL</span>

                    <h2>{{ $p->nama }}</h2>

                    <div class="banner-information">
                        <div>
                            <span>NIK</span>
                            <strong>{{ $nikTampil }}</strong>
                        </div>

                        <div>
                            <span>Wilayah</span>
                            <strong>{{ $lokasi ?: 'Wilayah belum tersedia' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="banner-decoration" aria-hidden="true">
                    <span class="banner-decoration-circle circle-one"></span>
                    <span class="banner-decoration-circle circle-two"></span>
                </div>

            </section>

            {{-- KARTU RINGKASAN --}}
            <section class="dashboard-summary-grid">

                {{-- BANTUAN TERAKHIR --}}
                <article class="summary-card">
                    <div class="summary-card-top">
                        <span class="summary-icon green">↗</span>
                        <span class="summary-label">Bantuan Terakhir</span>
                    </div>

                    @if($riwayatTerakhir)
                        <h3>
                            @if($riwayatTerakhir->jumlah !== null)
                                Rp{{ number_format((float) $riwayatTerakhir->jumlah, 0, ',', '.') }}
                            @else
                                Tercatat
                            @endif
                        </h3>

                        <p>
                            {{ $riwayatTerakhir->jenis?->nama_program ?? 'Program bantuan' }}
                        </p>

                        <small>
                            Diterima
                            {{ $riwayatTerakhir->tanggal_terima
                        ? $riwayatTerakhir->tanggal_terima->translatedFormat('d M Y')
                        : '-' }}
                        </small>
                    @else
                        <h3 class="summary-empty">Belum ada</h3>
                        <p>Belum terdapat penyaluran bantuan.</p>
                    @endif
                </article>

                {{-- PENGAJUAN AKTIF --}}
                <article class="summary-card">
                    <div class="summary-card-top">
                        <span class="summary-icon blue">▤</span>
                        <span class="summary-label">Pengajuan Aktif</span>
                    </div>

                    <h3>{{ $p->pengajuan_aktif_count ?? 0 }} Pengajuan</h3>

                    <p>
                        {{ $pengajuan?->jenisBantuan?->nama_program ?? 'Belum ada pengajuan aktif' }}
                    </p>

                    <a href="{{ route('masyarakat.status') }}" class="summary-link">
                        Lihat status →
                    </a>
                </article>

                {{-- DOKUMEN --}}
                <article class="summary-card">
                    <div class="summary-card-top">
                        <span class="summary-icon orange">▧</span>
                        <span class="summary-label">Dokumen Terunggah</span>
                    </div>

                    <h3>{{ $p->dokumen_count ?? 0 }} Dokumen</h3>

                    <div class="summary-document-list">
                        @forelse($p->dokumen->take(3) as $dokumen)
                            <span>{{ $dokumenLabel[$dokumen->jenis_dokumen] ?? 'Dokumen lainnya' }}</span>
                        @empty
                            <span>Belum ada dokumen terunggah</span>
                        @endforelse
                    </div>
                </article>

                {{-- DATA DIPERBARUI --}}
                <article class="summary-card">
                    <div class="summary-card-top">
                        <span class="summary-icon purple">◷</span>
                        <span class="summary-label">Data Diperbarui</span>
                    </div>

                    <h3 class="summary-date">
                        {{ $p->updated_at?->translatedFormat('d M Y') ?? '-' }}
                    </h3>

                    <p>Pembaruan terakhir data penerima.</p>
                </article>

            </section>

            {{-- BAGIAN BAWAH --}}
            <section class="dashboard-bottom-grid">

                {{-- ALUR PENGAJUAN --}}
                <article class="dashboard-panel">
                    <div class="panel-heading">
                        <div>
                            <h3>Alur Pengajuan Anda</h3>
                            <p>Perkembangan proses pengajuan bantuan.</p>
                        </div>

                        <a href="{{ route('masyarakat.status') }}">Detail →</a>
                    </div>

                    <div class="application-timeline">

                        <div class="timeline-item completed">
                            <span class="timeline-marker">✓</span>
                            <div>
                                <strong>Pendaftaran Masyarakat</strong>
                                <p>Data diri berhasil tercatat dalam sistem.</p>
                                <small>{{ $p->created_at?->translatedFormat('d M Y') ?? '-' }}</small>
                            </div>
                        </div>

                        @if($pengajuan)
                            <div class="timeline-item completed">
                                <span class="timeline-marker">✓</span>
                                <div>
                                    <strong>Pengajuan Bantuan</strong>
                                    <p>
                                        {{ $pengajuan->jenisBantuan?->nama_program ?? 'Program bantuan' }}
                                    </p>
                                    <small>{{ $pengajuan->created_at?->translatedFormat('d M Y') ?? '-' }}</small>
                                </div>
                            </div>

                            <div class="timeline-item {{ $pengajuan->tanggal_verifikasi ? 'completed' : 'current' }}">
                                <span class="timeline-marker">
                                    {{ $pengajuan->tanggal_verifikasi ? '✓' : '3' }}
                                </span>
                                <div>
                                    <strong>Verifikasi Pengajuan</strong>
                                    <p>{{ $statusLabel }}</p>

                                    @if($pengajuan->tanggal_verifikasi)
                                        <small>
                                            {{ $pengajuan->tanggal_verifikasi->translatedFormat('d M Y') }}
                                        </small>
                                    @else
                                        <small>Menunggu proses verifikasi.</small>
                                    @endif

                                    @if($pengajuan->catatan_verifikasi)
                                        <p class="timeline-note">
                                            {{ $pengajuan->catatan_verifikasi }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="timeline-item pending">
                                <span class="timeline-marker">2</span>
                                <div>
                                    <strong>Pengajuan Bantuan</strong>
                                    <p>Anda belum mengajukan program bantuan.</p>
                                </div>
                            </div>
                        @endif

                        @php
                            $riwayatPengajuanTerbaru = $pengajuan?->riwayat?->sortByDesc('tanggal_terima')->first();
                        @endphp

                        @if($riwayatPengajuanTerbaru)
                            <div class="timeline-item completed">
                                <span class="timeline-marker">✓</span>
                                <div>
                                    <strong>Penyaluran Bantuan</strong>
                                    <p>Bantuan telah tercatat diterima.</p>
                                    <small>
                                        {{ $riwayatPengajuanTerbaru->tanggal_terima?->translatedFormat('d M Y') ?? '-' }}
                                    </small>
                                </div>
                            </div>
                        @else
                            <div class="timeline-item pending">
                                <span class="timeline-marker">4</span>
                                <div>
                                    <strong>Penyaluran Bantuan</strong>
                                    <p>Belum terdapat catatan penerimaan bantuan.</p>
                                </div>
                            </div>
                        @endif

                    </div>
                </article>

                {{-- RIWAYAT BANTUAN --}}
                <article class="dashboard-panel">
                    <div class="panel-heading">
                        <div>
                            <h3>Riwayat Bantuan</h3>
                            <p>Catatan bantuan yang pernah diterima.</p>
                        </div>

                        <a href="{{ route('masyarakat.riwayat') }}">Lihat semua →</a>
                    </div>

                    <div class="history-list">

                        @forelse($p->riwayat as $riwayat)
                            <div class="history-item">
                                <span class="history-icon">✓</span>

                                <div class="history-information">
                                    <strong>
                                        {{ $riwayat->jenis?->nama_program ?? 'Program bantuan' }}
                                    </strong>

                                    <small>
                                        {{ $riwayat->tanggal_terima?->translatedFormat('d M Y') ?? '-' }}
                                    </small>
                                </div>

                                <div class="history-amount">
                                    @if($riwayat->jumlah !== null)
                                        Rp{{ number_format((float) $riwayat->jumlah, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="dashboard-empty">
                                <span>▤</span>
                                <p>Belum ada riwayat bantuan yang tercatat.</p>
                            </div>
                        @endforelse

                    </div>

                    @if($p->riwayat_count > 5)
                        <div class="panel-footer-link">
                            <a href="{{ route('masyarakat.riwayat') }}">
                                Lihat seluruh riwayat bantuan →
                            </a>
                        </div>
                    @endif

                </article>

            </section>

            {{-- STATUS PERBAIKAN DATA --}}
            <section class="dashboard-panel correction-panel">
                <div class="panel-heading">
                    <div>
                        <h3>Perbaikan Data</h3>
                        <p>Status permohonan perubahan data penerima.</p>
                    </div>

                    <a href="{{ route('masyarakat.perbaikan.create') }}">Ajukan perbaikan →</a>
                </div>

                @if($perbaikanTerakhir)
                    <div class="correction-status-row">
                        <div>
                            <strong>
                                Permohonan perbaikan data
                            </strong>
                            <p>
                                Diajukan
                                {{ $perbaikanTerakhir->created_at?->translatedFormat('d M Y') ?? '-' }}
                            </p>

                            @if($perbaikanTerakhir->catatan_admin)
                                <small>{{ $perbaikanTerakhir->catatan_admin }}</small>
                            @endif
                        </div>

                        <span class="correction-badge {{ $perbaikanTerakhir->status }}">
                            {{ ucfirst($perbaikanTerakhir->status) }}
                        </span>
                    </div>
                @else
                    <div class="dashboard-empty correction-empty">
                        <span>✓</span>
                        <p>Belum ada permohonan perbaikan data.</p>
                    </div>
                @endif
            </section>

        @else

            {{-- JIKA PROFIL PENERIMA BELUM TERSEDIA --}}
            <section class="dashboard-panel dashboard-no-data">
                <div class="no-data-icon">◎</div>
                <h2>Data masyarakat belum tersedia</h2>
                <p>
                    Akun Anda belum terhubung dengan data penerima.
                    Silakan hubungi administrator untuk mendapatkan bantuan.
                </p>
            </section>

        @endif

    </div>

@endsection