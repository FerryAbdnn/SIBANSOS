
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

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.penerima.export') }}"
                class="btn-primary-custom">
                <span>⇩</span> Export Data Penerima
            </a>

            <a href="{{ route('admin.penerima.create') }}"
            class="btn-primary-custom">
                <span>＋</span> Tambah Penerima
            </a>
        </div>
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
    <link rel="stylesheet" href="{{ asset('css/penerima-admin.css') }}">
@endpush
