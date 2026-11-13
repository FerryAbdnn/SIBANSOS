@extends('layouts.admin')
@section('title', 'Form Program Bantuan')
@section('heading', $item->exists ? 'Edit Program Bantuan' : 'Tambah Program Bantuan') @section('content')
    <div class="panel form-panel">
        <form method="POST" action="{{ $item->exists ? route('admin.jenis.update', $item) : route('admin.jenis.store') }}">
            @csrf
            @if($item->exists)@method('PUT')@endif<label>Kategori</label><select name="id_kategori"
                required>@foreach($kategori as $k)
                    <option value="{{ $k->id_kategori }}" @selected(old('id_kategori', $item->id_kategori) == $k->id_kategori)>
                        {{ $k->nama }}
                </option>@endforeach
            </select><label>Nama program</label><input name="nama_program"
                value="{{ old('nama_program', $item->nama_program) }}" required><label>Penyelenggara</label><input
                name="penyelenggara"
                value="{{ old('penyelenggara', $item->penyelenggara) }}"><label>Deskripsi</label><textarea
                name="deskripsi">{{ old('deskripsi', $item->deskripsi) }}</textarea>
            <div class="form-actions"><a class="btn outline" href="{{ route('admin.jenis.index') }}">Batal</a><button
                    class="btn primary">Simpan</button></div>
        </form>
</div>@endsection