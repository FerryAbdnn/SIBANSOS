
@extends('layouts.masyarakat')

@section('title', 'Pengajuan Bantuan')
@section('heading', 'Formulir Pengajuan Bantuan')

@section('content')

@php
    $wilayahRows = collect($wilayah);
    $rtList = $wilayahRows->where('tipe', 'rt');
    $rwList = $wilayahRows->where('tipe', 'rw');

    $selectedRtId = old('id_wilayah', $penerima->id_wilayah ?? '');
    $selectedRt = $rtList->firstWhere('id_wilayah', (int) $selectedRtId);
    $selectedRwId = old('id_rw', $selectedRt->id_induk ?? '');

    $sudahTerdaftar = isset($penerima) && $penerima !== null;
@endphp

<div class="recipient-form-page">

    <div class="recipient-form-heading">
        <div>
            <h2>Formulir Pengajuan Bantuan</h2>
            <p>Lengkapi identitas, kondisi ekonomi, dan dokumen untuk mengajukan bantuan sosial.</p>
        </div>
    </div>

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
        action="{{ route('masyarakat.pengajuan.store') }}"
    >
        @csrf

        {{-- IDENTITAS PENERIMA --}}
        <div class="recipient-form-card">

            <div class="recipient-form-section-heading">
                <div class="recipient-form-section-icon">01</div>
                <div>
                    <h3>Identitas Penerima</h3>
                    <p>
                        @if ($sudahTerdaftar)
                            Identitas menggunakan data yang sudah terdaftar dan tidak dapat diubah melalui formulir ini.
                        @else
                            Informasi identitas dasar calon penerima bantuan sosial.
                        @endif
                    </p>
                </div>
            </div>

            <div class="recipient-form-grid">

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
                        value="{{ old('nik', $nik ?? '') }}"
                        placeholder="Masukkan 16 digit NIK"
                        {{ $sudahTerdaftar ? 'readonly' : 'required' }}
                    >
                    @if (!$sudahTerdaftar)
                        <small>NIK harus terdiri dari 16 digit angka.</small>
                    @else
                        <small>Untuk memperbaiki NIK, gunakan fitur pengajuan perbaikan data.</small>
                    @endif
                    @error('nik') <small class="field-error">{{ $message }}</small> @enderror
                </div>

                <div class="recipient-field">
                    <label for="no_kk">Nomor Kartu Keluarga (KK) <span>*</span></label>
                    <input
                        type="text"
                        id="no_kk"
                        name="no_kk"
                        maxlength="16"
                        minlength="16"
                        inputmode="numeric"
                        pattern="[0-9]{16}"
                        value="{{ old('no_kk', $penerima->no_kk_decrypted ?? '') }}"
                        placeholder="Masukkan 16 digit nomor KK"
                        {{ $sudahTerdaftar ? 'readonly' : 'required' }}
                    >
                    @error('no_kk') <small class="field-error">{{ $message }}</small> @enderror
                </div>

                <div class="recipient-field">
                    <label for="id_jenis">Program Bantuan yang Dituju <span>*</span></label>
                    <select id="id_jenis" name="id_jenis" required>
                        <option value="">Pilih program bantuan</option>
                        @foreach ($jenisBantuan as $jenis)
                            <option
                                value="{{ $jenis->id_jenis }}"
                                @selected((string) old('id_jenis') === (string) $jenis->id_jenis)
                            >
                                {{ $jenis->nama_program }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_jenis') <small class="field-error">{{ $message }}</small> @enderror
                </div>

                <div class="recipient-field">
                    <label for="nama">Nama Lengkap <span>*</span></label>
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        maxlength="100"
                        value="{{ old('nama', $penerima->nama ?? '') }}"
                        placeholder="Masukkan nama lengkap"
                        {{ $sudahTerdaftar ? 'readonly' : 'required' }}
                    >
                    @error('nama') <small class="field-error">{{ $message }}</small> @enderror
                </div>

                <div class="recipient-field">
                    <label for="tanggal_lahir">Tanggal Lahir <span>*</span></label>
                    <input
                        type="date"
                        id="tanggal_lahir"
                        name="tanggal_lahir"
                        value="{{ old('tanggal_lahir', isset($penerima) ? $penerima->tanggal_lahir?->format('Y-m-d') : '') }}"
                        {{ $sudahTerdaftar ? 'readonly' : 'required' }}
                    >
                    @error('tanggal_lahir') <small class="field-error">{{ $message }}</small> @enderror
                </div>

                <div class="recipient-field">
                    <label for="jenis_kelamin">Jenis Kelamin <span>*</span></label>
                    <select
                        id="jenis_kelamin"
                        name="jenis_kelamin"
                        {{ $sudahTerdaftar ? 'disabled' : 'required' }}
                    >
                        <option value="">Pilih jenis kelamin</option>
                        <option value="L" @selected(old('jenis_kelamin', $penerima->jenis_kelamin ?? '') === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('jenis_kelamin', $penerima->jenis_kelamin ?? '') === 'P')>Perempuan</option>
                    </select>

                    @if ($sudahTerdaftar)
                        <input type="hidden" name="jenis_kelamin" value="{{ $penerima->jenis_kelamin }}">
                    @endif

                    @error('jenis_kelamin') <small class="field-error">{{ $message }}</small> @enderror
                </div>

                <div class="recipient-field">
                    <label for="nomor_whatsapp">Nomor WhatsApp <span>*</span></label>
                    <input
                        type="tel"
                        id="nomor_whatsapp"
                        name="nomor_whatsapp"
                        maxlength="20"
                        value="{{ old('nomor_whatsapp', $penerima->nomor_whatsapp ?? '') }}"
                        placeholder="Contoh: 081234567890"
                        required
                    >
                    <small>Masukkan nomor WhatsApp yang aktif.</small>
                    @error('nomor_whatsapp') <small class="field-error">{{ $message }}</small> @enderror
                </div>

                <div class="recipient-field full">
                    <label for="alamat">Alamat Lengkap <span>*</span></label>
                    <textarea
                        id="alamat"
                        name="alamat"
                        rows="3"
                        placeholder="Masukkan nama jalan, nomor rumah, kelurahan, dan keterangan alamat"
                        required
                    >{{ old('alamat', $penerima->alamat ?? '') }}</textarea>
                    @error('alamat') <small class="field-error">{{ $message }}</small> @enderror
                </div>

            </div>
        </div>

        {{-- WILAYAH --}}
        <div class="recipient-form-card">

            <div class="recipient-form-section-heading">
                <div class="recipient-form-section-icon">02</div>
                <div>
                    <h3>Informasi Wilayah</h3>
                    <p>Tentukan RT dan RW sesuai alamat tempat tinggal.</p>
                </div>
            </div>

            <div class="recipient-form-grid">

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
                    @error('id_rw') <small class="field-error">{{ $message }}</small> @enderror
                </div>

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
                    @error('id_wilayah') <small class="field-error">{{ $message }}</small> @enderror
                </div>

            </div>

            <small class="recipient-form-hint">
                Pilihan RT akan menyesuaikan RW yang dipilih.
            </small>
        </div>

        {{-- KONDISI EKONOMI --}}
        <div class="recipient-form-card">

            <div class="recipient-form-section-heading">
                <div class="recipient-form-section-icon">03</div>
                <div>
                    <h3>Kondisi Ekonomi</h3>
                    <p>Informasi kondisi ekonomi dan tanggungan calon penerima.</p>
                </div>
            </div>

            <div class="recipient-form-grid">

                <div class="recipient-field">
                    <label for="penghasilan_bulanan">Penghasilan Bulanan <span>*</span></label>
                    <div class="recipient-input-prefix">
                        <span>Rp</span>
                        <input
                            type="number"
                            id="penghasilan_bulanan"
                            name="penghasilan_bulanan"
                            min="0"
                            step="1000"
                            value="{{ old('penghasilan_bulanan', $penerima->penghasilan_bulanan ?? '') }}"
                            placeholder="Contoh: 1500000"
                            required
                        >
                    </div>
                    @error('penghasilan_bulanan') <small class="field-error">{{ $message }}</small> @enderror
                </div>

                <div class="recipient-field">
                    <label for="jumlah_tanggungan">Jumlah Tanggungan <span>*</span></label>
                    <input
                        type="number"
                        id="jumlah_tanggungan"
                        name="jumlah_tanggungan"
                        min="0"
                        max="99"
                        value="{{ old('jumlah_tanggungan', $penerima->jumlah_tanggungan ?? 0) }}"
                        required
                    >
                    @error('jumlah_tanggungan') <small class="field-error">{{ $message }}</small> @enderror
                </div>

            </div>

            <div class="recipient-field recipient-house-field">
                <label>Kondisi Rumah <span>*</span></label>

                <div class="recipient-house-options">
                    @foreach ([
                        'permanen' => ['Permanen', 'Rumah Sendiri'],
                        'semi_permanen' => ['Semi Permanen', 'Rumah Sewa, Kosan, DLL'],
                        'tidak_layak' => ['Tidak Layak', 'Kondisi rumah tidak layak huni'],
                    ] as $value => $item)

                        <label class="recipient-house-option">
                            <input
                                type="radio"
                                name="kondisi_rumah"
                                value="{{ $value }}"
                                @checked(old('kondisi_rumah', $penerima->kondisi_rumah ?? '') === $value)
                                required
                            >
                            <span>
                                <strong>{{ $item[0] }}</strong>
                                <small>{{ $item[1] }}</small>
                            </span>
                        </label>

                    @endforeach
                </div>

                @error('kondisi_rumah') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="recipient-special-condition">
                <h4>Kondisi Khusus Penerima</h4>
                <p>Pilih jika calon penerima termasuk dalam kondisi berikut.</p>

                <div class="recipient-checkbox-grid">

                    <label class="recipient-checkbox-card">
                        <input type="hidden" name="disabilitas" value="0">
                        <input
                            type="checkbox"
                            name="disabilitas"
                            value="1"
                            @checked((string) old('disabilitas', (int) ($penerima->disabilitas ?? 0)) === '1')
                        >
                        <span class="recipient-checkbox-content">
                            <strong>Penyandang Disabilitas</strong>
                            <small>Calon penerima memiliki kondisi disabilitas.</small>
                        </span>
                    </label>

                    <label class="recipient-checkbox-card">
                        <input type="hidden" name="lansia" value="0">
                        <input
                            type="checkbox"
                            name="lansia"
                            value="1"
                            @checked((string) old('lansia', (int) ($penerima->lansia ?? 0)) === '1')
                        >
                        <span class="recipient-checkbox-content">
                            <strong>Lansia</strong>
                            <small>Calon penerima termasuk kelompok lanjut usia.</small>
                        </span>
                    </label>

                </div>
            </div>
        </div>

        {{-- DOKUMEN PENDUKUNG --}}
        <div class="recipient-form-card recipient-document-card">

            <div class="recipient-form-section-heading">
                <div class="recipient-form-section-icon">04</div>
                <div>
                    <h3>Dokumen Pendukung</h3>
                    <p>Unggah seluruh dokumen yang diperlukan untuk pengajuan bantuan.</p>
                </div>
            </div>

            <div class="recipient-document-grid">

                @php
                    $documents = [
                        [
                            'id' => 'dokumen_ktp',
                            'name' => 'dokumen_ktp',
                            'title' => 'Foto KTP',
                            'description' => 'JPG atau PNG · Maksimal 2 MB',
                            'accept' => '.jpg,.jpeg,.png',
                        ],
                        [
                            'id' => 'dokumen_kk',
                            'name' => 'dokumen_kk',
                            'title' => 'Kartu Keluarga (KK)',
                            'description' => 'JPG, PNG, atau PDF · Maksimal 2 MB',
                            'accept' => '.jpg,.jpeg,.png,.pdf',
                        ],
                        [
                            'id' => 'dokumen_dtks_p3ke',
                            'name' => 'dokumen_dtks_p3ke',
                            'title' => 'Bukti DTKS / P3KE',
                            'description' => 'JPG, PNG, atau PDF · Maksimal 2 MB',
                            'accept' => '.jpg,.jpeg,.png,.pdf',
                        ],
                        [
                            'id' => 'foto_rumah_depan',
                            'name' => 'foto_rumah_depan',
                            'title' => 'Foto Rumah Tampak Depan',
                            'description' => 'JPG atau PNG · Maksimal 2 MB',
                            'accept' => '.jpg,.jpeg,.png',
                        ],
                        [
                            'id' => 'foto_rumah_dalam',
                            'name' => 'foto_rumah_dalam',
                            'title' => 'Foto Bagian Dalam Rumah',
                            'description' => 'JPG atau PNG · Maksimal 2 MB',
                            'accept' => '.jpg,.jpeg,.png',
                        ],
                        [
                            'id' => 'dokumen_sktm',
                            'name' => 'dokumen_sktm',
                            'title' => 'Surat Keterangan Tidak Mampu (SKTM)',
                            'description' => 'JPG, PNG, atau PDF · Maksimal 2 MB',
                            'accept' => '.jpg,.jpeg,.png,.pdf',
                        ],
                    ];
                @endphp

                @foreach ($documents as $document)
                    <div class="recipient-document-upload">
                        <label for="{{ $document['id'] }}" class="recipient-upload-area">
                            <span class="recipient-upload-icon">↑</span>
                            <strong>{{ $document['title'] }}</strong>
                            <span class="recipient-upload-description">
                                {{ $document['description'] }}
                            </span>
                            <span class="recipient-upload-button">Pilih File</span>

                            <input
                                type="file"
                                id="{{ $document['id'] }}"
                                name="{{ $document['name'] }}"
                                accept="{{ $document['accept'] }}"
                                required
                            >
                        </label>

                        <small
                            id="nama-{{ $document['id'] }}"
                            class="recipient-file-status"
                        >Belum ada file dipilih.</small>

                        @error($document['name'])
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </div>
                @endforeach

            </div>

            <p class="recipient-form-hint">
                Pastikan seluruh dokumen terbaca dengan jelas. Bukti DTKS/P3KE
                digunakan untuk mendukung pemeriksaan status kesejahteraan,
                sedangkan foto rumah dan SKTM digunakan untuk asesmen kondisi
                tempat tinggal dan ekonomi calon penerima.
            </p>

        </div>

        {{-- TOMBOL --}}
        <div class="recipient-form-actions">
            <a href="{{ route('masyarakat.status') }}" class="recipient-form-cancel">
                Batal
            </a>

            <button type="submit" class="recipient-form-submit">
                Kirim Pengajuan
            </button>
        </div>

    </form>
</div>

<style>
/* =========================================
   RECIPIENT FORM PAGE
========================================= */

.recipient-form-page {
    width: 100%;
    max-width: 1180px;
    margin: 0 auto;
    color: #20391a;
}

/* =========================================
   FORM CARD
========================================= */

.recipient-form-card {
    background: #fff;
    border: 1px solid #e5eadd;
    border-radius: 14px;
    padding: 24px;
    margin-bottom: 18px;
}

/* =========================================
   FORM GRID
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
   FORM LABEL
========================================= */

.recipient-field label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 600;
}

