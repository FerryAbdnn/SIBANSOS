@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/verifikasi-pengajuan-admin.css') }}">
@endpush

@section('title', 'Verifikasi Pengajuan')
@section('heading', 'Verifikasi Pengajuan')

@section('content')
    <div class="verifikasi-page">

        {{-- Header --}}
        <div class="verifikasi-header">
            <div>
                <h2>Verifikasi Pengajuan</h2>
                <p>
                    Periksa data penerima dan kelengkapan dokumen sebelum mengambil keputusan.
                </p>
            </div>
        </div>


        {{-- Statistik --}}
        <div class="verifikasi-stats">
            <div class="verifikasi-stat">
                <div class="verifikasi-stat-icon" aria-hidden="true">⏳</div>
                <div>
                    <div class="verifikasi-stat-label">Menunggu Verifikasi</div>
                    <div class="verifikasi-stat-value">{{ $totalMenunggu ?? 0 }}</div>
                </div>
            </div>

            <div class="verifikasi-stat">
                <div class="verifikasi-stat-icon" aria-hidden="true">✅</div>
                <div>
                    <div class="verifikasi-stat-label">Terverifikasi</div>
                    <div class="verifikasi-stat-value">{{ $totalTerverifikasi ?? 0 }}</div>
                </div>
            </div>

            <div class="verifikasi-stat">
                <div class="verifikasi-stat-icon" aria-hidden="true">❌</div>
                <div>
                    <div class="verifikasi-stat-label">Ditolak</div>
                    <div class="verifikasi-stat-value">{{ $totalDitolak ?? 0 }}</div>
                </div>
            </div>
        </div>

        {{-- Workspace --}}
        <div class="verifikasi-workspace">

            {{-- Kolom kiri: antrean --}}
            <div class="verifikasi-main-column">
                <div class="verifikasi-card">

                    <div class="verifikasi-card-header">
                        <div>
                            <h3>Daftar Pengajuan</h3>
                            <p>{{ $pengajuan->total() }} pengajuan menunggu verifikasi</p>
                        </div>

                        <span class="verifikasi-badge pending">Menunggu</span>
                    </div>

                    <div class="verifikasi-queue">
                        @forelse($pengajuan as $p)
                            @php
                                $snapshot = $p->data_penerima ?? [];
                                $isActive = $pilihan
                                    && $pilihan->id_pengajuan === $p->id_pengajuan;
                            @endphp

                            <a href="{{ route('admin.pengajuan.index', ['pilih' => $p->id_pengajuan]) }}"
                                class="verifikasi-queue-item {{ $isActive ? 'active' : '' }}">
                                <div class="verifikasi-queue-content">
                                    <div class="verifikasi-queue-name">
                                        {{ $p->penerima->nama ?? 'Nama tidak tersedia' }}
                                    </div>

                                    <div class="verifikasi-queue-meta">
                                        <span>
                                            {{ $p->jenisBantuan->nama_program ?? 'Program tidak tersedia' }}
                                        </span>
                                        <span>
                                            {{ $p->created_at?->format('d/m/Y') ?? '-' }}
                                        </span>
                                    </div>

                                    <div class="verifikasi-queue-program">
                                        @if(!empty($snapshot['nama_rt']) || !empty($snapshot['nama_rw']))
                                            <span class="verifikasi-queue-location">
                                                <i class="bi bi-geo-alt"></i>

                                                @if(!empty($snapshot['nama_rt']))
                                                    <span>RT {{ $snapshot['nama_rt'] }}</span>
                                                @endif

                                                @if(!empty($snapshot['nama_rw']))
                                                    <span>RW {{ $snapshot['nama_rw'] }}</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="verifikasi-queue-location-empty">
                                                <i class="bi bi-geo-alt"></i>
                                                Wilayah belum tersedia
                                            </span>
                                        @endif

                                        @if(!empty($snapshot['alamat']))
                                            <div class="verifikasi-queue-address">
                                                <i class="bi bi-house-door"></i>
                                                <span>{{ $snapshot['alamat'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="verifikasi-queue-time">
                                    {{ $p->created_at?->diffForHumans() ?? '-' }}
                                </div>
                            </a>
                        @empty
                            <div class="verifikasi-empty">
                                <i class="bi bi-inbox" style="font-size: 28px;"></i>
                                <p>Belum ada pengajuan yang menunggu verifikasi.</p>
                            </div>
                        @endforelse
                    </div>

                    <div style="padding: 15px 18px;">
                        <x-pagination :paginator="$pengajuan" />
                    </div>
                </div>
            </div>

            {{-- Kolom kanan: detail --}}
            <div class="verifikasi-side-column">

                @if($pilihan)
                        @php
                            $snapshot = $pilihan->data_penerima ?? [];
                            $dokumen = $pilihan->dokumen ?? collect();

                            $labelDokumen = [
                                'ktp' => 'Kartu Tanda Penduduk',
                                'kk' => 'Kartu Keluarga',
                                'dtks' => 'Dokumen DTKS / P3KE',
                                'foto_depan' => 'Foto Tampak Depan Rumah',
                                'foto_dalam' => 'Foto Bagian Dalam Rumah',
                                'sktm' => 'Surat Keterangan Tidak Mampu',
                            ];
                        @endphp

                        <div class="verifikasi-card">
                            <div class="verifikasi-card-header">
                                <div>
                                    <h3>Detail Pengajuan</h3>
                                    <p>
                                        Pengajuan #{{ $pilihan->id_pengajuan }}
                                    </p>
                                </div>

                                <span class="verifikasi-badge pending">
                                    Menunggu Verifikasi
                                </span>
                            </div>

                            <div class="verifikasi-detail">

                                {{-- Identitas --}}
                                <div class="verifikasi-detail-heading">
                                    <div>
                                        <h3 class="verifikasi-detail-name">
                                            {{ $pilihan->penerima->nama ?? 'Nama tidak tersedia' }}
                                        </h3>
                                        <div class="verifikasi-detail-code">
                                            Nomor pengajuan:
                                            #{{ $pilihan->id_pengajuan }}
                                        </div>
                                    </div>
                                </div>

                                {{-- Informasi program --}}
                                <div class="verifikasi-info-section">
                                    <h4 class="verifikasi-info-title">
                                        <i class="bi bi-clipboard-check"></i>
                                        Informasi Pengajuan
                                    </h4>

                                    <div class="verifikasi-info-grid">
                                        <div class="verifikasi-info-item">
                                            <span class="verifikasi-info-label">Program Bantuan</span>
                                            <span class="verifikasi-info-value">
                                                {{ $pilihan->jenisBantuan->nama_program ?? '-' }}
                                            </span>
                                        </div>

                                        <div class="verifikasi-info-item">
                                            <span class="verifikasi-info-label">Tanggal Pengajuan</span>
                                            <span class="verifikasi-info-value">
                                                {{ $pilihan->created_at?->format('d F Y') ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Data penerima --}}
                                <div class="verifikasi-info-section">
                                    <h4 class="verifikasi-info-title">
                                        <i class="bi bi-person-vcard"></i>
                                        Data Penerima
                                    </h4>

                                    <div class="verifikasi-info-grid">
                                        <div class="verifikasi-info-item">
                                            <span class="verifikasi-info-label">NIK</span>
                                            <span class="verifikasi-info-value">
                                                {{ $pilihan->penerima->nik_decrypted ?? '-' }}
                                            </span>
                                        </div>

                                        <div class="verifikasi-info-item">
                                            <span class="verifikasi-info-label">Nomor KK</span>
                                            <span class="verifikasi-info-value">
                                                {{ $pilihan->penerima->no_kk_decrypted ?? '-' }}
                                            </span>
                                        </div>

                                        <div class="verifikasi-info-item">
                                            <span class="verifikasi-info-label">Tanggal Lahir</span>
                                            <span class="verifikasi-info-value">
                                                @if($pilihan->penerima?->tanggal_lahir)
                                                    {{ \Illuminate\Support\Carbon::parse($pilihan->penerima->tanggal_lahir)->locale('id')->translatedFormat('d F Y') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>

                                        <div class="verifikasi-info-item">
                                            <span class="verifikasi-info-label">Nomor WhatsApp</span>
                                            <span class="verifikasi-info-value">
                                                {{ $snapshot['nomor_whatsapp'] ?? '-' }}
                                            </span>
                                        </div>

                                        <div class="verifikasi-info-item">
                                            <span class="verifikasi-info-label">Alamat</span>
                                            <span class="verifikasi-info-value">
                                                {{ $snapshot['alamat'] ?? '-' }}
                                            </span>
                                        </div>

                                        <div class="verifikasi-info-item">
                                            <span class="verifikasi-info-label">Wilayah</span>
                                            <span class="verifikasi-info-value">
                                                <span>RT/{{ $snapshot['nama_rt'] ?? '-' }}</span>
                                                <span>RW/{{ $snapshot['nama_rw'] ?? '-' }}</span>
                                            </span>
                                        </div>

                                        <div class="verifikasi-info-item">
                                            <span class="verifikasi-info-label">Penghasilan Bulanan</span>
                                            <span class="verifikasi-info-value">
                                                @if(isset($snapshot['penghasilan_bulanan']))
                                                    Rp{{ number_format((float) $snapshot['penghasilan_bulanan'], 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>

                                        <div class="verifikasi-info-item">
                                            <span class="verifikasi-info-label">Jumlah Tanggungan</span>
                                            <span class="verifikasi-info-value">
                                                {{ $snapshot['jumlah_tanggungan'] ?? '-' }} orang
                                            </span>
                                        </div>

                                        <div class="verifikasi-info-item">
                                            <span class="verifikasi-info-label">Kondisi Rumah</span>
                                            <span class="verifikasi-info-value">
                                                {{ isset($snapshot['kondisi_rumah'])
                    ? ucwords(str_replace('_', ' ', $snapshot['kondisi_rumah']))
                    : '-' }}
                                            </span>
                                        </div>

                                        <div class="verifikasi-info-item">
                                            <span class="verifikasi-info-label">Disabilitas</span>
                                            <span class="verifikasi-info-value">
                                                {{ !empty($snapshot['disabilitas']) ? 'Ya' : 'Tidak' }}
                                            </span>
                                        </div>

                                        <div class="verifikasi-info-item">
                                            <span class="verifikasi-info-label">Lansia</span>
                                            <span class="verifikasi-info-value">
                                                {{ !empty($snapshot['lansia']) ? 'Ya' : 'Tidak' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Dokumen --}}
                                <div class="verifikasi-info-section">
                                    <h4 class="verifikasi-info-title">
                                        <i class="bi bi-folder2-open"></i>
                                        Dokumen Pendukung
                                    </h4>

                                    @if($dokumen->isNotEmpty())
                                        <div class="verifikasi-document-list">
                                            @foreach($dokumen as $doc)
                                                                <div class="verifikasi-document-item">
                                                                    <div class="verifikasi-document-info">
                                                                        <div class="verifikasi-document-icon" aria-hidden="true">
                                                                            @switch($doc->jenis_dokumen)
                                                                                @case('ktp')
                                                                                    🪪
                                                                                    @break

                                                                                @case('kk')
                                                                                    👨‍👩‍👧‍👦
                                                                                    @break

                                                                                @case('surat_keterangan')
                                                                                    📄
                                                                                    @break

                                                                                @case('lainnya')
                                                                                    @php
                                                                                        $namaDokumen = strtolower($doc->nama_file ?? '');
                                                                                    @endphp

                                                                                    @if(str_contains($namaDokumen, 'dtks') || str_contains($namaDokumen, 'p3ke'))
                                                                                        📋
                                                                                    @elseif(str_contains($namaDokumen, 'depan'))
                                                                                        🏠
                                                                                    @elseif(str_contains($namaDokumen, 'dalam'))
                                                                                        🛋️
                                                                                    @else
                                                                                        📁
                                                                                    @endif
                                                                                    @break

                                                                                @default
                                                                                    📁
                                                                            @endswitch
                                                                        </div>
                                                                        <div>
                                                                            <div class="verifikasi-document-name">
                                                                                {{ $labelDokumen[$doc->jenis_dokumen] ?? ucwords(str_replace('_', ' ', $doc->jenis_dokumen)) }}
                                                                            </div>

                                                                            <div class="verifikasi-document-filename">
                                                                                {{ $doc->nama_file }}
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <a href="{{ route('admin.pengajuan.dokumen.show', [
                                                    'pengajuan' => $pilihan->id_pengajuan,
                                                    'dokumen' => $doc->id_dokumen,
                                                ]) }}" target="_blank" rel="noopener" class="verifikasi-document-link">
                                                                        <i class="bi bi-eye"></i>
                                                                        Lihat
                                                                    </a>
                                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="verifikasi-empty">
                                            Belum ada dokumen pendukung.
                                        </div>
                                    @endif
                                </div>

                                {{-- Pemeriksaan --}}
                                <div class="verifikasi-info-section">
                                    <h4 class="verifikasi-info-title">
                                        <i class="bi bi-shield-check"></i>
                                        Pemeriksaan Data
                                    </h4>

                                    <div class="verifikasi-check-list">
                                        <div class="verifikasi-check-item">
                                            <i class="bi bi-check-circle-fill"></i>
                                            Data pengajuan tersimpan dalam snapshot.
                                        </div>

                                        <div class="verifikasi-check-item">
                                            <i class="bi bi-check-circle-fill"></i>
                                            Dokumen terhubung dengan pengajuan ini.
                                        </div>

                                        <div class="verifikasi-check-item">
                                            <i class="bi bi-check-circle-fill"></i>
                                            Wilayah pengajuan mengikuti cakupan admin.
                                        </div>
                                    </div>
                                </div>

                                <div class="verifikasi-notice">
                                    <i class="bi bi-info-circle-fill"></i>
                                    <div>
                                        Pastikan kesesuaian identitas, kondisi penerima,
                                        wilayah, dan dokumen sebelum mengambil keputusan.
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Panel keputusan --}}
                        <div class="verifikasi-decision">
                            <h3 class="verifikasi-decision-title">
                                Keputusan Verifikasi
                            </h3>

                            <p class="verifikasi-decision-description">
                                Pilih tindakan setelah seluruh data dan dokumen diperiksa.
                            </p>

                            <div class="verifikasi-decision-actions">
                                <form class="verifikasi-approval-form"
                                    action="{{ route('admin.pengajuan.verifikasi', $pilihan->id_pengajuan) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="verifikasi-btn verifikasi-btn-primary">
                                        <i class="bi bi-check-lg"></i>
                                        Setujui Pengajuan
                                    </button>
                                </form>
                            </div>

                            <form class="verifikasi-rejection-form"
                                action="{{ route('admin.pengajuan.tolak', $pilihan->id_pengajuan) }}" method="POST">
                                @csrf
                                @method('PATCH')

                                <div class="verifikasi-form-group">
                                    <label for="catatan">
                                        Alasan Penolakan
                                        <span style="color: #a33b32;">*</span>
                                    </label>

                                    <textarea name="catatan" id="catatan" class="verifikasi-form-control" maxlength="1000" required
                                        placeholder="Tuliskan alasan penolakan secara jelas...">{{ old('catatan') }}</textarea>

                                    @error('catatan')
                                        <div class="verifikasi-form-error">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <button type="submit" class="verifikasi-btn verifikasi-btn-danger">
                                    <i class="bi bi-x-lg"></i>
                                    Tolak Pengajuan
                                </button>
                            </form>
                        </div>

                @else
                    <div class="verifikasi-card">
                        <div class="verifikasi-empty">
                            <i class="bi bi-inbox" style="font-size: 34px;"></i>
                            <p>Tidak ada pengajuan yang perlu diperiksa saat ini.</p>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection