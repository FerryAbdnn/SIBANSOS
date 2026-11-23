@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/export-penerima-admin.css') }}">
@endpush

@section('title', 'Export Data Penerima')
@section('heading', 'Export Data Penerima')

@section('content')

    <div class="container-fluid export-penerima-page">

        {{-- HEADER HALAMAN --}}
        <div class="export-header">
            <div>
                <h3>Export Data Penerima</h3>
                <p>
                    Unduh data penerima beserta dokumen pendukung dalam satu file ZIP.
                </p>
            </div>

            <a href="{{ route('admin.penerima.index') }}" class="export-btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Kembali
            </a>
        </div>

        {{-- NOTIFIKASI --}}
        @if(session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Terjadi kesalahan:
                </strong>

                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- KARTU FILTER EXPORT --}}
        <div class="export-card">
            <div class="export-card-body">

                <h5 class="export-section-title">
                    <span class="export-section-icon">
                        <i class="fa-solid fa-sliders"></i>
                    </span>
                    Pengaturan Export Data
                </h5>

                <p class="export-description">
                    Tentukan kriteria data yang ingin diunduh. Filter bersifat
                    opsional. Jika seluruh filter dikosongkan, sistem akan
                    mengunduh semua data penerima sesuai cakupan wilayah akun admin.
                </p>

                <form action="{{ route('admin.penerima.export.download') }}" method="POST">

                    @csrf

                    <div class="export-filter-grid">

                        {{-- FILTER RW --}}
                        <div class="export-filter-group">
                            <label for="rw">
                                Wilayah RW
                            </label>

                            <select name="rw" id="rw" class="form-select">
                                <option value="">Semua RW</option>

                                @foreach($wilayah->where('tipe', 'rw') as $item)
                                    <option value="{{ $item->id_wilayah }}" {{ old('rw') == $item->id_wilayah ? 'selected' : '' }}>
                                        RW {{ $item->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- FILTER RT --}}
                        <div class="export-filter-group">
                            <label for="rt">
                                Wilayah RT
                            </label>

                            <select name="rt" id="rt" class="form-select">
                                <option value="">Semua RT</option>

                                @foreach($wilayah->where('tipe', 'rt') as $item)
                                    <option value="{{ $item->id_wilayah }}" {{ old('rt') == $item->id_wilayah ? 'selected' : '' }}>
                                        RT {{ $item->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- FILTER PROGRAM --}}
                        <div class="export-filter-group">
                            <label for="id_jenis">
                                Program Bantuan
                            </label>

                            <select name="id_jenis" id="id_jenis" class="form-select">
                                <option value="">Semua Program Bantuan</option>

                                @foreach($jenisBantuan as $jenis)
                                    <option value="{{ $jenis->id_jenis }}" {{ old('id_jenis') == $jenis->id_jenis ? 'selected' : '' }}>
                                        {{ $jenis->nama_program }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- FILTER STATUS --}}
                        <div class="export-filter-group">
                            <label for="status">
                                Status Penerima
                            </label>

                            <select name="status" id="status" class="form-select">
                                <option value="">Semua Status</option>

                                <option value="diajukan" {{ old('status') == 'diajukan' ? 'selected' : '' }}>
                                    Diajukan
                                </option>

                                <option value="terverifikasi" {{ old('status') == 'terverifikasi' ? 'selected' : '' }}>
                                    Terverifikasi
                                </option>

                                <option value="ditolak" {{ old('status') == 'ditolak' ? 'selected' : '' }}>
                                    Ditolak
                                </option>

                                <option value="nonaktif" {{ old('status') == 'nonaktif' ? 'selected' : '' }}>
                                    Nonaktif
                                </option>
                            </select>
                        </div>

                    </div>

                    <hr class="export-divider">

                    {{-- TOMBOL AKSI --}}
                    <div class="export-actions">

                        <button type="submit" class="export-btn-primary">
                            <i class="bi bi-download"></i>
                            Unduh Data ZIP
                        </button>

                        <a href="{{ route('admin.penerima.export') }}" class="export-btn-secondary">
                            <i class="bi bi-arrow-counterclockwise"></i>
                            Reset Filter
                        </a>

                    </div>

                </form>

            </div>
        </div>

        {{-- KARTU INFORMASI EXPORT --}}
        <div class="export-card">
            <div class="export-card-body">

                <h5 class="export-section-title">
                    <span class="export-section-icon">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>
                    Informasi File Export
                </h5>

                <p class="export-description mb-0">
                    File ZIP yang diunduh berisi dua komponen berikut:
                </p>

                <div class="export-info-list">

                    {{-- INFORMASI EXCEL --}}
                    <div class="export-info-item">
                        <div class="export-info-icon">
                            <i class="fa-solid fa-file-excel"></i>
                        </div>

                        <div>
                            <strong>Data_Penerima.xlsx</strong>

                            <p>
                                Berisi identitas penerima, wilayah, program bantuan,
                                status, serta informasi kelengkapan dokumen.
                            </p>
                        </div>
                    </div>

                    {{-- INFORMASI DOKUMEN --}}
                    <div class="export-info-item">
                        <div class="export-info-icon">
                            <i class="fa-solid fa-folder-open"></i>
                        </div>

                        <div>
                            <strong>Dokumen_Pendukung</strong>

                            <p>
                                Berisi salinan dokumen asli yang tersedia,
                                dikelompokkan berdasarkan masing-masing penerima.
                            </p>
                        </div>
                    </div>

                </div>

                {{-- INFORMASI KEAMANAN --}}
                <div class="export-security-note">
                    <i class="bi bi-shield-lock"></i>

                    <div>
                        <strong>Keamanan Data</strong><br>
                        Export hanya mencakup data dalam cakupan wilayah akun admin.
                        Dokumen yang belum tersedia tidak akan dibuatkan file kosong.
                    </div>
                </div>

            </div>
        </div>

    </div>

@endsection