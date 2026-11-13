@extends('layouts.admin')
@section('title', 'Form Kategori')
@section('heading', $item->exists ? 'Edit Kategori' : 'Tambah Kategori') @section('content')
    <div class="panel form-panel">
        <form method="POST"
            action="{{ $item->exists ? route('admin.kategori.update', $item) : route('admin.kategori.store') }}">
            @csrf @if($item->exists)@method('PUT')@endif<label>Nama kategori</label><input name="nama"
                value="{{ old('nama', $item->nama) }}" required><label>Deskripsi</label><textarea
                name="deskripsi">{{ old('deskripsi', $item->deskripsi) }}</textarea>
            <div class="form-actions"><a class="btn outline" href="{{ route('admin.kategori.index') }}">Batal</a><button
                    class="btn primary">Simpan</button></div>
        </form>
</div>@endsection