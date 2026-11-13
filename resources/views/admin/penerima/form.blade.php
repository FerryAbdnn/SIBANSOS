
@extends('layouts.admin')

@section('title', $p->exists ? 'Edit Penerima' : 'Tambah Penerima')
@section('heading', $p->exists ? 'Edit Data Penerima' : 'Tambah Data Penerima')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | IDENTITAS
    |--------------------------------------------------------------------------
    */

    $nikValue = old('nik', '');

    if ($p->exists && $nikValue === '') {
        try {
            $nikValue = decrypt($p->nik);
        } catch (\Throwable $e) {
            $nikValue = '';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | WILAYAH
    |--------------------------------------------------------------------------
    */

    $wilayahRows = collect($wilayah);

    $rtList = $wilayahRows->where('tipe', 'rt');
    $rwList = $wilayahRows->where('tipe', 'rw');

    $selectedRtId = old('id_wilayah', $p->id_wilayah);

    $selectedRt = $rtList->firstWhere(
        'id_wilayah',
        (int) $selectedRtId
    );

    $selectedRwId = old(
        'id_rw',
        $selectedRt->id_induk ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | CEK DOKUMEN YANG SUDAH TERSIMPAN
    |--------------------------------------------------------------------------
    */

    $hasKtp = $p->exists &&
        $p->dokumen()
            ->where('jenis_dokumen', 'ktp')
            ->exists();

    $hasKk = $p->exists &&
        $p->dokumen()
            ->where('jenis_dokumen', 'kk')
            ->exists();

    $hasDtks = $p->exists &&
        $p->dokumen()
            ->where('jenis_dokumen', 'lainnya')
            ->where('nama_file', 'like', 'Bukti DTKS/P3KE - %')
            ->exists();

    $hasFotoDepan = $p->exists &&
        $p->dokumen()
            ->where('jenis_dokumen', 'lainnya')
            ->where('nama_file', 'like', 'Foto Rumah Depan - %')
            ->exists();

    $hasFotoDalam = $p->exists &&
        $p->dokumen()
            ->where('jenis_dokumen', 'lainnya')
            ->where('nama_file', 'like', 'Foto Rumah Dalam - %')
            ->exists();

    $hasSktm = $p->exists &&
        $p->dokumen()
            ->where('jenis_dokumen', 'surat_keterangan')
            ->where('nama_file', 'like', 'SKTM - %')
            ->exists();

@endphp

<div class="recipient-form-page">

    {{-- HEADER --}}
    <div class="recipient-form-heading">
        <div>
            <h2>
                {{ $p->exists ? 'Edit Data Penerima' : 'Tambah Penerima Baru' }}
            </h2>

            <p>
                {{ $p->exists
                    ? 'Perbarui informasi penerima bantuan sosial.'
                    : 'Lengkapi identitas, kondisi ekonomi, dan dokumen penerima bantuan sosial.' }}
            </p>
        </div>

        <a href="{{ route('admin.penerima.index') }}" class="recipient-form-back">
            ← Kembali ke Data Penerima
        </a>
    </div>

    {{-- VALIDASI --}}
    @if ($errors->any())
        <div class="recipient-form-error">
            <strong>Periksa kembali data yang Anda masukkan.</strong>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        enctype="multipart/form-data"
        action="{{ $p->exists
            ? route('admin.penerima.update', $p)
            : route('admin.penerima.store') }}"
    >

        @csrf

        @if ($p->exists)
            @method('PUT')
        @endif

        {{-- =====================================================
             01. IDENTITAS PENERIMA
        ====================================================== --}}

        <div class="recipient-form-card">

            <div class="recipient-form-section-heading">
                <div class="recipient-form-section-icon">01</div>

                <div>
                    <h3>Identitas Penerima</h3>
                    <p>Informasi identitas dasar penerima bantuan sosial.</p>
                </div>
            </div>

            <div class="recipient-form-grid">

                {{-- NIK --}}
                <div class="recipient-field">
                    <label for="nik">NIK <span>*</span></label>

                    <input
                        type="text"
                        id="nik"
                        name="nik"
                        maxlength="16"
                        minlength="16"
                        inputmode="numeric"
                        pattern="[0-9]{16}"
                        value="{{ $nikValue }}"
                        placeholder="Masukkan 16 digit NIK"
                        required
                    >

                    <small>NIK harus terdiri dari 16 digit angka.</small>

                    @error('nik')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- KK --}}
                <div class="recipient-field">
                    <label for="no_kk">Nomor Kartu Keluarga (KK)</label>

                    <input
                        type="text"
                        id="no_kk"
                        name="no_kk"
                        value="{{ old('no_kk', $p->no_kk_decrypted) }}"
                        placeholder="Masukkan 16 digit nomor KK"
                        maxlength="16"
                        minlength="16"
                        inputmode="numeric"
                        pattern="[0-9]{16}"
                    >

                    <small>Nomor KK harus terdiri dari 16 digit angka.</small>

                    @error('no_kk')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- PROGRAM BANTUAN --}}
                <div class="recipient-field">
                    <label for="id_jenis">
                        Program Bantuan yang Dituju <span>*</span>
                    </label>

                    <select id="id_jenis" name="id_jenis" required>
                        <option value="">Pilih program bantuan</option>

                        @foreach ($jenisBantuan as $jenis)
                            <option
                                value="{{ $jenis->id_jenis }}"
                                @selected(
                                    (string) old('id_jenis', $p->id_jenis)
                                    === (string) $jenis->id_jenis
                                )
                            >
                                {{ $jenis->nama_program }}
                            </option>
                        @endforeach
                    </select>

                    @error('id_jenis')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- NAMA --}}
                <div class="recipient-field">
                    <label for="nama">Nama Lengkap <span>*</span></label>

                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        maxlength="100"
                        value="{{ old('nama', $p->nama) }}"
                        placeholder="Masukkan nama lengkap"
                        required
                    >

                    @error('nama')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- TANGGAL LAHIR --}}
                <div class="recipient-field">
                    <label for="tanggal_lahir">Tanggal Lahir <span>*</span></label>

                    <input
                        type="date"
                        id="tanggal_lahir"
                        name="tanggal_lahir"
                        value="{{ old('tanggal_lahir', $p->tanggal_lahir?->format('Y-m-d')) }}"
                        required
                    >

                    @error('tanggal_lahir')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- JENIS KELAMIN --}}
                <div class="recipient-field">
                    <label for="jenis_kelamin">Jenis Kelamin <span>*</span></label>

                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="">Pilih jenis kelamin</option>

                        <option
                            value="L"
                            @selected(old('jenis_kelamin', $p->jenis_kelamin) === 'L')
                        >
                            Laki-laki
                        </option>

                        <option
                            value="P"
                            @selected(old('jenis_kelamin', $p->jenis_kelamin) === 'P')
                        >
                            Perempuan
                        </option>
                    </select>

                    @error('jenis_kelamin')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- WHATSAPP --}}
                <div class="recipient-field">
                    <label for="nomor_whatsapp">
                        Nomor WhatsApp <span>*</span>
                    </label>

                    <input
                        type="tel"
                        id="nomor_whatsapp"
                        name="nomor_whatsapp"
                        maxlength="20"
                        value="{{ old('nomor_whatsapp', $p->nomor_whatsapp) }}"
                        placeholder="Contoh: 081234567890"
                        required
                    >

                    <small>Masukkan nomor WhatsApp yang aktif.</small>

                    @error('nomor_whatsapp')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- ALAMAT --}}
                <div class="recipient-field full">
                    <label for="alamat">Alamat Lengkap <span>*</span></label>

                    <textarea
                        id="alamat"
                        name="alamat"
                        rows="3"
                        placeholder="Masukkan nama jalan, nomor rumah, dan keterangan alamat"
                        required
                    >{{ old('alamat', $p->alamat) }}</textarea>

                    @error('alamat')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

            </div>
        </div>

        {{-- =====================================================
             02. INFORMASI WILAYAH
        ====================================================== --}}

        <div class="recipient-form-card">

            <div class="recipient-form-section-heading">
                <div class="recipient-form-section-icon">02</div>

                <div>
                    <h3>Informasi Wilayah</h3>
                    <p>Tentukan RT dan RW sesuai alamat tempat tinggal penerima.</p>
                </div>
            </div>

            <div class="recipient-form-grid">

                {{-- RW --}}
                <div class="recipient-field">
                    <label for="id_rw">RW <span>*</span></label>

                    <select id="id_rw" name="id_rw" required>
                        <option value="">Pilih RW</option>

                        @foreach ($rwList as $rw)
                            <option
                                value="{{ $rw->id_wilayah }}"
                                @selected((string) $selectedRwId === (string) $rw->id_wilayah)
                            >
                                {{ $rw->nama }}
                            </option>
                        @endforeach
                    </select>

                    @error('id_rw')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- RT --}}
                <div class="recipient-field">
                    <label for="id_wilayah">RT <span>*</span></label>

                    <select id="id_wilayah" name="id_wilayah" required>
                        <option value="">Pilih RT</option>

                        @foreach ($rtList as $rt)
                            <option
                                value="{{ $rt->id_wilayah }}"
                                data-rw="{{ $rt->id_induk }}"
                                @selected((string) $selectedRtId === (string) $rt->id_wilayah)
                            >
                                {{ $rt->nama }}
                            </option>
                        @endforeach
                    </select>

                    @error('id_wilayah')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <small class="recipient-form-hint">
                Pilihan RT akan menyesuaikan RW yang dipilih.
            </small>

        </div>

        {{-- =====================================================
             03. KONDISI EKONOMI
        ====================================================== --}}

        <div class="recipient-form-card">

            <div class="recipient-form-section-heading">
                <div class="recipient-form-section-icon">03</div>

                <div>
                    <h3>Kondisi Ekonomi</h3>
                    <p>Informasi kondisi ekonomi dan tanggungan penerima.</p>
                </div>
            </div>

            <div class="recipient-form-grid">

                {{-- PENGHASILAN --}}
                <div class="recipient-field">
                    <label for="penghasilan_bulanan">
                        Penghasilan Bulanan <span>*</span>
                    </label>

                    <div class="recipient-input-prefix">
                        <span>Rp</span>

                        <input
                            type="number"
                            id="penghasilan_bulanan"
                            name="penghasilan_bulanan"
                            min="0"
                            step="1000"
                            value="{{ old('penghasilan_bulanan', $p->penghasilan_bulanan) }}"
                            placeholder="Contoh: 1500000"
                            required
                        >
                    </div>

                    @error('penghasilan_bulanan')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                {{-- TANGGUNGAN --}}
                <div class="recipient-field">
                    <label for="jumlah_tanggungan">
                        Jumlah Tanggungan <span>*</span>
                    </label>

                    <input
                        type="number"
                        id="jumlah_tanggungan"
                        name="jumlah_tanggungan"
                        min="0"
                        max="99"
                        value="{{ old('jumlah_tanggungan', $p->jumlah_tanggungan ?? 0) }}"
                        required
                    >

                    @error('jumlah_tanggungan')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            {{-- KONDISI RUMAH --}}
            <div class="recipient-field recipient-house-field">
                <label>Kondisi Rumah <span>*</span></label>

                <div class="recipient-house-options">

                    @foreach ([
                        'permanen' => ['Permanen', 'Dinding bata atau beton'],
                        'semi_permanen' => ['Semi Permanen', 'Sebagian kayu atau batako'],
                        'tidak_layak' => ['Tidak Layak', 'Kondisi rumah tidak layak huni'],
                    ] as $value => $item)

                        <label class="recipient-house-option">

                            <input
                                type="radio"
                                name="kondisi_rumah"
                                value="{{ $value }}"
                                @checked(old('kondisi_rumah', $p->kondisi_rumah) === $value)
                                required
                            >

                            <span>
                                <strong>{{ $item[0] }}</strong>
                                <small>{{ $item[1] }}</small>
                            </span>

                        </label>

                    @endforeach

                </div>

                @error('kondisi_rumah')
                    <small class="field-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- KONDISI KHUSUS --}}
            <div class="recipient-special-condition">

                <h4>Kondisi Khusus Penerima</h4>
                <p>Pilih jika penerima termasuk dalam kondisi berikut.</p>

                <div class="recipient-checkbox-grid">

                    <label class="recipient-checkbox-card">
                        <input type="hidden" name="disabilitas" value="0">

                        <input
                            type="checkbox"
                            name="disabilitas"
                            value="1"
                            @checked((bool) old('disabilitas', $p->disabilitas))
                        >

                        <span class="recipient-checkbox-content">
                            <strong>Penyandang Disabilitas</strong>
                            <small>Penerima memiliki kondisi disabilitas.</small>
                        </span>
                    </label>

                    <label class="recipient-checkbox-card">
                        <input type="hidden" name="lansia" value="0">

                        <input
                            type="checkbox"
                            name="lansia"
                            value="1"
                            @checked((bool) old('lansia', $p->lansia))
                        >

                        <span class="recipient-checkbox-content">
                            <strong>Lansia</strong>
                            <small>Penerima termasuk kelompok lanjut usia.</small>
                        </span>
                    </label>

                </div>
            </div>

        </div>

        {{-- =====================================================
             04. DOKUMEN PENDUKUNG
        ====================================================== --}}

        <div class="recipient-form-card recipient-document-card">

            <div class="recipient-form-section-heading">
                <div class="recipient-form-section-icon">04</div>

                <div>
                    <h3>Dokumen Pendukung</h3>
                    <p>Unggah dokumen identitas untuk melengkapi data penerima.</p>
                </div>
            </div>

            <div class="recipient-document-grid">

                {{-- KTP --}}
                <div class="recipient-document-upload">

                    <label for="dokumen_ktp" class="recipient-upload-area">
                        <span class="recipient-upload-icon">↑</span>
                        <strong>Foto KTP</strong>

                        <span class="recipient-upload-description">
                            JPG atau PNG · Maksimal 2 MB
                        </span>

                        <span class="recipient-upload-button">Pilih File</span>

                        <input
                            type="file"
                            id="dokumen_ktp"
                            name="dokumen_ktp"
                            accept=".jpg,.jpeg,.png"
                            data-existing="{{ $hasKtp ? '1' : '0' }}"
                            {{ $hasKtp ? '' : 'required' }}
                        >
                    </label>

                    <small
                        id="nama-ktp"
                        class="recipient-file-status {{ $hasKtp ? 'recipient-file-existing' : '' }}"
                    >
                        {{ $hasKtp ? '✓ Dokumen KTP sudah tersedia.' : 'Belum ada file dipilih.' }}
                    </small>

                    @error('dokumen_ktp')
                        <small class="field-error">{{ $message }}</small>
                    @enderror

                </div>

                {{-- KK --}}
                <div class="recipient-document-upload">

                    <label for="dokumen_kk" class="recipient-upload-area">
                        <span class="recipient-upload-icon">↑</span>
                        <strong>Kartu Keluarga (KK)</strong>

                        <span class="recipient-upload-description">
                            JPG, PNG, atau PDF · Maksimal 2 MB
                        </span>

                        <span class="recipient-upload-button">Pilih File</span>

                        <input
                            type="file"
                            id="dokumen_kk"
                            name="dokumen_kk"
                            accept=".jpg,.jpeg,.png,.pdf"
                            data-existing="{{ $hasKk ? '1' : '0' }}"
                            {{ $hasKk ? '' : 'required' }}
                        >
                    </label>

                    <small
                        id="nama-kk"
                        class="recipient-file-status {{ $hasKk ? 'recipient-file-existing' : '' }}"
                    >
                        {{ $hasKk ? '✓ Dokumen KK sudah tersedia.' : 'Belum ada file dipilih.' }}
                    </small>

                    @error('dokumen_kk')
                        <small class="field-error">{{ $message }}</small>
                    @enderror

                </div>

                {{-- DTKS / P3KE --}}
                <div class="recipient-document-upload">

                    <label for="dokumen_dtks_p3ke" class="recipient-upload-area">
                        <span class="recipient-upload-icon">↑</span>
                        <strong>Bukti DTKS / P3KE</strong>

                        <span class="recipient-upload-description">
                            JPG, PNG, atau PDF · Maksimal 2 MB
                        </span>

                        <span class="recipient-upload-button">Pilih File</span>

                        <input
                            type="file"
                            id="dokumen_dtks_p3ke"
                            name="dokumen_dtks_p3ke"
                            accept=".jpg,.jpeg,.png,.pdf"
                            data-existing="{{ $hasDtks ? '1' : '0' }}"
                            {{ $hasDtks ? '' : 'required' }}
                        >
                    </label>

                    <small
                        id="nama-dtks"
                        class="recipient-file-status {{ $hasDtks ? 'recipient-file-existing' : '' }}"
                    >
                        {{ $hasDtks ? '✓ Bukti DTKS/P3KE sudah tersedia.' : 'Belum ada file dipilih.' }}
                    </small>

                    @error('dokumen_dtks_p3ke')
                        <small class="field-error">{{ $message }}</small>
                    @enderror

                </div>

                {{-- FOTO DEPAN --}}
                <div class="recipient-document-upload">

                    <label for="foto_rumah_depan" class="recipient-upload-area">
                        <span class="recipient-upload-icon">↑</span>
                        <strong>Foto Rumah Tampak Depan</strong>

                        <span class="recipient-upload-description">
                            JPG atau PNG · Maksimal 2 MB
                        </span>

                        <span class="recipient-upload-button">Pilih Foto</span>

                        <input
                            type="file"
                            id="foto_rumah_depan"
                            name="foto_rumah_depan"
                            accept=".jpg,.jpeg,.png"
                            data-existing="{{ $hasFotoDepan ? '1' : '0' }}"
                            {{ $hasFotoDepan ? '' : 'required' }}
                        >
                    </label>

                    <small
                        id="nama-depan"
                        class="recipient-file-status {{ $hasFotoDepan ? 'recipient-file-existing' : '' }}"
                    >
                        {{ $hasFotoDepan ? '✓ Foto rumah depan sudah tersedia.' : 'Belum ada foto dipilih.' }}
                    </small>

                    @error('foto_rumah_depan')
                        <small class="field-error">{{ $message }}</small>
                    @enderror

                </div>

                {{-- FOTO DALAM --}}
                <div class="recipient-document-upload">

                    <label for="foto_rumah_dalam" class="recipient-upload-area">
                        <span class="recipient-upload-icon">↑</span>
                        <strong>Foto Bagian Dalam Rumah</strong>

                        <span class="recipient-upload-description">
                            JPG atau PNG · Maksimal 2 MB
                        </span>

                        <span class="recipient-upload-button">Pilih Foto</span>

                        <input
                            type="file"
                            id="foto_rumah_dalam"
                            name="foto_rumah_dalam"
                            accept=".jpg,.jpeg,.png"
                            data-existing="{{ $hasFotoDalam ? '1' : '0' }}"
                            {{ $hasFotoDalam ? '' : 'required' }}
                        >
                    </label>

                    <small
                        id="nama-dalam"
                        class="recipient-file-status {{ $hasFotoDalam ? 'recipient-file-existing' : '' }}"
                    >
                        {{ $hasFotoDalam ? '✓ Foto bagian dalam rumah sudah tersedia.' : 'Belum ada foto dipilih.' }}
                    </small>

                    @error('foto_rumah_dalam')
                        <small class="field-error">{{ $message }}</small>
                    @enderror

                </div>

                {{-- SKTM --}}
                <div class="recipient-document-upload">

                    <label for="dokumen_sktm" class="recipient-upload-area">
                        <span class="recipient-upload-icon">↑</span>
                        <strong>Surat Keterangan Tidak Mampu (SKTM)</strong>

                        <span class="recipient-upload-description">
                            JPG, PNG, atau PDF · Maksimal 2 MB
                        </span>

                        <span class="recipient-upload-button">Pilih File</span>

                        <input
                            type="file"
                            id="dokumen_sktm"
                            name="dokumen_sktm"
                            accept=".jpg,.jpeg,.png,.pdf"
                            data-existing="{{ $hasSktm ? '1' : '0' }}"
                            {{ $hasSktm ? '' : 'required' }}
                        >
                    </label>

                    <small
                        id="nama-sktm"
                        class="recipient-file-status {{ $hasSktm ? 'recipient-file-existing' : '' }}"
                    >
                        {{ $hasSktm ? '✓ Dokumen SKTM sudah tersedia.' : 'Belum ada file dipilih.' }}
                    </small>

                    @error('dokumen_sktm')
                        <small class="field-error">{{ $message }}</small>
                    @enderror

                </div>

            </div>

            <p class="recipient-form-hint">
                Pastikan seluruh dokumen terbaca dengan jelas. Bukti DTKS/P3KE
                digunakan untuk mendukung pemeriksaan status kesejahteraan,
                sedangkan foto rumah dan SKTM digunakan untuk asesmen kondisi
                tempat tinggal dan ekonomi penerima.
            </p>

        </div>

        {{-- =====================================================
             TOMBOL AKSI
        ====================================================== --}}

        <div class="recipient-form-actions">

            <a
                href="{{ route('admin.penerima.index') }}"
                class="recipient-form-cancel"
            >
                Batal
            </a>

            <button type="submit" class="recipient-form-submit">
                {{ $p->exists ? 'Simpan Perubahan' : '＋ Simpan Penerima' }}
            </button>

        </div>

    </form>

