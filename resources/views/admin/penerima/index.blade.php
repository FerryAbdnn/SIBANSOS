
@extends('layouts.admin')

@section('title', 'Data Penerima')
@section('heading', 'Data Penerima')

@section('content')

@php
    $statusLabel = [
        'diajukan' => 'Menunggu Verifikasi',
        'terverifikasi' => 'Terverifikasi',
        'ditolak' => 'Ditolak',
        'nonaktif' => 'Nonaktif',
    ];

    $statusClass = [
        'diajukan' => 'warning',
        'terverifikasi' => 'success',
        'ditolak' => 'danger',
        'nonaktif' => 'secondary',
    ];
@endphp

<div class="penerima-page">

    {{-- HEADER HALAMAN --}}
    <div class="penerima-header">
        <div>
            <span class="penerima-eyebrow">MANAJEMEN DATA</span>
            <h2>Data Penerima Bantuan</h2>
            <p>
                Kelola dan pantau data masyarakat penerima bantuan sosial.
            </p>
        </div>

        <a href="{{ route('admin.penerima.create') }}"
           class="btn-primary-custom">
            <span>＋</span>
            Tambah Penerima
        </a>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="penerima-alert success-alert">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->has('delete'))
        <div class="penerima-alert danger-alert">
            {{ $errors->first('delete') }}
        </div>
    @endif

    @if($errors->any() && !$errors->has('delete'))
        <div class="penerima-alert danger-alert">
            Terdapat kesalahan pada proses sebelumnya.
            Silakan periksa kembali data yang dimasukkan.
        </div>
    @endif

    {{-- STATISTIK --}}
    <div class="penerima-stats">

        <div class="penerima-stat-card">
            <div class="stat-icon blue-icon">👥</div>
            <div>
                <span>Total Penerima</span>
                <h3>{{ number_format($totalPenerima, 0, ',', '.') }}</h3>
                <small>Seluruh data dalam cakupan wilayah</small>
            </div>
        </div>

        <div class="penerima-stat-card">
            <div class="stat-icon orange-icon">⏳</div>
            <div>
                <span>Menunggu Verifikasi</span>
                <h3>{{ number_format($menungguVerifikasi, 0, ',', '.') }}</h3>
                <small>Pengajuan yang perlu diperiksa</small>
            </div>
        </div>

    </div>

    {{-- FILTER DAN PENCARIAN --}}
    <div class="penerima-panel">

        <div class="penerima-panel-heading">
            <div>
                <h3>Daftar Penerima</h3>
                <p>Cari dan filter data penerima bantuan sosial.</p>
            </div>
        </div>

        <form action="{{ route('admin.penerima.index') }}"
              method="GET"
              class="penerima-filter">

            <div class="filter-search">
                <label for="q">Pencarian</label>
                <input
                    type="text"
                    id="q"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Cari nama, kode, atau NIK..."
                >
            </div>

            <div>
                <label for="kategori">Kategori Bantuan</label>
                <select name="kategori" id="kategori">
                    <option value="">Semua Kategori</option>

                    @foreach($kategori as $item)
                        <option
                            value="{{ $item->id_kategori }}"
                            {{ request('kategori') == $item->id_kategori ? 'selected' : '' }}
                        >
                            {{ $item->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status">Status</label>
                <select name="status" id="status">
                    <option value="">Semua Status</option>

                    @foreach($statusLabel as $value => $label)
                        <option
                            value="{{ $value }}"
                            {{ request('status') == $value ? 'selected' : '' }}
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="wilayah">Wilayah</label>
                <select name="wilayah" id="wilayah">
                    <option value="">Semua Wilayah</option>

                    @foreach($wilayah as $item)
                        <option
                            value="{{ $item->id_wilayah }}"
                            {{ request('wilayah') == $item->id_wilayah ? 'selected' : '' }}
                        >
                            {{ strtoupper($item->tipe) }} - {{ $item->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-filter">
                    Terapkan
                </button>

                <a href="{{ route('admin.penerima.index') }}"
                class="btn-reset">
                    Reset
                </a>
            </div>

        </form>

        {{-- TABEL DATA --}}
        <div class="penerima-table-wrapper">

            <table class="penerima-table">

                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Kode</th>
                        <th>Nama Penerima</th>
                        <th>NIK</th>
                        <th>RT / RW</th>
                        <th>Kategori Bantuan</th>
                        <th>Status</th>
                        <th>Total Diterima</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($penerima as $index => $p)

                        @php
                            try {
                                $nikTampil = decrypt($p->nik);
                            } catch (\Throwable $e) {
                                $nikTampil = 'Tidak tersedia';
                            }

                            $statusTampil = $p->status === 'nonaktif'
                                ? 'nonaktif'
                                : ($p->pengajuanTerbaru?->status ?? $p->status);

                            // Kode penerima
                            $tahun = $p->created_at
                                ? $p->created_at->format('Y')
                                : date('Y');

                            $kodePenerima = 'PNR-' . $tahun . '-' .
                                str_pad($p->id_penerima, 5, '0', STR_PAD_LEFT);

                            // Wilayah RT dan RW
                            $rt = $p->wilayah;
                            $rw = $rt && $rt->id_induk
                                ? $wilayahMap->get($rt->id_induk)
                                : null;

                            // Daftar kategori bantuan
                            $kategoriPenerima = $p->riwayat
                                ->map(function ($riwayat) {
                                    return optional(
                                        optional($riwayat->jenis)->kategori
                                    )->nama;
                                })
                                ->filter()
                                ->unique()
                                ->values();

                            // Total bantuan
                            $totalDiterima = (float) (
                                $p->total_diterima ?? 0
                            );

                            // Pemeriksaan riwayat administrasi
                            $bolehHapus = !$p->riwayat()->exists()
                                && !$p->dokumen()->exists()
                                && !$p->pengajuanPerbaikan()->exists();
                        @endphp

                        <tr>
                            <td>
                                {{ $penerima->firstItem() + $index }}
                            </td>

                            <td>
                                <span class="kode-penerima">
                                    {{ $kodePenerima }}
                                </span>
                            </td>

                            <td>
                                <div class="nama-penerima">
                                    <strong>{{ $p->nama }}</strong>
                                    <small>
                                        {{ $p->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                    </small>
                                </div>
                            </td>

                            {{-- NIK LENGKAP --}}
                            <td>
                                <span class="nik-value">
                                    {{ $nikTampil }}
                                </span>
                            </td>

                            <td>
                                <div class="wilayah-info">
                                    <strong>
                                        {{ $rt ? $rt->nama : '-' }}
                                    </strong>

                                    <small>
                                        {{ $rw ? $rw->nama : '-' }}
                                    </small>
                                </div>
                            </td>

                            <td>
                                @if($kategoriPenerima->isNotEmpty())
                                    <div class="kategori-list">
                                        @foreach($kategoriPenerima as $namaKategori)
                                            <span class="kategori-tag">
                                                {{ $namaKategori }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted-custom">
                                        Belum ada bantuan
                                    </span>
                                @endif
                            </td>

                            <td>
                                <span class="status-badge status-{{ $statusClass[$statusTampil] ?? 'secondary' }}">
                                    {{ $statusLabel[$statusTampil] ?? ucfirst($statusTampil) }}
                                </span>
                            </td>

                            <td>
                                <strong class="total-bantuan">
                                    Rp {{ number_format($totalDiterima, 0, ',', '.') }}
                                </strong>
                            </td>

                            <td>
                                <div class="aksi-penerima">

                                    <a
                                        href="{{ route('admin.penerima.show', $p->id_penerima) }}"
                                        class="aksi-btn detail-btn"
                                        title="Detail"
                                    >
                                        Detail
                                    </a>

                                    <a
                                        href="{{ route('admin.penerima.edit', $p->id_penerima) }}"
                                        class="aksi-btn edit-btn"
                                        title="Edit"
                                    >
                                        Edit
                                    </a>
                                    
                                    @if($p->status !== 'nonaktif')
                                        <form
                                            action="{{ route('admin.penerima.nonaktif', $p->id_penerima) }}"
                                            method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan penerima ini?')"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="aksi-btn nonaktif-btn"
                                            >
                                                Nonaktifkan
                                            </button>
                                        </form>
                                    @endif

                                    @if($bolehHapus)
                                        <form
                                            action="{{ route('admin.penerima.destroy', $p->id_penerima) }}"
                                            method="POST"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus data penerima ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="aksi-btn delete-btn"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="9" class="empty-table">
                                <div class="empty-content">
                                    <span>📂</span>
                                    <strong>Data penerima belum tersedia</strong>
                                    <small>
                                        Belum ada data yang sesuai dengan pencarian atau filter.
                                    </small>
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- FOOTER TABEL --}}
        <div class="penerima-table-footer">

            <div class="table-information">
                Menampilkan
                <strong>{{ $penerima->firstItem() ?? 0 }}</strong>
                sampai
                <strong>{{ $penerima->lastItem() ?? 0 }}</strong>
                dari
                <strong>{{ $penerima->total() }}</strong>
                data penerima
            </div>

            <div class="penerima-pagination">
                {{ $penerima->onEachSide(1)->links() }}
            </div>

        </div>

    </div>
</div>

@endsection

@push('styles')
<style>
    .penerima-page {
        display: flex;
        flex-direction: column;
        gap: 22px;
        min-width: 0;
    }

    .penerima-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }

    .penerima-eyebrow {
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.5px;
    }

    .penerima-header h2 {
        margin: 7px 0;
        color: #172554;
        font-size: 25px;
        font-weight: 750;
    }

    .penerima-header p {
        margin: 0;
        color: #64748b;
        font-size: 13px;
    }

    .btn-primary-custom {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 17px;
        border-radius: 9px;
        background: #176b50;
        color: #fff;
        text-decoration: none;
        font-size: 13px;
        font-weight: 650;
        transition: .2s;
    }

    .btn-primary-custom:hover {
        background: #0d4937;
        color: #fff;
    }

    .penerima-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .penerima-stat-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        background: #fff;
        border: 1px solid #e5eaf2;
        border-radius: 12px;
        box-shadow: 0 3px 12px rgba(15, 23, 42, .035);
    }

    .stat-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        border-radius: 12px;
        font-size: 22px;
    }

    .blue-icon {
        background: #eff6ff;
    }

    .orange-icon {
        background: #fff7ed;
    }

    .penerima-stat-card span {
        color: #64748b;
        font-size: 12px;
    }

    .penerima-stat-card h3 {
        margin: 4px 0;
        color: #172554;
        font-size: 25px;
        font-weight: 750;
    }

    .penerima-stat-card small {
        color: #94a3b8;
        font-size: 11px;
    }

    .penerima-panel {
        min-width: 0;
        background: #fff;
        border: 1px solid #e5eaf2;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 12px rgba(15, 23, 42, .035);
    }

    .penerima-panel-heading {
        padding: 20px 22px;
        border-bottom: 1px solid #edf0f5;
    }

    .penerima-panel-heading h3 {
        margin: 0 0 5px;
        color: #172554;
        font-size: 16px;
        font-weight: 700;
    }

    .penerima-panel-heading p {
        margin: 0;
        color: #94a3b8;
        font-size: 12px;
    }

    .penerima-filter {
    display: grid;
    grid-template-columns: minmax(200px, 1.5fr) repeat(3, minmax(130px, 1fr)) auto;
    gap: 12px;
    align-items: end;
    padding: 18px 22px;
    background: #f8fafc;
    border-bottom: 1px solid #edf0f5;
}

    .penerima-filter > div:not(.filter-actions) {
        min-width: 0;
    }

    .penerima-filter label {
        display: block;
        margin-bottom: 7px;
        color: #475569;
        font-size: 11px;
        font-weight: 650;
    }

    .penerima-filter input,
    .penerima-filter select {
        width: 100%;
        min-width: 0;
        height: 39px;
        box-sizing: border-box;
        padding: 0 11px;
        border: 1px solid #dbe2ea;
        border-radius: 7px;
        outline: none;
        background: #fff;
        color: #334155;
        font-size: 12px;
    }

    .penerima-filter input:focus,
    .penerima-filter select:focus {
        border-color: #15803d;
        box-shadow: 0 0 0 3px rgba(21, 128, 61, .1);
    }

    .filter-actions {
        display: flex;
        align-items: center;
        align-self: end;
        gap: 7px;
        height: 72px;
        margin: 0;
        padding: 0;
    }

    .btn-filter,
    .btn-reset {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        height: 39px;
        padding: 0 13px;
        border: 0;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 650;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-filter {
        background: #176b50;
        color: #fff;
    }

    .btn-filter:hover {
        background: #0d4937;
    }

    .btn-reset {
        background: #e2e8f0;
        color: #475569;
    }

    .penerima-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .penerima-table {
        width: 100%;
        min-width: 1250px;
        border-collapse: collapse;
    }

    .penerima-table th {
        padding: 14px 13px;
        background: #f8fafc;
        color: #64748b;
        text-align: left;
        font-size: 10px;
        font-weight: 750;
        letter-spacing: .4px;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid #e8edf3;
    }

    .penerima-table td {
        padding: 14px 13px;
        color: #475569;
        font-size: 12px;
        vertical-align: middle;
        border-bottom: 1px solid #edf0f5;
    }

    .penerima-table tbody tr:hover {
        background: #f8fbff;
    }

    .kode-penerima {
        color: #1e293b;
        font-family: monospace;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .nama-penerima {
        display: flex;
        flex-direction: column;
        gap: 4px;
        min-width: 120px;
    }

    .nama-penerima strong {
        color: #1e293b;
        font-size: 12px;
    }

    .nama-penerima small,
    .wilayah-info small {
        color: #94a3b8;
        font-size: 10px;
    }

    .nik-value {
        display: inline-block;
        color: #263449;
        font-family: monospace;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .5px;
        white-space: nowrap;
    }

    .wilayah-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .wilayah-info strong {
        color: #334155;
        font-size: 11px;
    }

    .kategori-list {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }

    .kategori-tag {
        display: inline-block;
        padding: 4px 7px;
        border-radius: 5px;
        background: #eff6ff;
        color: #15803d;
        font-size: 10px;
        white-space: nowrap;
    }

    .text-muted-custom {
        color: #94a3b8;
        font-size: 10px;
    }

    .status-badge {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .status-success {
        background: #dcfce7;
        color: #15803d;
    }

    .status-warning {
        background: #fef3c7;
        color: #b45309;
    }

    .status-danger {
        background: #fee2e2;
        color: #b91c1c;
    }

    .status-secondary {
        background: #e2e8f0;
        color: #475569;
    }

    .total-bantuan {
        color: #15803d;
        font-size: 11px;
        white-space: nowrap;
    }

    .aksi-penerima {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        min-width: 160px;
    }

    .aksi-penerima form {
        margin: 0;
    }

    .aksi-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 8px;
        border: 0;
        border-radius: 5px;
        font-size: 10px;
        font-weight: 650;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .detail-btn {
        background: #eff6ff;
        color: #15803d;
    }

    .edit-btn {
        background: #fef3c7;
        color: #a16207;
    }

    .nonaktif-btn {
        background: #f1f5f9;
        color: #475569;
    }

    .delete-btn {
        background: #fee2e2;
        color: #dc2626;
    }

    .empty-table {
        padding: 45px 20px !important;
        text-align: center;
    }

    .empty-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .empty-content span {
        font-size: 30px;
    }

    .empty-content strong {
        color: #334155;
        font-size: 13px;
    }

    .empty-content small {
        color: #94a3b8;
        font-size: 11px;
    }

    .penerima-table-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        flex-wrap: wrap;
        padding: 16px 20px;
    }

    .table-information {
        color: #64748b;
        font-size: 11px;
    }

    .table-information strong {
        color: #334155;
    }

    .penerima-pagination nav {
        display: flex;
        justify-content: flex-end;
    }

    .penerima-information {
        padding: 16px 19px;
        border: 1px solid #bfdbfe;
        border-radius: 9px;
        background: #eff6ff;
    }

    .penerima-information strong {
        color: #15803d;
        font-size: 12px;
    }

    .penerima-information p {
        margin: 6px 0 0;
        color: #475569;
        font-size: 11px;
        line-height: 1.7;
    }

    .penerima-alert {
        padding: 12px 15px;
        border-radius: 8px;
        font-size: 12px;
    }

    .success-alert {
        background: #dcfce7;
        color: #166534;
    }

    .danger-alert {
        background: #fee2e2;
        color: #991b1b;
    }

    @media (max-width: 1100px) {
        .penerima-filter {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .filter-search {
            grid-column: span 2;
        }

        .filter-actions {
            grid-column: span 2;
            justify-self: start;
        }
    }

    @media (max-width: 600px) {
        .penerima-stats {
            grid-template-columns: 1fr;
        }

        .penerima-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .penerima-filter {
            grid-template-columns: 1fr;
        }

        .filter-search,
        .filter-actions {
            grid-column: auto;
        }

        .filter-actions {
            width: 100%;
        }

        .filter-actions > * {
            flex: 1;
        }

        .penerima-table-footer {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endpush
