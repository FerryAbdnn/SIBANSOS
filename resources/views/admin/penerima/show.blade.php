@extends('layouts.admin')

@section('title', 'Detail Penerima')
@section('heading', 'Detail Penerima')

@section('content')

    @php
        $nik = '-';

        try {
            $nik = decrypt($penerima->nik);
        } catch (\Throwable $e) {
            $nik = 'Tidak tersedia';
        }

        $noKk = $penerima->no_kk_decrypted;

        $rt = $penerima->wilayah;
        $rw = $rt && $rt->id_induk
            ? $wilayahMap->get($rt->id_induk)
            : null;

        $namaWilayah = $rt && $rt->tipe !== 'rt'
            ? $rt->nama
            : '-';

        $statusLabel = [
            'diajukan' => 'Menunggu Verifikasi',
            'terverifikasi' => 'Terverifikasi',
            'ditolak' => 'Ditolak',
            'nonaktif' => 'Nonaktif',
        ];

        $statusClass = [
            'diajukan' => 'waiting',
            'terverifikasi' => 'verified',
            'ditolak' => 'rejected',
            'nonaktif' => 'inactive',
        ];

        $jumlahRiwayat = $penerima->riwayat->count();

        $dokumenKtp = $penerima->dokumen
            ->where('jenis_dokumen', 'ktp');

        $dokumenKk = $penerima->dokumen
            ->where('jenis_dokumen', 'kk');
    @endphp

    <div class="recipient-detail-page">

        {{-- BREADCRUMB --}}
        <div class="recipient-detail-breadcrumb">
            <a href="{{ route('admin.penerima.index') }}">Data Penerima</a>
            <span>/</span>
            <strong>
                PNR-{{ $penerima->created_at?->format('Y') ?? date('Y') }}-{{ str_pad($penerima->id_penerima, 5, '0', STR_PAD_LEFT) }}
            </strong>
        </div>

        {{-- HEADER --}}
        <div class="recipient-detail-header">

            <div class="recipient-detail-person">

                <div class="recipient-avatar">
                    {{ strtoupper(substr($penerima->nama, 0, 1)) }}
                </div>

                <div>
                    <div class="recipient-detail-name">
                        <h2>{{ $penerima->nama }}</h2>

                        <span class="recipient-status {{ $statusClass[$penerima->status] ?? 'inactive' }}">
                            {{ $statusLabel[$penerima->status] ?? ucfirst($penerima->status) }}
                        </span>
                    </div>

                    <p>
                        PNR-{{ $penerima->created_at?->format('Y') ?? date('Y') }}-{{ str_pad($penerima->id_penerima, 5, '0', STR_PAD_LEFT) }}
                        · NIK {{ $nik }}
                        · No. KK {{ $noKk }}
                    </p>
                </div>

            </div>

            <div class="recipient-detail-actions">

                <a href="{{ route('admin.penerima.edit', $penerima) }}" class="recipient-detail-btn outline">
                    ✎ Edit Data
                </a>

                <a href="{{ route('admin.penerima.index') }}" class="recipient-detail-btn primary">
                    ← Kembali
                </a>

            </div>

        </div>

        {{-- CONTENT GRID --}}
        <div class="recipient-detail-layout">

            <main class="recipient-detail-main">

                {{-- SUMMARY --}}
                <div class="recipient-summary-grid">

                    <div class="recipient-summary-card">
                        <span>Total Diterima</span>
                        <strong>
                            Rp {{ number_format($totalDiterima, 0, ',', '.') }}
                        </strong>
                        <small>{{ $jumlahRiwayat }} kali penyaluran</small>
                    </div>

                    <div class="recipient-summary-card">
                        <span>Kategori Aktif</span>
                        <strong>
                            {{ $kategoriAktif->isNotEmpty()
                                ? $kategoriAktif->first()
                                : 'Belum ada' }}
                        </strong>
                        <small>
                            {{ $kategoriAktif->count() }} kategori bantuan
                        </small>
                    </div>

                    <div class="recipient-summary-card">
                        <span>Terdaftar Sejak</span>
                        <strong>
                            {{ $penerima->created_at?->format('M Y') ?? '-' }}
                        </strong>
                        <small>Data penerima terdaftar</small>
                    </div>

                    <div class="recipient-summary-card">
                        <span>Tanggungan</span>
                        <strong>{{ $penerima->jumlah_tanggungan ?? 0 }}</strong>
                        <small>Jumlah tanggungan keluarga</small>
                    </div>

                </div>

                {{-- RIWAYAT BANTUAN --}}
                <section class="recipient-detail-card">

                    <div class="recipient-detail-card-heading">
                        <div>
                            <h3>Riwayat Bantuan</h3>
                            <p>Rekam jejak penyaluran bantuan penerima.</p>
                        </div>

                        <span class="recipient-count">
                            {{ $jumlahRiwayat }} tercatat
                        </span>
                    </div>

                    <div class="recipient-history-scroll">
                        <table class="recipient-history-table">

                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Kategori</th>
                                    <th>Program</th>
                                    <th>Nominal / Jumlah</th>
                                    <th>Petugas</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($penerima->riwayat->sortByDesc('tanggal_terima') as $r)

                                    <tr>
                                        <td>
                                            {{ $r->tanggal_terima?->format('d M Y') ?? '-' }}
                                        </td>

                                        <td>
                                            <span class="recipient-category">
                                                {{ $r->jenis->kategori->nama ?? '-' }}
                                            </span>
                                        </td>

                                        <td>
                                            <strong>
                                                {{ $r->jenis->nama_program ?? '-' }}
                                            </strong>
                                        </td>

                                        <td>
                                            @if ($r->jumlah !== null)
                                                {{ number_format((float) $r->jumlah, 0, ',', '.') }}
                                                {{ $r->satuan }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <td>
                                            Petugas #{{ $r->dicatat_oleh }}
                                        </td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5">
                                            <div class="recipient-detail-empty">
                                                Belum ada riwayat penyaluran bantuan.
                                            </div>
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>
                    </div>

                    <div class="recipient-history-note">
                        ⓘ Riwayat penyaluran bantuan disimpan sebagai catatan administrasi.
                    </div>

                </section>

                {{-- DOKUMEN --}}
                <section class="recipient-detail-card">

                    <div class="recipient-detail-card-heading">
                        <div>
                            <h3>Dokumen Pendukung</h3>
                            <p>Dokumen identitas yang tersimpan dalam sistem.</p>
                        </div>

                        <span class="recipient-count">
                            {{ $penerima->dokumen->count() }} dokumen
                        </span>
                    </div>

                    <div class="recipient-document-grid">

                        @foreach ([
                                'ktp' => ['label' => 'Kartu Tanda Penduduk', 'items' => $dokumenKtp],
                                'kk' => ['label' => 'Kartu Keluarga', 'items' => $dokumenKk],
                            ] as $jenis => $data)

                            <div class="recipient-document-item">
                                <div class="recipient-document-icon">▤</div>

                                <div class="recipient-document-info">
                                    <strong>{{ $data['label'] }}</strong>

                                    @if ($data['items']->isNotEmpty())
                                        @foreach ($data['items'] as $dokumen)
                                            <a href="{{ asset('storage/' . $dokumen->path_file) }}" target="_blank" rel="noopener">
                                                {{ $dokumen->nama_file }}
                                            </a>
                                        @endforeach
                                    @else
                                        <small>Belum ada dokumen</small>
                                    @endif
                                </div>
                            </div>

                        @endforeach

                    </div>

                </section>

            </main>

            <aside class="recipient-detail-sidebar">

                {{-- IDENTITAS --}}
                <section class="recipient-detail-card">

                    <div class="recipient-detail-card-heading">
                        <h3>Identitas & Wilayah</h3>
                    </div>

                    <div class="recipient-info-list">

                        <div>
                            <small>NIK</small>
                            <strong>{{ $nik }}</strong>
                        </div>

                        <div>
                            <small>Nomor Kartu Keluarga (KK)</small>
                            <strong>{{ $noKk }}</strong>
                        </div>

                        <div>
                            <small>Nama Lengkap</small>
                            <strong>{{ $penerima->nama }}</strong>
                        </div>

                        <div>
                            <small>Tanggal Lahir</small>
                            <strong>
                                {{ $penerima->tanggal_lahir?->translatedFormat('d F Y') ?? '-' }}
                            </strong>
                        </div>

                        <div>
                            <small>Jenis Kelamin</small>
                            <strong>
                                {{ $penerima->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </strong>
                        </div>

                        <div>
                            <small>RT / RW</small>
                            <strong>
                                {{ $rt && $rt->tipe === 'rt' ? $rt->nama : '-' }}
                                /
                                {{ $rw && $rw->tipe === 'rw' ? $rw->nama : '-' }}
                            </strong>
                        </div>

                        <div>
                            <small>Wilayah</small>
                            <strong>{{ $namaWilayah }}</strong>
                        </div>

                        <div>
                            <small>Alamat Lengkap</small>
                            <strong>{{ $penerima->alamat }}</strong>
                        </div>

                    </div>

                </section>

                {{-- KONDISI EKONOMI --}}
                <section class="recipient-detail-card">

                    <div class="recipient-detail-card-heading">
                        <h3>Kondisi Ekonomi</h3>
                    </div>

                    <div class="recipient-economy-box">
                        <div>
                            <small>Penghasilan Bulanan</small>
                            <strong>
                                Rp {{ number_format((float) $penerima->penghasilan_bulanan, 0, ',', '.') }}
                            </strong>
                        </div>

                        <div>
                            <small>Jumlah Tanggungan</small>
                            <strong>
                                {{ $penerima->jumlah_tanggungan }} orang
                            </strong>
                        </div>

                        <div>
                            <small>Kondisi Rumah</small>
                            <strong>
                                {{ [
        'permanen' => 'Permanen',
        'semi_permanen' => 'Semi permanen',
        'tidak_layak' => 'Tidak layak',
    ][$penerima->kondisi_rumah] ?? '-' }}
                            </strong>
                        </div>
                    </div>

                    <div class="recipient-detail-tags">
                        <span>
                            {{ $penerima->penghasilan_bulanan < 1000000
        ? 'Penghasilan < Rp 1 jt'
        : 'Penghasilan ≥ Rp 1 jt' }}
                        </span>

                        <span>
                            {{ $penerima->disabilitas ? 'Disabilitas' : 'Tidak disabilitas' }}
                        </span>

                        <span>
                            {{ $penerima->lansia ? 'Lansia' : 'Bukan lansia' }}
                        </span>
                    </div>

                </section>

                {{-- STATUS VERIFIKASI --}}
                <section class="recipient-detail-card recipient-verification-card">

                    <div class="recipient-verification-icon">✓</div>

                    <div>
                        <strong>
                            {{ $statusLabel[$penerima->status] ?? ucfirst($penerima->status) }}
                        </strong>

                        @if ($penerima->tanggal_verifikasi)
                            <small>
                                {{ \Carbon\Carbon::parse($penerima->tanggal_verifikasi)->translatedFormat('d F Y') }}
                            </small>
                        @else
                            <small>
                                Belum ada tanggal verifikasi.
                            </small>
                        @endif

                        @if ($penerima->catatan_verifikasi)
                            <p>{{ $penerima->catatan_verifikasi }}</p>
                        @endif
                    </div>

                </section>

            </aside>

        </div>

        {{-- ACTION --}}
        <div class="recipient-detail-bottom-actions">

            @if ($penerima->status !== 'nonaktif')
                <form method="POST" action="{{ route('admin.penerima.nonaktif', $penerima) }}"
                    onsubmit="return confirm('Nonaktifkan penerima ini?')">

                    @csrf
                    @method('PATCH')

                    <button type="submit" class="recipient-detail-btn warning">
                        Nonaktifkan Penerima
                    </button>
                </form>
            @endif

            @if (
                    !$penerima->riwayat()->exists() &&
                    !$penerima->dokumen()->exists() &&
                    !$penerima->pengajuanPerbaikan()->exists()
                )
                <form method="POST" action="{{ route('admin.penerima.destroy', $penerima) }}"
                    onsubmit="return confirm('Hapus data penerima ini secara permanen?')">

                    @csrf
                    @method('DELETE')

                    <button type="submit" class="recipient-detail-btn danger">
                        Hapus Data
                    </button>
                </form>
            @endif

        </div>

    </div>

@endsection

@push('styles')
    <style>
        /* =====================================================
               SIBANSOS - DETAIL PENERIMA
               Seluruh CSS khusus halaman Detail Penerima
            ====================================================== */

        /* BREADCRUMB */
        .recipient-detail-page {
            width: 100%;
            min-width: 0;
            color: #20391a;
        }

        .recipient-detail-breadcrumb {
            display: flex;
            align-items: center;
            gap: 9px;
            margin-bottom: 17px;
            color: #9aa292;
            font-size: 11px;
        }

        .recipient-detail-breadcrumb a {
            color: #89947f;
            text-decoration: none;
        }

        .recipient-detail-breadcrumb strong {
            color: #263c20;
            font-weight: 650;
        }

        /* HEADER */
        .recipient-detail-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .recipient-detail-person {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .recipient-avatar {
            width: 47px;
            height: 47px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: #174f37;
            border-radius: 50%;
            font-size: 15px;
            font-weight: 750;
        }

        .recipient-detail-name {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 9px;
        }

        .recipient-detail-name h2 {
            margin: 0;
            color: #20391a;
            font-size: 24px;
            font-weight: 750;
        }

        .recipient-detail-person>div>p {
            margin: 5px 0 0;
            color: #7f8976;
            font-size: 11px;
            overflow-wrap: anywhere;
        }

        .recipient-detail-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* STATUS */
        .recipient-status {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .recipient-status.waiting {
            color: #a16207;
            background: #fef3c7;
        }

        .recipient-status.verified {
            color: #166534;
            background: #dcfce7;
        }

        .recipient-status.rejected {
            color: #b91c1c;
            background: #fee2e2;
        }

        .recipient-status.inactive {
            color: #475569;
            background: #e2e8f0;
        }

        /* BUTTONS */
        .recipient-detail-btn {
            min-height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            border: 1px solid transparent;
            border-radius: 22px;
            font-family: inherit;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: .2s ease;
        }

        .recipient-detail-btn.outline {
            color: #174f37;
            background: #fff;
            border-color: #a9b69f;
        }

        .recipient-detail-btn.outline:hover {
            background: #edf4e7;
        }

        .recipient-detail-btn.primary {
            color: #fff;
            background: #174f37;
        }

        .recipient-detail-btn.primary:hover {
            background: #123d2b;
            color: #fff;
        }

        .recipient-detail-btn.warning {
            color: #8b671f;
            background: #fbf3df;
            border-color: #f0e3bd;
        }

        .recipient-detail-btn.danger {
            color: #a23e37;
            background: #fff0ee;
            border-color: #efd2ce;
        }

        /* MAIN LAYOUT */
        .recipient-detail-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 290px;
            align-items: start;
            gap: 16px;
        }

        .recipient-detail-main,
        .recipient-detail-sidebar {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* SUMMARY */
        .recipient-summary-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .recipient-summary-card {
            min-width: 0;
            padding: 17px 15px;
            background: #fff;
            border: 1px solid #e8ebe4;
            border-radius: 13px;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .025);
        }

        .recipient-summary-card>span {
            display: block;
            margin-bottom: 8px;
            color: #77816d;
            font-size: 10px;
        }

        .recipient-summary-card strong {
            display: block;
            overflow-wrap: anywhere;
            color: #174f37;
            font-size: 18px;
            font-weight: 750;
        }

        .recipient-summary-card small {
            display: block;
            margin-top: 6px;
            color: #929a89;
            font-size: 10px;
        }

        /* DETAIL CARDS */
        .recipient-detail-card {
            min-width: 0;
            padding: 18px;
            background: #fff;
            border: 1px solid #e8ebe4;
            border-radius: 13px;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .025);
        }

        .recipient-detail-card-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 14px;
        }

        .recipient-detail-card-heading h3 {
            margin: 0;
            color: #174f37;
            font-size: 13px;
            font-weight: 750;
        }

        .recipient-detail-card-heading p {
            margin: 5px 0 0;
            color: #929a89;
            font-size: 10px;
        }

        .recipient-count {
            padding: 5px 10px;
            color: #166534;
            background: #e9f3ec;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 700;
            white-space: nowrap;
        }

        /* HISTORY TABLE */
        .recipient-history-scroll {
            width: 100%;
            overflow-x: auto;
        }

        .recipient-history-table {
            width: 100%;
            min-width: 570px;
            border-collapse: collapse;
            font-size: 10px;
        }

        .recipient-history-table thead {
            background: #f7f8f4;
        }

        .recipient-history-table th {
            padding: 11px 9px;
            color: #66705e;
            text-align: left;
            font-size: 9px;
            font-weight: 700;
            white-space: nowrap;
            border-bottom: 1px solid #e9ece5;
        }

        .recipient-history-table td {
            padding: 12px 9px;
            color: #35452e;
            border-bottom: 1px solid #edf0e9;
            white-space: nowrap;
        }

        .recipient-history-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .recipient-history-table td strong {
            font-weight: 650;
        }

        .recipient-history-note {
            padding-top: 12px;
            color: #929a89;
            border-top: 1px solid #edf0e9;
            font-size: 10px;
        }

        .recipient-detail-empty {
            padding: 25px 10px;
            color: #929a89;
            text-align: center;
            white-space: normal;
        }

        .recipient-category {
            display: inline-block;
            padding: 4px 8px;
            color: #166534;
            background: #e9f3ec;
            border-radius: 5px;
            font-size: 10px;
        }

        /* IDENTITY */
        .recipient-info-list>div {
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 10px 0;
            border-bottom: 1px solid #edf0e9;
        }

        .recipient-info-list>div:last-child {
            padding-bottom: 0;
            border-bottom: 0;
        }

        .recipient-info-list small {
            color: #899380;
            font-size: 10px;
        }

        .recipient-info-list strong {
            color: #293e23;
            font-size: 11px;
            font-weight: 650;
            overflow-wrap: anywhere;
            line-height: 1.5;
        }

        /* ECONOMY */
        .recipient-economy-box {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 13px;
            margin-bottom: 13px;
            background: #f7f8f4;
            border-radius: 10px;
        }

        .recipient-economy-box>div {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .recipient-economy-box small {
            color: #77816d;
            font-size: 10px;
        }

        .recipient-economy-box strong {
            color: #34482b;
            text-align: right;
            font-size: 10px;
        }

        .recipient-detail-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .recipient-detail-tags span {
            padding: 5px 9px;
            color: #166534;
            background: #e9f3ec;
            border-radius: 20px;
            font-size: 9px;
            font-weight: 650;
        }

        /* DOCUMENTS */
        .recipient-document-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .recipient-document-item {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            min-width: 0;
            padding: 13px;
            background: #f8f9f6;
            border: 1px solid #e9ece5;
            border-radius: 9px;
        }

        .recipient-document-icon {
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #47613a;
            background: #eaf1e4;
            border-radius: 8px;
        }

        .recipient-document-info {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .recipient-document-info strong {
            color: #35472d;
            font-size: 10px;
        }

        .recipient-document-info a,
        .recipient-document-info small {
            overflow-wrap: anywhere;
            color: #718365;
            font-size: 10px;
            text-decoration: none;
        }

        .recipient-document-info a:hover {
            text-decoration: underline;
        }

        /* VERIFICATION */
        .recipient-verification-card {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .recipient-verification-icon {
            width: 31px;
            height: 31px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #166534;
            background: #e9f3ec;
            border-radius: 50%;
            font-weight: 750;
        }

        .recipient-verification-card strong {
            display: block;
            margin: 2px 0 6px;
            color: #344b2a;
            font-size: 11px;
        }

        .recipient-verification-card small,
        .recipient-verification-card p {
            display: block;
            margin: 0;
            color: #87917d;
            font-size: 10px;
            line-height: 1.6;
        }

        .recipient-verification-card p {
            margin-top: 7px;
        }

        /* BOTTOM ACTIONS */
        .recipient-detail-bottom-actions {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 15px;
        }

        .recipient-detail-bottom-actions form {
            margin: 0;
        }

        /* RESPONSIVE */
        @media (max-width: 1100px) {
            .recipient-detail-layout {
                grid-template-columns: minmax(0, 1fr) 260px;
            }

            .recipient-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 800px) {
            .recipient-detail-layout {
                grid-template-columns: 1fr;
            }

            .recipient-detail-sidebar {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                align-items: start;
            }

            .recipient-verification-card {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 600px) {
            .recipient-detail-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .recipient-detail-name h2 {
                font-size: 20px;
            }

            .recipient-detail-actions {
                width: 100%;
            }

            .recipient-detail-actions .recipient-detail-btn {
                flex: 1;
            }

            .recipient-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .recipient-detail-sidebar {
                display: flex;
            }

            .recipient-document-grid {
                grid-template-columns: 1fr;
            }

            .recipient-detail-card {
                padding: 14px;
            }
        }
    </style>
@endpush