</div>

<style>

/* =========================================
   HALAMAN FORM
========================================= */

.recipient-form-page {
    width: 100%;
    max-width: 1180px;
    margin: 0 auto;
    color: #20391a;
}

/* =========================================
   HEADER
========================================= */

.recipient-form-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 22px;
}

.recipient-form-heading h2 {
    margin: 0 0 6px;
    font-size: 21px;
    font-weight: 700;
}

.recipient-form-heading p {
    margin: 0;
    color: #7b8875;
    font-size: 13px;
    line-height: 1.5;
}

.recipient-form-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 14px;
    border: 1px solid #dce5d8;
    border-radius: 8px;
    color: #174f37;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    background: #fff;
}

.recipient-form-back:hover {
    background: #f1f6ef;
}

/* =========================================
   ERROR VALIDASI
========================================= */

.recipient-form-error {
    padding: 14px 17px;
    margin-bottom: 18px;
    border: 1px solid #fecaca;
    border-radius: 10px;
    background: #fef2f2;
    color: #991b1b;
    font-size: 13px;
}

.recipient-form-error ul {
    margin: 8px 0 0;
    padding-left: 20px;
}

.recipient-form-error li {
    margin: 4px 0;
}

.field-error {
    display: block;
    margin-top: 6px;
    color: #dc2626 !important;
    font-size: 12px;
}

