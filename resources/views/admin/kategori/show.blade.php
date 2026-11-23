@extends('layouts.admin')

@section('title', 'Detail Kategori Bantuan')
@section('heading', 'Detail Kategori Bantuan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kategori-detail-admin.css') }}">
@endpush

@section('content')

    <div class="kategori-detail-page">

        <div class="kategori-detail-breadcrumb">
            <a href="{{ route('admin.kategori.index') }}">
                Kategori Bantuan
            </a>
            <i class="fa-solid fa-chevron-right"></i>
            <span>Detail Kategori</span>
        </div>

        <section class="kategori-detail-hero">
            <div class="kategori-detail-hero-icon">
                <i class="fa-solid fa-hand-holding-heart"></i>
            </div>

            <div class="kategori-detail-hero-info">
                <span class="kategori-detail-eyebrow">
                    INFORMASI KATEGORI
                </span>

                <h2>{{ $kategori->nama }}</h2>

                <p>
                    {{ $kategori->deskripsi ?: 'Belum ada deskripsi untuk kategori ini.' }}
                </p>

                <span class="kategori-detail-count">
                    <i class="fa-solid fa-layer-group"></i>
                    {{ $kategori->jenis->count() }} jenis bantuan tersedia
                </span>
            </div>

            <a href="{{ route('admin.kategori.edit', $kategori) }}" class="kategori-detail-edit">
                <i class="fa-solid fa-pen"></i>
                Edit Kategori
            </a>
        </section>

        <section class="kategori-detail-panel">

            <div class="kategori-detail-panel-head">
                <div>
                    <span class="kategori-detail-section-label">
                        DAFTAR PROGRAM
                    </span>

                    <h3>Jenis Bantuan dalam Kategori Ini</h3>

                    <p>
                        Berikut program bantuan yang terdaftar
                        pada kategori {{ $kategori->nama }}.
                    </p>
                </div>

                <a href="{{ route('admin.jenis.create') }}" class="kategori-detail-add">
                    <i class="fa-solid fa-plus"></i>
                    Tambah Program
                </a>
            </div>

            <div class="kategori-detail-list">

                @forelse ($kategori->jenis as $jenis)
                    <article class="kategori-detail-item">

                        <div class="kategori-detail-item-icon">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>

                        <div class="kategori-detail-item-info">
                            <h4>{{ $jenis->nama_program }}</h4>

                            <p>
                                {{ $jenis->deskripsi ?: 'Belum ada deskripsi program.' }}
                            </p>

                            <span>
                                <i class="fa-solid fa-building"></i>
                                {{ $jenis->penyelenggara ?: 'Penyelenggara belum dicantumkan' }}
                            </span>
                        </div>

                        <div class="kategori-detail-item-actions">
                            <span class="kategori-detail-status {{ $jenis->aktif ? 'aktif' : 'nonaktif' }}">
                                {{ $jenis->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>

                            <a href="{{ route('admin.jenis.edit', $jenis) }}" class="kategori-detail-item-edit">
                                Edit
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>

                    </article>
                @empty
                    <div class="kategori-detail-empty">
                        <i class="fa-regular fa-folder-open"></i>
                        <strong>Belum ada jenis bantuan</strong>
                        <p>
                            Belum terdapat program bantuan dalam kategori ini.
                        </p>
                        <a href="{{ route('admin.jenis.create') }}">
                            Tambahkan program bantuan
                        </a>
                    </div>
                @endforelse

            </div>

        </section>

        <div class="kategori-detail-footer">
            <a href="{{ route('admin.kategori.index') }}">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Kategori Bantuan
            </a>
        </div>

    </div>

@endsection