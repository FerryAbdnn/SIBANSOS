@extends('layouts.admin')

@section('title', 'Form Program Bantuan')
@section('heading', 'Jenis Bantuan / ' . ($item->exists ? 'Edit' : 'Tambah'))

@section('content')

    <div class="page-head">
        <div class="page-head-text">
            <h1>{{ $item->exists ? 'Edit Program Bantuan' : 'Tambah Program Bantuan' }}</h1>

            <p>
                {{ $item->exists
                    ? 'Perbarui informasi program bantuan yang sudah terdaftar.'
                    : 'Daftarkan program baru di bawah kategori bantuan.' }}
            </p>
        </div>
    </div>

    <div class="card" style="max-width:760px;">

        <div class="card-head">
            <div>
                <h3>Informasi program</h3>
                <p>Program akan tampil pada formulir pengajuan warga.</p>
            </div>
        </div>

        <form method="POST"
              action="{{ $item->exists ? route('admin.jenis.update', $item) : route('admin.jenis.store') }}">
            @csrf
            @if ($item->exists)
                @method('PUT')
            @endif

            <div class="card-body">

                <div class="form-grid">

                    <div class="field">
                        <label for="id_kategori">Kategori induk <span class="req">*</span></label>

                        <select class="select" id="id_kategori" name="id_kategori" required>
                            <option value="">Pilih kategori</option>

                            @foreach ($kategori as $k)
                                <option value="{{ $k->id_kategori }}"
                                    @selected(old('id_kategori', $item->id_kategori) == $k->id_kategori)>
                                    {{ $k->nama }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_kategori')
                            <small class="err">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="nama_program">Nama program <span class="req">*</span></label>

                        <input class="input" type="text" id="nama_program" name="nama_program"
                               maxlength="100" value="{{ old('nama_program', $item->nama_program) }}"
                               placeholder="Contoh: PKH Tahap IV" required>

                        @error('nama_program')
                            <small class="err">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="field span-2">
                        <label for="penyelenggara">Penyelenggara</label>

                        <input class="input" type="text" id="penyelenggara" name="penyelenggara"
                               maxlength="100" value="{{ old('penyelenggara', $item->penyelenggara) }}"
                               placeholder="Contoh: Pemerintah Desa">

                        @error('penyelenggara')
                            <small class="err">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="field span-2">
                        <label for="deskripsi">Deskripsi</label>

                        <textarea class="textarea" id="deskripsi" name="deskripsi" rows="3"
                                  placeholder="Penjelasan singkat mengenai program bantuan">{{ old('deskripsi', $item->deskripsi) }}</textarea>

                        @error('deskripsi')
                            <small class="err">{{ $message }}</small>
                        @enderror
                    </div>

                </div>

            </div>

            <div class="card-foot" style="justify-content:flex-end;">
                <a href="{{ route('admin.jenis.index') }}" class="btn btn-outline">Batal</a>

                <button type="submit" class="btn btn-primary">
                    <x-icon name="check" :size="14" />
                    Simpan Program
                </button>
            </div>

        </form>

    </div>

@endsection