/* =========================================
   FORM CARD
========================================= */

.recipient-form-card {
    padding: 24px;
    margin-bottom: 18px;
    border: 1px solid #e5eadd;
    border-radius: 14px;
    background: #fff;
}

/* =========================================
   JUDUL BAGIAN
========================================= */

.recipient-form-section-heading {
    display: flex;
    align-items: center;
    gap: 13px;
    margin-bottom: 23px;
}

.recipient-form-section-icon {
    width: 39px;
    height: 39px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #eaf2e7;
    color: #174f37;
    font-size: 13px;
    font-weight: 800;
}

.recipient-form-section-heading h3 {
    margin: 0 0 4px;
    color: #20391a;
    font-size: 16px;
    font-weight: 700;
}

.recipient-form-section-heading p {
    margin: 0;
    color: #7b8875;
    font-size: 12px;
    line-height: 1.5;
}

/* =========================================
   GRID FORM
========================================= */

.recipient-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.recipient-field {
    min-width: 0;
}

.recipient-field.full {
    grid-column: 1 / -1;
}

/* =========================================
   LABEL
========================================= */

.recipient-field > label {
    display: block;
    margin-bottom: 8px;
    color: #30452c;
    font-size: 13px;
    font-weight: 600;
}

.recipient-field > label span {
    color: #dc2626;
}

