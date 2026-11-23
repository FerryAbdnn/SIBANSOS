@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/perbaikan-admin.css') }}">
@endpush

@section('title', 'Perbaikan Data')
@section('heading', 'Pengajuan Perbaikan Data')

@section('content')
    <div class="perbaikan-page">

        {{-- Header halaman --}}
        <div class="perbaikan-header">
            <div>
                <span class="perbaikan-eyebrow">PENGELOLAAN DATA MASYARAKAT</span>
                <h2>Pengajuan Perbaikan Data</h2>
                <p>
                    Periksa perubahan data yang diajukan masyarakat sebelum
                    menyetujui atau menolaknya.
                </p>
            </div>

            <div class="perbaikan-header-icon" aria-hidden="true">📝</div>
        </div>

        {{-- Notifikasi --}}
        @if (session('warning'))
            <div class="perbaikan-alert warning">
                <span>⚠️</span>
                {{ session('warning') }}
            </div>
        @endif

        @if (session('success'))
            <div class="perbaikan-alert success">
                <span>✓</span>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="perbaikan-alert warning">
                <div>
                    <strong>Periksa kembali isian Anda.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Statistik --}}
        <div class="perbaikan-stats">

            <div class="perbaikan-stat">
                <div class="perbaikan-stat-icon waiting">⏳</div>
                <div>
                    <span class="perbaikan-stat-label">Menunggu Verifikasi</span>
                    <strong>{{ $statistik['menunggu'] ?? 0 }}</strong>
                    <small>Perlu ditinjau</small>
                </div>
            </div>

            <div class="perbaikan-stat">
                <div class="perbaikan-stat-icon approved">✓</div>
                <div>
                    <span class="perbaikan-stat-label">Disetujui</span>
                    <strong>{{ $statistik['disetujui'] ?? 0 }}</strong>
                    <small>Perubahan diterapkan</small>
                </div>
            </div>

            <div class="perbaikan-stat">
                <div class="perbaikan-stat-icon rejected">✕</div>
                <div>
                    <span class="perbaikan-stat-label">Ditolak</span>
                    <strong>{{ $statistik['ditolak'] ?? 0 }}</strong>
                    <small>Pengajuan tidak disetujui</small>
                </div>
            </div>

        </div>

        {{-- Daftar pengajuan --}}
        <div class="perbaikan-section-heading">
            <div>
                <h3>Daftar Permintaan Perubahan</h3>
                <p>Data yang ditampilkan sesuai wilayah tugas admin.</p>
            </div>

            <span class="perbaikan-total">
                {{ $items->total() }} Pengajuan
            </span>
        </div>

        <div class="perbaikan-list">

            @forelse ($items as $i)

                @php
                    $usulan = (array) ($i->data_usulan ?? []);
                    $penerima = $i->penerima;

                    $formatTanggal = function ($value) {
                        if (!$value) {
                            return '-';
                        }

                        try {
                            return \Carbon\Carbon::parse($value)->format('d/m/Y');
                        } catch (\Throwable $e) {
                            return $value;
                        }
                    };

                    $formatWilayah = function ($wilayah) {
                        if (!$wilayah) {
                            return '-';
                        }

                        $nama = $wilayah->nama ?? '-';
                        $induk = $wilayah->induk->nama ?? null;

                        return $nama . ($induk ? ' / ' . $induk : '');
                    };

                    $dataLama = [
                        'nama' => $penerima->nama ?? '-',
                        'nik' => $penerima->nik_decrypted ?? '-',
                        'no_kk' => $penerima->no_kk_decrypted ?? '-',
                        'tanggal_lahir' => $formatTanggal($penerima->tanggal_lahir ?? null),
                        'jenis_kelamin' => match ($penerima->jenis_kelamin ?? '') {
                            'L' => 'Laki-laki',
                            'P' => 'Perempuan',
                            default => '-',
                        },
                        'wilayah' => $formatWilayah($penerima->wilayah ?? null),
                        'alamat' => $penerima->alamat ?? '-',
                        'penghasilan_bulanan' => isset($penerima->penghasilan_bulanan)
                            ? 'Rp ' . number_format((float) $penerima->penghasilan_bulanan, 0, ',', '.')
                            : '-',
                        'jumlah_tanggungan' => isset($penerima->jumlah_tanggungan)
                            ? $penerima->jumlah_tanggungan . ' orang'
                            : '-',
                    ];

                    $wilayahBaru = isset($usulan['id_wilayah'])
                        ? ($wilayahUsulan[$usulan['id_wilayah']] ?? null)
                        : null;

                    $dataBaru = [
                        'nama' => $usulan['nama'] ?? null,
                        'nik' => isset($usulan['nik'])
                            ? decrypt($usulan['nik'])
                            : null,
                        'no_kk' => isset($usulan['no_kk'])
                            ? decrypt($usulan['no_kk'])
                            : null,
                        'tanggal_lahir' => isset($usulan['tanggal_lahir'])
                            ? $formatTanggal($usulan['tanggal_lahir'])
                            : null,
                        'jenis_kelamin' => isset($usulan['jenis_kelamin'])
                            ? match ($usulan['jenis_kelamin']) {
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                                default => $usulan['jenis_kelamin'],
                            }
                            : null,
                        'wilayah' => isset($usulan['id_wilayah'])
                            ? $formatWilayah($wilayahBaru)
                            : null,
                        'alamat' => $usulan['alamat'] ?? null,
                        'penghasilan_bulanan' => isset($usulan['penghasilan_bulanan'])
                            ? 'Rp ' . number_format((float) $usulan['penghasilan_bulanan'], 0, ',', '.')
                            : null,
                        'jumlah_tanggungan' => isset($usulan['jumlah_tanggungan'])
                            ? $usulan['jumlah_tanggungan'] . ' orang'
                            : null,
                    ];

                    $labelData = [
                        'nama' => 'Nama Lengkap',
                        'nik' => 'NIK',
                        'no_kk' => 'Nomor Kartu Keluarga',
                        'tanggal_lahir' => 'Tanggal Lahir',
                        'jenis_kelamin' => 'Jenis Kelamin',
                        'wilayah' => 'Wilayah RT / RW',
                        'alamat' => 'Alamat',
                        'penghasilan_bulanan' => 'Penghasilan Bulanan',
                        'jumlah_tanggungan' => 'Jumlah Tanggungan',
                    ];

                    $dokumenSuratRt = $i->dokumen_surat_rt ?? null;
                    $dokumenBerhentiKerja = $i->dokumen_berhenti_kerja ?? null;
                @endphp

                <article class="perbaikan-card">

                    {{-- Identitas pengajuan --}}
                    <div class="perbaikan-card-head">
                        <div class="perbaikan-person">
                            <div class="perbaikan-avatar" aria-hidden="true">
                                {{ strtoupper(substr($penerima->nama ?? 'M', 0, 1)) }}
                            </div>

                            <div>
                                <h4>{{ $penerima->nama ?? 'Data penerima tidak ditemukan' }}</h4>
                                <p>{{ $i->pengaju->email ?? 'Email tidak tersedia' }}</p>
                                <span class="perbaikan-date">
                                    Diajukan:
                                    {{ $i->created_at?->format('d/m/Y H:i') ?? '-' }}
                                </span>
                            </div>
                        </div>

                        <span class="perbaikan-status {{ $i->status }}">
                            @if ($i->status === 'menunggu')
                                ⏳ Menunggu
                            @elseif ($i->status === 'disetujui')
                                ✓ Disetujui
                            @elseif ($i->status === 'ditolak')
                                ✕ Ditolak
                            @else
                                {{ ucfirst($i->status) }}
                            @endif
                        </span>
                    </div>

                    {{-- Alasan pengajuan --}}
                    <div class="perbaikan-reason">
                        <span class="perbaikan-block-label">ALASAN PERBAIKAN</span>
                        <p>{{ $i->alasan ?: 'Tidak ada alasan yang dicantumkan.' }}</p>
                    </div>

                    {{-- Perbandingan data --}}
                    <div class="perbaikan-data-section">
                        <div class="perbaikan-block-heading">
                            <div>
                                <h5>Perbandingan Data</h5>
                                <p>Periksa data lama dan data yang diajukan masyarakat.</p>
                            </div>
                        </div>

                        <div class="perbaikan-comparison">
                            <div class="perbaikan-comparison-head">
                                <div>INFORMASI DATA</div>
                                <div>DATA SAAT INI</div>
                                <div>DATA USULAN</div>
                            </div>

                            @foreach ($labelData as $key => $label)
                                @if (array_key_exists($key, $dataBaru))
                                    <div class="perbaikan-comparison-row">
                                        <div class="perbaikan-field-label">
                                            {{ $label }}
                                        </div>

                                        <div class="perbaikan-old-value">
                                            {{ $dataLama[$key] }}
                                        </div>

                                        <div class="perbaikan-new-value">
                                            {{ $dataBaru[$key] }}
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Dokumen pendukung --}}
                    <div class="perbaikan-documents">
                        <div class="perbaikan-block-label">DOKUMEN PENDUKUNG</div>

                        <div class="perbaikan-document-list">

                            @if ($dokumenSuratRt)
                                <a href="{{ route('admin.perbaikan.dokumen.show', ['pengajuan' => $i->id_pengajuan, 'jenis' => 'surat-rt']) }}"
                                    target="_blank" rel="noopener" class="perbaikan-document-link">
                                    <span class="perbaikan-document-icon">📄</span>
                                    <span>
                                        <strong>Surat Keterangan RT</strong>
                                        <small>Lihat dokumen</small>
                                    </span>
                                    <span class="perbaikan-document-arrow">↗</span>
                                </a>
                            @else
                                <div class="perbaikan-document-empty">
                                    📄 Surat Keterangan RT — Tidak dilampirkan
                                </div>
                            @endif

                            @if ($dokumenBerhentiKerja)
                                <a href="{{ route('admin.perbaikan.dokumen.show', ['pengajuan' => $i->id_pengajuan, 'jenis' => 'berhenti-kerja']) }}"
                                    target="_blank" rel="noopener" class="perbaikan-document-link">
                                    <span class="perbaikan-document-icon">📎</span>
                                    <span>
                                        <strong>Surat Keterangan Berhenti Kerja</strong>
                                        <small>Lihat dokumen</small>
                                    </span>
                                    <span class="perbaikan-document-arrow">↗</span>
                                </a>
                            @else
                                <div class="perbaikan-document-empty">
                                    📎 Surat Berhenti Kerja — Tidak dilampirkan
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- Keputusan admin --}}
                    @if ($i->status === 'menunggu')
                        <div class="perbaikan-decision">
                            <div class="perbaikan-block-heading">
                                <div>
                                    <h5>Keputusan Admin</h5>
                                    <p>Pastikan seluruh perubahan sudah diperiksa sebelum mengambil keputusan.</p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('admin.perbaikan.proses', $i) }}" class="perbaikan-decision-form">
                                @csrf
                                @method('PATCH')

                                <label for="catatan_admin_{{ $i->id_pengajuan }}">
                                    Catatan Admin <span>(opsional)</span>
                                </label>

                                <textarea id="catatan_admin_{{ $i->id_pengajuan }}" name="catatan_admin" rows="3" maxlength="1000"
                                    placeholder="Tuliskan catatan atau alasan keputusan...">{{ old('catatan_admin') }}</textarea>

                                <div class="perbaikan-decision-actions">
                                    <button type="submit" name="status" value="ditolak" class="perbaikan-button reject"
                                        onclick="return confirm('Apakah Anda yakin ingin menolak pengajuan perbaikan ini?')">
                                        ✕ Tolak Pengajuan
                                    </button>

                                    <button type="submit" name="status" value="disetujui" class="perbaikan-button approve"
                                        onclick="return confirm('Setujui perubahan data ini? Data penerima akan diperbarui.')">
                                        ✓ Setujui Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="perbaikan-processed">
                            <div>
                                <strong>
                                    {{ $i->status === 'disetujui' ? '✓ Pengajuan telah disetujui' : '✕ Pengajuan telah ditolak' }}
                                </strong>

                                @if ($i->diproses_at)
                                    <small>
                                        Diproses pada {{ $i->diproses_at->format('d/m/Y H:i') }}
                                    </small>
                                @endif
                            </div>

                            @if ($i->catatan_admin)
                                <div class="perbaikan-admin-note">
                                    <span>Catatan Admin</span>
                                    <p>{{ $i->catatan_admin }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                </article>

            @empty

                <div class="perbaikan-empty">
                    <div class="perbaikan-empty-icon">📂</div>
                    <h4>Belum Ada Pengajuan Perbaikan</h4>
                    <p>
                        Belum ada permintaan perubahan data dari masyarakat
                        pada wilayah tugas Anda.
                    </p>
                </div>

            @endforelse

        </div>

        {{-- Pagination --}}
        @if ($items->hasPages())
            <div class="perbaikan-pagination">
                <x-pagination :paginator="$items" />
            </div>
        @endif

    </div>
@endsection