@extends('layouts.admin')
@section('title', 'Jenis Bantuan')
@section('heading', 'Jenis / Program Bantuan')
@section('content')
    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Daftar program bantuan</h3>
                <p>Program yang berada di bawah kategori bantuan.</p>
            </div><a class="btn primary" href="{{ route('admin.jenis.create') }}">＋ Tambah program</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Program</th>
                        <th>Kategori</th>
                        <th>Penyelenggara</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>@forelse($items as $i)
                    <tr>
                        <td><b>{{ $i->nama_program }}</b></td>
                        <td>{{ $i->kategori->nama }}</td>
                        <td>{{ $i->penyelenggara }}</td>
                        <td><span
                                class="status {{ $i->aktif ? 'terverifikasi' : 'nonaktif' }}">{{ $i->aktif ? 'Aktif' : 'Nonaktif' }}</span>
                        </td>
                        <td class="actions"><a href="{{ route('admin.jenis.edit', $i) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.jenis.destroy', $i) }}"
                                onsubmit="return confirm('Hapus/nonaktifkan program?')">@csrf @method('DELETE')<button
                                    class="link-button">Hapus</button></form>
                        </td>
                </tr>@empty<tr>
                        <td colspan="5" class="empty">Belum ada program bantuan.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </div><x-pagination :paginator="$items" />
</div>@endsection