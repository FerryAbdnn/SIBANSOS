@extends('layouts.admin')

@section('title', 'Jenis Bantuan')
@section('heading', 'Jenis Bantuan')

@section('content')

    @php
        $kategoriList = \App\Models\KategoriBantuan::withCount('jenis')->get();

        $totalAktif = \App\Models\JenisBantuan::where('aktif', true)->count();

        $totalNonaktif = \App\Models\JenisBantuan::where('aktif', false)->count();
    @endphp

    <div class="page-head">
        <div class="page-head-text">
            <h1>Jenis Bantuan</h1>

            <p>
                {{ number_format($items->total(), 0, ',', '.') }} program &middot;
                {{ number_format($totalNonaktif, 0, ',', '.') }} nonaktif
            </p>
        </div>

        <div class="page-head-actions">
            <a href="{{ route('admin.jenis.index') }}" class="btn btn-outline">
                <x-icon name="download" :size="15" />
                Ekspor
            </a>

            <a href="{{ route('admin.jenis.create') }}" class="btn btn-primary">
                <x-icon name="plus" :size="15" />
                Tambah Jenis
            </a>
        </div>
    </div>

    <div class="alert alert-info">
        <x-icon name="info" :size="16" />
        <div>
            Jenis bantuan yang sudah dipakai pada riwayat penyaluran tidak dapat dihapus,
            hanya dinonaktifkan agar riwayat tetap utuh.
        </div>
    </div>

    <div class="card">

        <div class="card-head">
            <div>
                <h3>Daftar program bantuan</h3>
                <p>Program yang berada di bawah kategori bantuan.</p>
            </div>

            <div class="card-head-actions">
                <span class="badge badge-green">{{ $totalAktif }} aktif</span>
                <span class="badge badge-gray">{{ $totalNonaktif }} nonaktif</span>
            </div>
        </div>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Nama Program</th>
                        <th>Kategori</th>
                        <th>Penyelenggara</th>
                        <th>Status</th>
                        <th class="num">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($items as $i)
                        <tr>
                            <td class="strong">{{ $i->nama_program }}</td>

                            <td>
                                <span class="badge badge-gray">{{ $i->kategori->nama ?? '-' }}</span>
                            </td>

                            <td class="muted">{{ $i->penyelenggara ?: '—' }}</td>

                            <td>
                                <span class="badge {{ $i->aktif ? 'badge-green' : 'badge-gray' }}">
                                    {{ $i->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.jenis.edit', $i) }}"
                                       class="icon-action" title="Edit program">
                                        <x-icon name="edit" :size="14" />
                                    </a>

                                    <form method="POST" action="{{ route('admin.jenis.destroy', $i) }}"
                                          onsubmit="return confirm('Hapus atau nonaktifkan program ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="icon-action danger"
                                                title="{{ $i->riwayat()->exists() ? 'Nonaktifkan program' : 'Hapus program' }}">
                                            <x-icon name="{{ $i->riwayat()->exists() ? 'x' : 'trash' }}" :size="14" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty">
                                    <div class="empty-icon"><x-icon name="list" :size="20" /></div>
                                    <strong>Belum ada program bantuan</strong>
                                    <p>Tambahkan program di bawah kategori bantuan yang tersedia.</p>

                                    <a href="{{ route('admin.jenis.create') }}" class="btn btn-primary">
                                        Tambah Jenis
                                    </a>
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

    </div>

    {{-- SEBARAN --}}
    <div class="card" style="margin-top:18px;">
        <div class="card-head">
            <div>
                <h3>Sebaran program</h3>
                <p>Jumlah program pada tiap kategori bantuan.</p>
            </div>
        </div>

        <div class="card-body" style="padding-top:6px;padding-bottom:6px;">
            <div class="dot-list">
                @foreach ($kategoriList as $k)
                    <div class="dot-row">
                        <i style="opacity: {{ 1 - min(0.7, $loop->index * 0.15) }};"></i>
                        <span>{{ $k->nama }}</span>
                        <b>{{ $k->jenis_count }} program</b>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card-foot">
            <x-icon name="info" :size="13" />
            Satu penerima dapat terdaftar pada beberapa jenis bantuan selama kategorinya berbeda.
        </div>
    </div>

@endsection