/* =========================================
   INPUT
========================================= */

.recipient-field input:not([type="checkbox"]):not([type="radio"]),
.recipient-field select,
.recipient-field textarea {
    width: 100%;
    min-height: 43px;
    padding: 10px 12px;
    box-sizing: border-box;
    border: 1px solid #dce3d6;
    border-radius: 8px;
    background: #fff;
    color: #20391a;
    font: inherit;
    font-size: 13px;
}

.recipient-field textarea {
    resize: vertical;
    min-height: 90px;
}

.recipient-field input:focus,
.recipient-field select:focus,
.recipient-field textarea:focus {
    outline: none;
    border-color: #174f37;
    box-shadow: 0 0 0 3px rgba(23, 79, 55, .10);
}

/* =========================================
   PETUNJUK FORM
========================================= */

.recipient-field > small,
.recipient-form-hint {
    display: block;
    margin-top: 7px;
    color: #7b8875;
    font-size: 12px;
    line-height: 1.5;
}

/* =========================================
   PENGHASILAN
========================================= */


.recipient-input-prefix {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    width: 100%;
    height: 43px;
    min-height: 43px;
    padding: 0 !important;
    border: 1px solid #dce3d6;
    border-radius: 8px;
    overflow: hidden;
    box-sizing: border-box;
}

