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

        $statusBadge = [
            'diajukan' => 'badge-amber',
            'terverifikasi' => 'badge-green',
            'ditolak' => 'badge-red',
            'nonaktif' => 'badge-gray',
        ];
    @endphp

    {{-- HEADER --}}
    <div class="page-head">
        <div class="page-head-text">
            <h1>Data Penerima</h1>

            <p>
                {{ number_format($totalPenerima, 0, ',', '.') }} warga terdata &middot;
                {{ number_format($menungguVerifikasi, 0, ',', '.') }} menunggu verifikasi
            </p>
        </div>

        <div class="page-head-actions">
            <a href="{{ route('admin.penerima.index') }}" class="btn btn-outline">
                <x-icon name="download" :size="15" />
                Ekspor
            </a>

            <a href="{{ route('admin.penerima.create') }}" class="btn btn-primary">
                <x-icon name="plus" :size="15" />
                Tambah Penerima
            </a>
        </div>
    </div>

    {{-- FILTER --}}
    <form action="{{ route('admin.penerima.index') }}" method="GET" class="filter-bar">

        <div class="field grow">
            <label for="q">Pencarian</label>

            <input class="input" type="text" id="q" name="q" value="{{ request('q') }}"
                   placeholder="NIK, nama, atau kode penerima...">
        </div>

        <div class="field">
            <label for="kategori">Kategori</label>

            <select class="select" name="kategori" id="kategori">
                <option value="">Semua Kategori</option>

                @foreach ($kategori as $item)
                    <option value="{{ $item->id_kategori }}"
                        @selected(request('kategori') == $item->id_kategori)>
                        {{ $item->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="status">Status</label>

            <select class="select" name="status" id="status">
                <option value="">Semua Status</option>

                @foreach ($statusLabel as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') == $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="wilayah">RT / RW</label>

            <select class="select" name="wilayah" id="wilayah">
                <option value="">Semua RT / RW</option>

                @foreach ($wilayah as $item)
                    <option value="{{ $item->id_wilayah }}"
                        @selected(request('wilayah') == $item->id_wilayah)>
                        {{ $item->nama }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">
                <x-icon name="filter" :size="14" />
                Terapkan
            </button>

            <a href="{{ route('admin.penerima.index') }}" class="btn btn-outline">
                Reset
            </a>
        </div>

    </form>

    {{-- TABEL --}}
    <div class="card">

        <div class="card-head">
            <div>
                <h3>Daftar penerima</h3>
                <p>Data penerima bantuan sosial pada wilayah tugas Anda.</p>
            </div>

            <div class="card-head-actions">
                <span class="badge badge-outline">
                    {{ number_format($penerima->total(), 0, ',', '.') }} data
                </span>
            </div>
        </div>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Penerima</th>
                        <th>NIK</th>
                        <th>RT / RW</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th class="num">Total Diterima</th>
                        <th class="num">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($penerima as $p)
                        @php
                            $nikTampil = $p->nik_masked;

                            $statusTampil = $p->status === 'nonaktif'
                                ? 'nonaktif'
                                : ($p->pengajuanTerbaru?->status ?? $p->status);

                            $kodePenerima = 'PNR-' . ($p->created_at?->format('Y') ?? date('Y'))
                                . '-' . str_pad($p->id_penerima, 5, '0', STR_PAD_LEFT);

                            $rt = $p->wilayah;
                            $rw = $rt && $rt->id_induk
                                ? $wilayahMap->get($rt->id_induk)
                                : null;

                            $kategoriPenerima = $p->riwayat
                                ->map(fn ($r) => optional(optional($r->jenis)->kategori)->nama)
                                ->filter()
                                ->unique()
                                ->values();

                            $totalDiterima = (float) ($p->total_diterima ?? 0);

                            $bolehHapus = !$p->riwayat()->exists()
                                && !$p->dokumen()->exists()
                                && !$p->pengajuanPerbaikan()->exists();
                        @endphp

                        <tr>
                            <td class="mono">{{ $kodePenerima }}</td>

                            <td>
                                <div class="tbl-person">
                                    <b>{{ $p->nama }}</b>
                                    <small>{{ $p->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</small>
                                </div>
                            </td>

                            <td class="mono">{{ $nikTampil }}</td>

                            <td>{{ $rt->label ?? '-' }} / {{ $rw->label ?? '-' }}</td>

                            <td>
                                @if ($kategoriPenerima->isNotEmpty())
                                    <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                        @foreach ($kategoriPenerima as $namaKategori)
                                            <span class="badge badge-gray">{{ $namaKategori }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="muted" style="font-size:11.5px;">Belum ada</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge {{ $statusBadge[$statusTampil] ?? 'badge-gray' }}">
                                    {{ $statusLabel[$statusTampil] ?? ucfirst($statusTampil) }}
                                </span>
                            </td>

                            <td class="num strong">
                                @if ($totalDiterima > 0)
                                    Rp {{ number_format($totalDiterima, 0, ',', '.') }}
                                @else
                                    &mdash;
                                @endif
                            </td>

                            <td>
                                <div class="row-actions">

                                    <a href="{{ route('admin.penerima.show', $p->id_penerima) }}"
                                       class="icon-action" title="Lihat detail">
                                        <x-icon name="eye" :size="14" />
                                    </a>

                                    <a href="{{ route('admin.penerima.edit', $p->id_penerima) }}"
                                       class="icon-action" title="Edit data">
                                        <x-icon name="edit" :size="14" />
                                    </a>

                                    @if ($p->status !== 'nonaktif')
                                        <form action="{{ route('admin.penerima.nonaktif', $p->id_penerima) }}"
                                              method="POST"
                                              onsubmit="return confirm('Nonaktifkan penerima ini? Riwayat tetap tersimpan.')">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="icon-action"
                                                    title="Nonaktifkan penerima">
                                                <x-icon name="x" :size="14" />
                                            </button>
                                        </form>
                                    @endif

                                    @if ($bolehHapus)
                                        <form action="{{ route('admin.penerima.destroy', $p->id_penerima) }}"
                                              method="POST"
                                              onsubmit="return confirm('Hapus data penerima ini secara permanen?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="icon-action danger"
                                                    title="Hapus data">
                                                <x-icon name="trash" :size="14" />
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="icon-action" disabled
                                                title="Tidak dapat dihapus karena memiliki riwayat">
                                            <x-icon name="trash" :size="14" />
                                        </button>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty">
                                    <div class="empty-icon"><x-icon name="users" :size="20" /></div>
                                    <strong>Data penerima belum tersedia</strong>
                                    <p>Belum ada data yang sesuai dengan pencarian atau filter yang dipilih.</p>

                                    <a href="{{ route('admin.penerima.create') }}" class="btn btn-primary">
                                        Tambah Penerima
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($penerima->hasPages())
            <x-pagination :paginator="$penerima" />
        @endif

        <div class="card-foot">
            <x-icon name="info" :size="13" />
            Ikon hapus tidak aktif untuk penerima yang sudah memiliki riwayat penyaluran.
            Untuk mengoreksi data, gunakan Edit &mdash; riwayat tetap tersimpan sebagai jejak audit.
        </div>

    </div>

@endsection