.recipient-field label span {
    color: #20391a;
}

/* =========================================
   FORM INPUT
========================================= */

.recipient-field input:not([type="checkbox"]):not([type="radio"]),
.recipient-field select,
.recipient-field textarea {
    width: 100%;
    min-height: 43px;
    border: 1px solid #dce3d6;
    border-radius: 8px;
    padding: 10px 12px;
    background: #fff;
    color: #20391a;
    font: inherit;
    box-sizing: border-box;
}

.recipient-field textarea {
    resize: vertical;
}

.recipient-field input:focus,
.recipient-field select:focus,
.recipient-field textarea:focus {
    outline: none;
    border-color: #174f37;
    box-shadow: 0 0 0 3px rgba(23, 79, 55, .10);
}

/* =========================================
   FORM HINT & ERROR
========================================= */

.recipient-field > small,
.recipient-form-hint {
    display: block;
    margin-top: 7px;
    color: #7b8875;
    font-size: 12px;
}

.recipient-field .field-error,
.field-error {
    color: #dc2626;
}

/* =========================================
   PENGHASILAN BULANAN
========================================= */

.recipient-input-prefix {
    display: flex;
    align-items: center;
    height: 43px;
    min-height: 43px;
    box-sizing: border-box;
}

.recipient-input-prefix span {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    padding: 0 13px;
    background: transparent;
    color: #64748b;
    border-right: 1px solid #e2e8f0;
    font-weight: 500;
}