.recipient-input-prefix:focus-within {
    border-color: #174f37;
    box-shadow: 0 0 0 3px rgba(23, 79, 55, .10);
}

.recipient-input-prefix span {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 100%;
    padding: 0 13px !important;
    margin: 0 !important;
    border: none;
    border-right: 1px solid #e2e8f0;
    border-radius: 0 !important;
    background: #f8faf7;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
    line-height: normal !important;
    box-sizing: border-box;
    flex-shrink: 0;
}

.recipient-input-prefix input,
.recipient-input-prefix input.form-control {
    display: block !important;
    flex: 1 1 auto !important;
    width: 0 !important;
    min-width: 0 !important;
    height: 100% !important;
    min-height: 0 !important;
    padding: 0 12px !important;
    margin: 0 !important;
    border: none !important;
    border-radius: 0 !important;
    outline: none !important;
    box-shadow: none !important;
    background: #fff;
    font-size: 14px;
    line-height: normal !important;
    box-sizing: border-box;
}

/* =========================================
   KONDISI RUMAH
========================================= */

.recipient-house-field {
    margin-top: 20px;
}

.recipient-house-options {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.recipient-house-option {
    min-height: 85px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px;
    box-sizing: border-box;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    cursor: pointer;
}

.recipient-house-option:hover {
    border-color: #9caf98;
}

.recipient-house-option:has(input:checked) {
    border-color: #174f37;
    background: #f7faf6;
}

.recipient-house-option input {
    flex-shrink: 0;
    width: 16px;
    height: 16px;
    margin: 0;
    accent-color: #174f37;
}

.recipient-house-option input:focus,
.recipient-house-option input:focus-visible,
.recipient-house-option input:active {
    outline: none !important;
    box-shadow: none !important;
}

.recipient-house-option span {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.recipient-house-option strong {
    color: #20391a;
    font-size: 13px;
    line-height: 1.4;
}

.recipient-house-option small {
    color: #64748b;
    font-size: 11px;
    line-height: 1.4;
}

/* =========================================
   KONDISI KHUSUS
========================================= */

.recipient-special-condition {
    margin-top: 23px;
    padding: 17px;
    border: 1px solid #e5eadd;
    border-radius: 10px;
    background: #fafcf9;
}

.recipient-special-condition h4 {
    margin: 0 0 5px;
    color: #20391a;
    font-size: 14px;
}

.recipient-special-condition > p {
    margin: 0 0 15px;
    color: #7b8875;
    font-size: 12px;
}

.recipient-checkbox-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.recipient-checkbox-card {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 13px;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #fff;
    cursor: pointer;
}

.recipient-checkbox-card:has(input[type="checkbox"]:checked) {
    border-color: #174f37;
    background: #f7faf6;
}

.recipient-checkbox-card input[type="checkbox"] {
    width: 17px;
    height: 17px;
    flex-shrink: 0;
    accent-color: #174f37;
}

.recipient-checkbox-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.recipient-checkbox-content strong {
    color: #20391a;
    font-size: 12px;
}

.recipient-checkbox-content small {
    color: #7b8875;
    font-size: 11px;
    line-height: 1.4;
}

/* =========================================
   DOKUMEN PENDUKUNG
========================================= */

.recipient-document-card {
    padding: 24px;
}

.recipient-document-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.recipient-document-upload {
    min-width: 0;
    position: relative;
}

/* Input tetap bisa difokuskan melalui label */

.recipient-document-upload input[type="file"] {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.recipient-upload-area:focus-within {
    outline: 2px solid #174f37;
    outline-offset: 3px;
}

/* Kotak upload */

.recipient-upload-area {
    min-height: 150px;
    padding: 20px 14px;
    border: 1.5px dashed #a9bca1;
    border-radius: 11px;
    background: #f0f4ed;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-align: center;
    cursor: pointer;
    box-sizing: border-box;
    transition: background .2s ease, border-color .2s ease;
}

.recipient-upload-area:hover {
    background: #e8f0e5;
    border-color: #174f37;
}

/* Ikon */

.recipient-upload-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #fff;
    color: #174f37;
    font-size: 23px;
    font-weight: 500;
}

/* Teks */

.recipient-upload-area strong {
    color: #20391a;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.4;
}

.recipient-upload-description {
    color: #6f7e69;
    font-size: 10px;
    line-height: 1.4;
}

/* Tombol pilih file */

.recipient-upload-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 7px 14px;
    margin-top: 3px;
    border: 1px solid #cbd9c6;
    border-radius: 6px;
    background: #fff;
    color: #174f37;
    font-size: 11px;
    font-weight: 700;
}

/* Status file */

.recipient-file-status {
    display: block;
    margin-top: 9px;
    color: #879381;
    font-size: 11px;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.recipient-file-status.recipient-file-existing {
    color: #287346;
    font-weight: 600;
}

.recipient-document-upload .field-error {
    display: block;
    margin-top: 6px;
    color: #dc2626;
    font-size: 12px;
}

/* =========================================
   TOMBOL FORM
========================================= */

.recipient-form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    margin: 20px 0 30px;
}

.recipient-form-cancel,
.recipient-form-submit {
    min-height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 19px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    box-sizing: border-box;
}

.recipient-form-cancel {
    border: 1px solid #dce3d6;
    background: #fff;
    color: #475569;
}

.recipient-form-cancel:hover {
    background: #f8faf7;
}

.recipient-form-submit {
    border: 1px solid #174f37;
    background: #174f37;
    color: #fff;
}

.recipient-form-submit:hover {
    background: #103d2a;
}

/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 700px) {

    .recipient-form-card {
        padding: 17px;
    }

    .recipient-form-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .recipient-form-grid {
        grid-template-columns: 1fr;
    }

    .recipient-field.full {
        grid-column: auto;
    }

    .recipient-house-options {
        grid-template-columns: 1fr;
    }

    .recipient-checkbox-grid {
        grid-template-columns: 1fr;
    }

    .recipient-document-grid {
        grid-template-columns: 1fr;
    }

    .recipient-upload-area {
        min-height: 140px;
    }

    .recipient-form-actions {
        flex-direction: column-reverse;
    }

    .recipient-form-actions > * {
        width: 100%;
        text-align: center;
    }
}

