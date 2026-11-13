@extends('layouts.admin')
@section('title', 'Kategori Bantuan')
@section('heading', 'Kategori Bantuan')
@section('content')
    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Daftar kategori</h3>
                <p>Kelola kelompok program bantuan sosial.</p>
            </div><a class="btn primary" href="{{ route('admin.kategori.create') }}">＋ Tambah kategori</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama kategori</th>
                        <th>Jumlah program</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>@forelse($items as $i)
                    <tr>
                        <td><b>{{ $i->nama }}</b></td>
                        <td>{{ $i->jenis_count }}</td>
                        <td>{{ $i->deskripsi }}</td>
                        <td class="actions"><a href="{{ route('admin.kategori.edit', $i) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.kategori.destroy', $i) }}"
                                onsubmit="return confirm('Hapus kategori?')">@csrf @method('DELETE')<button
                                    class="link-button">Hapus</button></form>
                        </td>
                </tr>@empty<tr>
                        <td colspan="4" class="empty">Belum ada kategori.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </div><x-pagination :paginator="$items" />
</div>@endsection