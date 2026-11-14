@extends('layouts.admin')

@section('title', $item->exists ? 'Edit Kategori' : 'Tambah Kategori')
@section('heading', 'Kategori Bantuan / ' . ($item->exists ? 'Edit' : 'Tambah'))

@section('content')

    <div class="page-head">
        <div class="page-head-text">
            <h1>{{ $item->exists ? 'Edit Kategori' : 'Tambah Kategori' }}</h1>

            <p>
                {{ $item->exists
                    ? 'Perbarui nama atau deskripsi kategori bantuan.'
                    : 'Buat kelompok baru untuk program bantuan sosial.' }}
            </p>
        </div>
    </div>

    <div class="card" style="max-width:680px;">

        <div class="card-head">
            <div>
                <h3>Informasi kategori</h3>
                <p>Nama kategori harus unik dan mudah dikenali warga.</p>
            </div>
        </div>

        <form method="POST"
              action="{{ $item->exists ? route('admin.kategori.update', $item) : route('admin.kategori.store') }}">
            @csrf
            @if ($item->exists)
                @method('PUT')
            @endif

            <div class="card-body">

                <div class="field">
                    <label for="nama">Nama kategori <span class="req">*</span></label>

                    <input class="input" type="text" id="nama" name="nama" maxlength="50"
                           value="{{ old('nama', $item->nama) }}" placeholder="Contoh: PKH" required>

                    @error('nama')
                        <small class="err">{{ $message }}</small>
                    @enderror
                </div>

                <div class="field" style="margin-top:16px;">
                    <label for="deskripsi">Deskripsi</label>

                    <textarea class="textarea" id="deskripsi" name="deskripsi" rows="3"
                              placeholder="Penjelasan singkat mengenai kategori ini">{{ old('deskripsi', $item->deskripsi) }}</textarea>

                    <small class="hint">Deskripsi ditampilkan pada halaman publik sebagai penjelasan kategori.</small>

                    @error('deskripsi')
                        <small class="err">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <div class="card-foot" style="justify-content:flex-end;">
                <a href="{{ route('admin.kategori.index') }}" class="btn btn-outline">Batal</a>

                <button type="submit" class="btn btn-primary">
                    <x-icon name="check" :size="14" />
                    Simpan
                </button>
            </div>

        </form>

    </div>

@endsection