</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | FILTER RT BERDASARKAN RW
    |--------------------------------------------------------------------------
    */

    const rwSelect = document.getElementById('id_rw');
    const rtSelect = document.getElementById('id_wilayah');

    if (rwSelect && rtSelect) {

        function filterRT() {

            const selectedRW = rwSelect.value;
            let selectedStillValid = false;

            Array.from(rtSelect.options).forEach(function (option, index) {

                if (index === 0) {
                    return;
                }

                const match = option.dataset.rw === selectedRW;

                option.hidden = !match;
                option.disabled = !match;

                if (match && option.selected) {
                    selectedStillValid = true;
                }
            });

            if (!selectedStillValid) {
                rtSelect.value = '';
            }
        }

        rwSelect.addEventListener('change', filterRT);

        filterRT();
    }

    /*
    |--------------------------------------------------------------------------
    | TAMPILKAN NAMA FILE
    |--------------------------------------------------------------------------
    */

    function showFileName(inputId, outputId) {

        const input = document.getElementById(inputId);
        const output = document.getElementById(outputId);

        if (!input || !output) {
            return;
        }

        const hasExistingDocument = input.dataset.existing === '1';

        const existingMessage = output.textContent;

        input.addEventListener('change', function () {

            if (this.files && this.files.length > 0) {

                output.textContent = '✓ ' + this.files[0].name;

                output.classList.add('recipient-file-existing');

            } else {

                if (hasExistingDocument) {

                    output.textContent = existingMessage;

                    output.classList.add('recipient-file-existing');

                } else {

                    output.textContent = 'Belum ada file dipilih.';

                    output.classList.remove('recipient-file-existing');
                }
            }
        });
    }

    showFileName('dokumen_ktp', 'nama-ktp');
    showFileName('dokumen_kk', 'nama-kk');
    showFileName('dokumen_dtks_p3ke', 'nama-dtks');
    showFileName('foto_rumah_depan', 'nama-depan');
    showFileName('foto_rumah_dalam', 'nama-dalam');
    showFileName('dokumen_sktm', 'nama-sktm');

});
</script>

@endsection