.recipient-input-prefix input {
    height: 100%;
    min-height: 0;
    margin-top: auto;
    padding: 12px;
    line-height: normal;
    box-sizing: border-box;
    border: none !important;
    outline: none;
    background: transparent;
}

.recipient-input-prefix:focus-within {
    border-color: inherit;
    box-shadow: none;
    outline: none;
}

.recipient-input-prefix input:focus {
    border: none !important;
    box-shadow: none !important;
    outline: none !important;
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
   DOKUMEN PENDUKUNG
========================================= */

.recipient-document-card {
    padding: 24px;
}

.recipient-document-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.recipient-document-upload {
    min-width: 0;
}

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

/* Ikon upload */

.recipient-upload-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;
    background: #ffffff;
    color: #174f37;
}

.recipient-upload-icon svg {
    width: 19px;
    height: 19px;
}

/* Teks upload */

.recipient-upload-area strong {
    color: #20391a;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.4;
}

.recipient-upload-area small {
    color: #6f7e69;
    font-size: 10px;
    line-height: 1.4;
}

/* Nama file */

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

/* Error upload */

.recipient-document-upload .field-error {
    display: block;
    margin-top: 6px;
    color: #dc2626;
    font-size: 12px;
}

/* Keterangan dokumen */

.recipient-document-card .recipient-form-hint {
    margin-top: 12px;
    line-height: 1.5;
}

