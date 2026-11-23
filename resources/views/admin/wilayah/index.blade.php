@extends('layouts.admin')

@section('title', 'Manajemen Wilayah')
@section('heading', 'Manajemen Wilayah')

@section('content')

@php
    $wilayahInduk = auth()->user()->wilayah;
@endphp

<style>
    .wilayah-page {
        display: grid;
        gap: 22px;
        color: #20391a;
    }

    .wilayah-intro {
        color: #647260;
        font-size: 14px;
        margin-top: -12px;
    }

    .wilayah-card {
        background: #fff;
        border: 1px solid #e5ebdf;
        border-radius: 14px;
        padding: 22px;
        box-shadow: 0 3px 12px rgba(32, 57, 26, .04);
    }

    .wilayah-card h3 {
        margin: 0 0 6px;
        font-size: 17px;
    }

    .wilayah-card > p {
        color: #71806c;
        font-size: 13px;
        margin: 0 0 18px;
    }

    /* Form tambah RW dan RT */
    .wilayah-form {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: end;
        gap: 12px;
    }

    .wilayah-form .wilayah-field {
        min-width: 0;
    }

    .wilayah-form .wilayah-btn {
        height: 42px;
        box-sizing: border-box;
        margin-bottom: 14px;
    }

    .wilayah-field {
        min-width: 0;
    }

    .wilayah-field label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 7px;
    }

    .wilayah-field input {
        width: 100%;
        box-sizing: border-box;
        height: 42px;
        border: 1px solid #dce5d7;
        border-radius: 8px;
        padding: 9px 12px;
        font: inherit;
        outline: none;
    }

    .wilayah-field input:focus,
    .wilayah-edit-input:focus {
        border-color: #548c3d;
        box-shadow: 0 0 0 3px rgba(84, 140, 61, .10);
    }

    .wilayah-btn {
        border: 0;
        border-radius: 8px;
        padding: 0 15px;
        height: 42px;
        font-weight: 600;
        cursor: pointer;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .wilayah-btn-primary {
        background: #174f37;
        color: #fff;
        transition: background .2s ease;
    }

    .wilayah-btn-primary:hover {
        background: #103d2a;
    }

    .wilayah-btn-edit {
        background: #edf4e9;
        color: #477c35;
    }

    .wilayah-btn-delete {
        background: #fff0ef;
        color: #c0392b;
    }

    /* Daftar wilayah */
    .wilayah-list {
        display: grid;
        gap: 16px;
    }

    .wilayah-item {
        border: 1px solid #e5ebdf;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }

    .wilayah-rw-header {
        padding: 15px 18px;
        background: #f5f8f2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }

    .wilayah-rw-title {
        font-weight: 700;
        font-size: 15px;
    }

    .wilayah-count {
        font-size: 12px;
        color: #71806c;
        font-weight: 400;
        margin-left: 5px;
    }

    .wilayah-rw-content {
        padding: 18px;
        display: grid;
        gap: 15px;
    }

    .wilayah-section-label {
        font-size: 13px;
        font-weight: 600;
        color: #53644c;
        margin-bottom: 9px;
    }

    .wilayah-rt-list {
        display: grid;
        gap: 9px;
    }

    .wilayah-rt {
        border: 1px solid #edf0e9;
        border-radius: 9px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .wilayah-rt-name {
        font-weight: 600;
        font-size: 14px;
    }

    /* Form edit dan tombol aksi */
    .wilayah-actions {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
    }

    .wilayah-edit-form {
        display: flex;
        align-items: center;
        gap: 7px;
        margin: 0;
    }

    .wilayah-actions form {
        margin: 0;
        display: flex;
        align-items: center;
    }

    .wilayah-edit-input {
        width: 115px;
        height: 40px;
        box-sizing: border-box;
        border: 1px solid #dce5d7;
        border-radius: 7px;
        padding: 7px 10px;
        font: inherit;
        outline: none;
    }

    .wilayah-edit-form .wilayah-btn,
    .wilayah-actions > form .wilayah-btn {
        height: 40px;
        box-sizing: border-box;
    }

    .wilayah-empty {
        text-align: center;
        padding: 25px 15px;
        color: #788473;
        background: #f8faf6;
        border-radius: 10px;
        font-size: 13px;
    }

    .wilayah-error {
        color: #c0392b;
        font-size: 12px;
        margin-top: 5px;
        display: block;
    }

    .wilayah-alert {
        padding: 13px 16px;
        border-radius: 9px;
        font-size: 14px;
    }

    .wilayah-alert-success {
        background: #edf7e9;
        color: #356c2a;
    }

    .wilayah-alert-error {
        background: #fff0ef;
        color: #b83227;
    }

    @media (max-width: 600px) {
        .wilayah-card {
            padding: 16px;
        }

        .wilayah-form {
            grid-template-columns: 1fr;
        }

        .wilayah-form .wilayah-btn {
            width: 100%;
        }

        .wilayah-rw-header {
            align-items: flex-start;
        }

        .wilayah-actions {
            width: 100%;
        }

        .wilayah-edit-form {
            flex: 1;
        }

        .wilayah-edit-input {
            flex: 1;
            min-width: 0;
        }

        .wilayah-form .wilayah-btn {
            margin-bottom: 0;
        }
    }

    .wilayah-form .wilayah-btn {
        margin-bottom: 14px;
    }

    .wilayah-rw-content .wilayah-form .wilayah-field input {
        transform: translateY(2px);
    }

    .wilayah-rw-content > div:first-child .wilayah-form .wilayah-field input {
        position: relative !important;
        top: 2px !important;
    }
</style>

<div class="wilayah-page">

    <p class="wilayah-intro">
        Kelola data RW dan RT di wilayah Desa/Kelurahan
        {{ $wilayahInduk->nama ?? '-' }}.
    </p>

    {{-- Tambah RW --}}
    <div class="wilayah-card">
        <h3>Tambah RW</h3>
        <p>Tambahkan RW baru untuk wilayah {{ $wilayahInduk->nama ?? '-' }}.</p>

        <form action="{{ route('admin.wilayah.store') }}"
              method="POST"
              class="wilayah-form">
            @csrf

            <input type="hidden" name="tipe" value="rw">
            <input type="hidden"
                   name="id_induk"
                   value="{{ auth()->user()->id_wilayah }}">

            <div class="wilayah-field">
                <label for="nama_rw">Nomor RW</label>
                <input type="text"
                       id="nama_rw"
                       name="nama"
                       placeholder="Contoh: 01"
                       value="{{ old('tipe') === 'rw' ? old('nama') : '' }}"
                       required
                       maxlength="10"
                       pattern="[0-9]+">
            </div>

            <button type="submit" class="wilayah-btn wilayah-btn-primary">
                + Tambah RW
            </button>
        </form>
    </div>

    {{-- Daftar RW dan RT --}}
    <div class="wilayah-card">
        <h3>Daftar RW dan RT</h3>
        <p>Kelola RW dan RT yang berada dalam kewenangan Anda.</p>

        @forelse($rwList as $rw)
            <div class="wilayah-list" style="margin-bottom: 15px;">
                <div class="wilayah-item">

                    {{-- Header RW --}}
                    <div class="wilayah-rw-header">

                        <div class="wilayah-rw-title">
                            RW {{ $rw->nama }}
                            <span class="wilayah-count">
                                {{ $rw->anak->count() }} RT
                            </span>
                        </div>

                        <div class="wilayah-actions">

                            <form action="{{ route('admin.wilayah.update', $rw->id_wilayah) }}"
                                  method="POST"
                                  class="wilayah-edit-form">
                                @csrf
                                @method('PUT')

                                <input type="text"
                                       name="nama"
                                       value="{{ $rw->nama }}"
                                       required
                                       maxlength="10"
                                       pattern="[0-9]+"
                                       aria-label="Nama RW"
                                       class="wilayah-edit-input">

                                <button type="submit"
                                        class="wilayah-btn wilayah-btn-edit">
                                    Simpan
                                </button>
                            </form>

                            <form action="{{ route('admin.wilayah.destroy', $rw->id_wilayah) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus {{ $rw->nama }}?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="wilayah-btn wilayah-btn-delete">
                                    Hapus
                                </button>
                            </form>

                        </div>
                    </div>

                    {{-- Isi RW --}}
                    <div class="wilayah-rw-content">

                        {{-- Tambah RT --}}
                        <div>
                            <div class="wilayah-section-label">
                                Tambahkan RT ke RW {{ $rw->nama }}
                            </div>

                            <form action="{{ route('admin.wilayah.store') }}"
                                  method="POST"
                                  class="wilayah-form">
                                @csrf

                                <input type="hidden" name="tipe" value="rt">
                                <input type="hidden"
                                       name="id_induk"
                                       value="{{ $rw->id_wilayah }}">

                                <div class="wilayah-field">
                                    <label for="nama_rt_{{ $rw->id_wilayah }}">
                                        Nomor RT
                                    </label>

                                    <input type="text"
                                           id="nama_rt_{{ $rw->id_wilayah }}"
                                           name="nama"
                                           placeholder="Contoh: 01"
                                           maxlength="10"
                                           pattern="[0-9]+"
                                           required>
                                </div>

                                <button type="submit"
                                        class="wilayah-btn wilayah-btn-primary">
                                    + Tambah RT
                                </button>
                            </form>
                        </div>

                        {{-- Daftar RT --}}
                        <div>
                            <div class="wilayah-section-label">
                                Daftar RT
                            </div>

                            @forelse($rw->anak as $rt)
                                <div class="wilayah-rt-list">
                                    <div class="wilayah-rt">

                                        <div class="wilayah-rt-name">
                                            RT {{ $rt->nama }}
                                        </div>

                                        <div class="wilayah-actions">

                                            <form action="{{ route('admin.wilayah.update', $rt->id_wilayah) }}"
                                                  method="POST"
                                                  class="wilayah-edit-form">
                                                @csrf
                                                @method('PUT')

                                                <input type="text"
                                                       name="nama"
                                                       value="{{ $rt->nama }}"
                                                       required
                                                       maxlength="100"
                                                       aria-label="Nama RT"
                                                       class="wilayah-edit-input">

                                                <button type="submit"
                                                        class="wilayah-btn wilayah-btn-edit">
                                                    Simpan
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.wilayah.destroy', $rt->id_wilayah) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Yakin ingin menghapus {{ $rt->nama }}?');">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="wilayah-btn wilayah-btn-delete">
                                                    Hapus
                                                </button>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="wilayah-empty">
                                    Belum ada RT yang terdaftar di {{ $rw->nama }}.
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="wilayah-empty">
                <strong>Belum ada data RW.</strong>
                <p style="margin: 7px 0 0;">
                    Silakan tambahkan RW terlebih dahulu.
                </p>
            </div>
        @endforelse
    </div>

</div>

@endsection
