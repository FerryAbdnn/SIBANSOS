@extends('layouts.admin')

@section('title', 'Kategori Bantuan')
@section('heading', 'Kategori Bantuan')

@section('content')

    <div class="page-head">
        <div class="page-head-text">
            <h1>Kategori Bantuan</h1>

            <p>
                {{ number_format($items->total(), 0, ',', '.') }} kategori terdaftar &middot;
                kelompok program bantuan sosial
            </p>
        </div>

        <div class="page-head-actions">
            <a href="{{ route('admin.kategori.create') }}" class="btn btn-primary">
                <x-icon name="plus" :size="15" />
                Tambah Kategori
            </a>
        </div>
    </div>

    <div class="card">

        <div class="card-head">
            <div>
                <h3>Daftar kategori</h3>
                <p>Kategori yang masih memiliki program tidak dapat dihapus.</p>
            </div>

            <div class="card-head-actions">
                <span class="badge badge-outline">
                    {{ number_format($items->total(), 0, ',', '.') }} kategori
                </span>
            </div>
        </div>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Nama Kategori</th>
                        <th>Jumlah Program</th>
                        <th>Deskripsi</th>
                        <th class="num">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($items as $i)
                        <tr>
                            <td class="strong">{{ $i->nama }}</td>

                            <td>
                                <span class="badge badge-gray">{{ $i->jenis_count }} program</span>
                            </td>

                            <td class="muted">{{ $i->deskripsi ?: '—' }}</td>

                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.kategori.edit', $i) }}"
                                       class="icon-action" title="Edit kategori">
                                        <x-icon name="edit" :size="14" />
                                    </a>

                                    @if ($i->jenis_count > 0)
                                        <button type="button" class="icon-action" disabled
                                                title="Kategori masih memiliki program bantuan">
                                            <x-icon name="trash" :size="14" />
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('admin.kategori.destroy', $i) }}"
                                              onsubmit="return confirm('Hapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="icon-action danger"
                                                    title="Hapus kategori">
                                                <x-icon name="trash" :size="14" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty">
                                    <div class="empty-icon"><x-icon name="layers" :size="20" /></div>
                                    <strong>Belum ada kategori</strong>
                                    <p>Tambahkan kategori bantuan untuk mulai mengelompokkan program.</p>

                                    <a href="{{ route('admin.kategori.create') }}" class="btn btn-primary">
                                        Tambah Kategori
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

        <div class="card-foot">
            <x-icon name="info" :size="13" />
            Kategori yang sudah dipakai pada riwayat penyaluran tidak dapat dihapus agar
            jejak audit tetap utuh.
        </div>

    </div>

@endsection