/* Responsive */

@media (max-width: 700px) {
    .recipient-document-grid {
        grid-template-columns: 1fr;
    }

    .recipient-upload-area {
        min-height: 140px;
    }
}

/* =========================================
   FORM ACTIONS
========================================= */

.recipient-form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    margin: 20px 0 30px;
}

/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 700px) {

    .recipient-form-card {
        padding: 17px;
    }

    .recipient-form-grid {
        grid-template-columns: 1fr;
    }

    .recipient-field.full {
        grid-column: auto;
    }

    .recipient-house-options {
        flex-direction: column;
    }

    .recipient-document-grid {
        grid-template-columns: 1fr;
    }

    .recipient-form-actions {
        flex-direction: column-reverse;
    }

    .recipient-form-actions > * {
        width: 100%;
        text-align: center;
        box-sizing: border-box;
    }

}
</style>


<script>
document.addEventListener('DOMContentLoaded', function () {

    // =====================================
    // FILTER RT BERDASARKAN RW
    // =====================================

    const rwSelect = document.getElementById('id_rw');
    const rtSelect = document.getElementById('id_wilayah');

    function filterRT() {
        const selectedRW = rwSelect.value;
        let selectedStillValid = false;

        Array.from(rtSelect.options).forEach(function (option, index) {
            if (index === 0) return;

            const match = option.dataset.rw === selectedRW;

            option.hidden = !match;

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


    // =====================================
    // MENAMPILKAN NAMA FILE
    // =====================================

    function showFileName(inputId, outputId) {
        const input = document.getElementById(inputId);
        const output = document.getElementById(outputId);

        if (!input || !output) return;

        input.addEventListener('change', function () {

            if (this.files && this.files.length > 0) {
                output.textContent = '✓ ' + this.files[0].name;

                output.classList.add(
                    'recipient-file-existing'
                );

            } else {
                output.textContent = 'Belum ada file dipilih.';

                output.classList.remove(
                    'recipient-file-existing'
                );
            }
        });
    }

    showFileName('dokumen_ktp', 'nama-dokumen_ktp');
    showFileName('dokumen_kk', 'nama-dokumen_kk');
    showFileName('dokumen_dtks_p3ke', 'nama-dokumen_dtks_p3ke');
    showFileName('foto_rumah_depan', 'nama-foto_rumah_depan');
    showFileName('foto_rumah_dalam', 'nama-foto_rumah_dalam');
    showFileName('dokumen_sktm', 'nama-dokumen_sktm');

});
</script>

@endsection
