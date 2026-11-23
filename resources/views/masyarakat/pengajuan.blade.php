@extends('layouts.masyarakat')

@section('title', 'Pengajuan Bantuan')
@section('heading', 'Formulir Pengajuan Bantuan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pengajuan-masyarakat.css') }}">
@endpush

@section('content')

@php
    $wilayahRows = collect($wilayah);
    $rtList = $wilayahRows->where('tipe', 'rt');
    $rwList = $wilayahRows->where('tipe', 'rw');

    $selectedRtId = old('id_wilayah', $penerima->id_wilayah ?? '');
    $selectedRt = $rtList->firstWhere('id_wilayah', (int) $selectedRtId);
    $selectedRwId = old('id_rw', $selectedRt->id_induk ?? '');

    $sudahTerdaftar = isset($penerima) && $penerima !== null;

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

<div class="recipient-form-page">

    <div class="recipient-form-heading">
        <div>
            <span class="recipient-eyebrow">LAYANAN MASYARAKAT</span>
            <h2>Ajukan Bantuan Sosial</h2>
            <p>Lengkapi informasi berikut dengan benar agar pengajuan dapat diperiksa oleh petugas.</p>
        </div>
    </div>

    {{-- INDIKATOR TAHAPAN --}}
    <div class="recipient-stepper">
        <div class="recipient-step recipient-step-active">
            <span class="recipient-step-number">1</span>
            <span>Identitas &amp; alamat</span>
        </div>
        <span class="recipient-step-line"></span>
        <div class="recipient-step">
            <span class="recipient-step-number">2</span>
            <span>Kondisi ekonomi</span>
        </div>
        <span class="recipient-step-line"></span>
        <div class="recipient-step">
            <span class="recipient-step-number">3</span>
            <span>Dokumen</span>
        </div>
        <span class="recipient-step-line"></span>
        <div class="recipient-step">
            <span class="recipient-step-number">4</span>
            <span>Tinjau &amp; kirim</span>
        </div>
    </div>

    @if ($errors->any())
        <div class="recipient-form-error" role="alert">
            <strong>Periksa kembali data yang Anda masukkan.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        id="recipientApplicationForm"
        method="POST"
        enctype="multipart/form-data"
        action="{{ route('masyarakat.pengajuan.store') }}"
    >
        @csrf

        <div class="recipient-form-layout">

            {{-- KOLOM UTAMA --}}
            <div class="recipient-form-main">

                {{-- IDENTITAS DAN ALAMAT --}}
                <section class="recipient-form-card" id="bagian-identitas">

                    <div class="recipient-form-section-heading">
                        <span class="recipient-section-number">01</span>
                        <div>
                            <h3>Identitas &amp; Alamat</h3>
                            <p>Pastikan identitas dan alamat tempat tinggal sesuai dengan dokumen resmi.</p>
                        </div>
                    </div>

                    @if ($sudahTerdaftar)
                        <div class="recipient-info-notice">
                            <span class="recipient-notice-icon">i</span>
                            <p>Identitas dasar menggunakan data akun yang sudah terdaftar. Untuk memperbaiki NIK, KK, nama, tanggal lahir, atau jenis kelamin, gunakan fitur Perbaikan Data.</p>
                        </div>
                    @endif

                    <div class="recipient-form-grid">

                        <div class="recipient-field">
                            <label for="nik">NIK <span>*</span></label>
                            <input
                                type="text"
                                id="nik"
                                name="nik"
                                class="{{ $sudahTerdaftar ? 'recipient-input-locked' : '' }}"
                                tabindex="{{ $sudahTerdaftar ? '-1' : '0' }}"
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
                            @endif
                            @error('nik') <small class="field-error">{{ $message }}</small> @enderror
                        </div>

                        <div class="recipient-field">
                            <label for="no_kk">Nomor Kartu Keluarga (KK) <span>*</span></label>
                            <input
                                type="text"
                                id="no_kk"
                                name="no_kk"
                                class="{{ $sudahTerdaftar ? 'recipient-input-locked' : '' }}"
                                tabindex="{{ $sudahTerdaftar ? '-1' : '0' }}"
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
                            <label for="nama">Nama Lengkap <span>*</span></label>
                            <input
                                type="text"
                                id="nama"
                                name="nama"
                                class="{{ $sudahTerdaftar ? 'recipient-input-locked' : '' }}"
                                value="{{ old('nama', $penerima->nama ?? '') }}"
                                placeholder="Masukkan nama lengkap"
                                tabindex="{{ $sudahTerdaftar ? '-1' : '0' }}"
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
                                class="{{ $sudahTerdaftar ? 'recipient-input-locked' : '' }}"
                                value="{{ old('tanggal_lahir', isset($penerima->tanggal_lahir) ? \Illuminate\Support\Carbon::parse($penerima->tanggal_lahir)->format('Y-m-d') : '') }}"
                                tabindex="{{ $sudahTerdaftar ? '-1' : '0' }}"
                                {{ $sudahTerdaftar ? 'readonly' : 'required' }}
                            >
                            @error('tanggal_lahir') <small class="field-error">{{ $message }}</small> @enderror
                        </div>

                        <div class="recipient-field">
                            <label for="jenis_kelamin">Jenis Kelamin <span>*</span></label>
                            <select
                                id="jenis_kelamin"
                                name="jenis_kelamin"
                                class="{{ $sudahTerdaftar ? 'recipient-input-locked' : '' }}"
                                tabindex="{{ $sudahTerdaftar ? '-1' : '0' }}"
                                {{ $sudahTerdaftar ? 'disabled' : 'required' }}
                            >
                                <option value="">Pilih Jenis Kelamin</option>
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
                            @error('nomor_whatsapp') <small class="field-error">{{ $message }}</small> @enderror
                        </div>

                        <div class="recipient-field recipient-field-full">
                            <label for="alamat">Alamat Lengkap <span>*</span></label>
                            <textarea
                                id="alamat"
                                name="alamat"
                                class="{{ $sudahTerdaftar ? 'recipient-input-locked' : '' }}"
                                rows="3"
                                placeholder="Masukkan nama jalan, nomor rumah, dan keterangan alamat"
                                tabindex="{{ $sudahTerdaftar ? '-1' : '0' }}"
                                {{ $sudahTerdaftar ? 'readonly' : 'required' }}
                                @if ($sudahTerdaftar) onfocus="this.blur()" @endif
                            >{{ old('alamat', $penerima->alamat ?? '') }}</textarea>
                            @error('alamat') <small class="field-error">{{ $message }}</small> @enderror
                        </div>

                        <div class="recipient-field">
                            <label for="id_rw">RW <span>*</span></label>
                            <select
                                id="id_rw"
                                @if (!$sudahTerdaftar)
                                    name="id_rw"
                                    required
                                @else
                                    disabled
                                    class="recipient-input-locked"
                                    tabindex="-1"
                                @endif
                            >
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

                            @if ($sudahTerdaftar)
                                <input type="hidden" name="id_rw" value="{{ $selectedRwId }}">
                            @endif

                            @error('id_rw')
                                <small class="field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="recipient-field">
                            <label for="id_wilayah">RT <span>*</span></label>
                            <select
                                id="id_wilayah"
                                @if (!$sudahTerdaftar)
                                    name="id_wilayah"
                                    required
                                @else
                                    disabled
                                    class="recipient-input-locked"
                                    tabindex="-1"
                                @endif
                            >
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

                            @if ($sudahTerdaftar)
                                <input type="hidden" name="id_wilayah" value="{{ $selectedRtId }}">
                            @endif

                            @error('id_wilayah')
                                <small class="field-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- KONDISI EKONOMI --}}
                <section class="recipient-form-card" id="bagian-ekonomi">

                    <div class="recipient-form-section-heading">
                        <span class="recipient-section-number">02</span>
                        <div>
                            <h3>Kondisi Ekonomi</h3>
                            <p>Berikan gambaran kondisi ekonomi dan keadaan tempat tinggal saat ini.</p>
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
                                    class="{{ $sudahTerdaftar ? 'recipient-input-locked' : '' }}"
                                    min="0"
                                    step="1000"
                                    value="{{ old('penghasilan_bulanan', $penerima->penghasilan_bulanan ?? '') }}"
                                    placeholder="Contoh: 1500000"
                                    tabindex="{{ $sudahTerdaftar ? '-1' : '0' }}"
                                    {{ $sudahTerdaftar ? 'readonly' : 'required' }}
                                >
                            </div>
                            @error('penghasilan_bulanan')
                                <small class="field-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="recipient-field">
                            <label for="jumlah_tanggungan">Jumlah Tanggungan <span>*</span></label>
                            <input
                                type="number"
                                id="jumlah_tanggungan"
                                name="jumlah_tanggungan"
                                class="{{ $sudahTerdaftar ? 'recipient-input-locked' : '' }}"
                                min="0"
                                max="99"
                                value="{{ old('jumlah_tanggungan', $penerima->jumlah_tanggungan ?? 0) }}"
                                tabindex="{{ $sudahTerdaftar ? '-1' : '0' }}"
                                {{ $sudahTerdaftar ? 'readonly' : 'required' }}
                            >
                            @error('jumlah_tanggungan')
                                <small class="field-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="recipient-field recipient-house-field">
                        <label>Kondisi Rumah <span>*</span></label>

                        <div class="recipient-house-options">
                            @foreach ([
                                'permanen' => ['Permanen', 'Bangunan rumah permanen'],
                                'semi_permanen' => ['Semi Permanen', 'Bangunan sebagian permanen'],
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
                        <p>Pilih jika calon penerima termasuk dalam salah satu kondisi berikut.</p>

                        <div class="recipient-checkbox-grid">
                            <label class="recipient-checkbox-card">
                                <input type="hidden" name="disabilitas" value="0">
                                <input
                                    type="checkbox"
                                    name="disabilitas"
                                    value="1"
                                    @checked((string) old('disabilitas', (int) ($penerima->disabilitas ?? 0)) === '1')
                                >
                                <span>
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
                                <span>
                                    <strong>Lansia</strong>
                                    <small>Calon penerima termasuk kelompok lanjut usia.</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </section>

                {{-- DOKUMEN --}}
                <section class="recipient-form-card" id="bagian-dokumen">

                    <div class="recipient-form-section-heading">
                        <span class="recipient-section-number">03</span>
                        <div>
                            <h3>Dokumen Pendukung</h3>
                            <p>Unggah dokumen yang diperlukan. Pastikan foto atau hasil pindai terbaca dengan jelas.</p>
                        </div>
                    </div>

                    <div class="recipient-document-grid">
                        @foreach ($documents as $document)
                            <div class="recipient-document-upload">
                                <label for="{{ $document['id'] }}" class="recipient-upload-area">
                                    <span class="recipient-upload-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none">
                                            <path d="M12 16V4M7.5 8.5L12 4L16.5 8.5M4 16.5V19C4 19.55 4.45 20 5 20H19C19.55 20 20 19.55 20 19V16.5"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                    <strong>{{ $document['title'] }}</strong>
                                    <span class="recipient-upload-description">{{ $document['description'] }}</span>
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

                    <div class="recipient-form-hint">
                        Bukti DTKS/P3KE, foto rumah, dan SKTM digunakan sebagai dokumen pendukung pemeriksaan kelayakan calon penerima.
                    </div>
                </section>

                {{-- TOMBOL AKSI --}}
                <div class="recipient-form-actions">
                    <a href="{{ route('masyarakat.status') }}" class="recipient-form-cancel">
                        Batal
                    </a>

                    <button type="submit" class="recipient-form-submit">
                        Kirim Pengajuan
                        <span aria-hidden="true">→</span>
                    </button>
                </div>

            </div>

            {{-- PANEL INFORMASI SAMPING --}}
            <aside class="recipient-form-sidebar">

                <div class="recipient-sidebar-status">
                    <span class="recipient-sidebar-status-icon" aria-hidden="true">✓</span>
                    <h3>Setelah dikirim</h3>
                    <p>Pengajuan akan masuk ke antrean pemeriksaan petugas. Anda dapat memantau perkembangan melalui menu Status Pengajuan.</p>
                    <a href="{{ route('masyarakat.status') }}">Lihat Status Pengajuan <span aria-hidden="true">→</span></a>
                </div>

                <div class="recipient-sidebar-card">
                    <div class="recipient-sidebar-card-heading">
                        <h3>Kelengkapan Formulir</h3>
                        <span id="recipientCompletionCount">0/5</span>
                    </div>

                    <div class="recipient-completion-track">
                        <span id="recipientCompletionBar"></span>
                    </div>

                    <p class="recipient-completion-caption">Periksa kembali bagian-bagian berikut sebelum mengirim pengajuan.</p>

                    <ul class="recipient-checklist">
                        <li data-check="identity">
                            <span class="recipient-check-icon">✓</span>
                            <span>Identitas</span>
                            <small>Belum lengkap</small>
                        </li>
                        <li data-check="address">
                            <span class="recipient-check-icon">✓</span>
                            <span>Alamat</span>
                            <small>Belum lengkap</small>
                        </li>
                        <li data-check="economy">
                            <span class="recipient-check-icon">✓</span>
                            <span>Kondisi ekonomi</span>
                            <small>Belum lengkap</small>
                        </li>
                        <li data-check="ktp">
                            <span class="recipient-check-icon">✓</span>
                            <span>Foto KTP</span>
                            <small>Belum dipilih</small>
                        </li>
                        <li data-check="kk">
                            <span class="recipient-check-icon">✓</span>
                            <span>Kartu Keluarga</span>
                            <small>Belum dipilih</small>
                        </li>
                    </ul>
                </div>

                <div class="recipient-sidebar-privacy">
                    <span class="recipient-privacy-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 3L19 6V11.5C19 16 16.1 19.5 12 21C7.9 19.5 5 16 5 11.5V6L12 3Z"
                                stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                            <path d="M9 12L11 14L15 10"
                                stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <div>
                        <h3>Privasi Data Anda</h3>
                        <p>Informasi dan dokumen yang dikirim digunakan untuk keperluan pendataan serta pemeriksaan pengajuan bantuan sosial.</p>
                    </div>
                </div>

            </aside>

        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rwSelect = document.getElementById('id_rw');
    const rtSelect = document.getElementById('id_wilayah');

    // Filter pilihan RT berdasarkan RW.
    function filterRT() {
        if (!rwSelect || !rtSelect) return;

        const selectedRW = rwSelect.value;
        let selectedStillValid = false;

        Array.from(rtSelect.options).forEach(function (option, index) {
            if (index === 0) return;

            const match = option.dataset.rw === selectedRW;
            option.disabled = !match;
            option.hidden = !match;

            if (match && option.selected) {
                selectedStillValid = true;
            }
        });

        if (!selectedStillValid) {
            rtSelect.value = '';
        }
    }

    if (rwSelect && rtSelect) {
        rwSelect.addEventListener('change', filterRT);
        filterRT();
    }

    // Tampilkan nama file setelah dipilih.
    document.querySelectorAll('.recipient-document-upload input[type="file"]').forEach(function (input) {
        input.addEventListener('change', function () {
            const output = document.getElementById('nama-' + this.id);
            if (!output) return;

            if (this.files && this.files.length > 0) {
                output.textContent = '✓ ' + this.files[0].name;
                output.classList.add('recipient-file-selected');
            } else {
                output.textContent = 'Belum ada file dipilih.';
                output.classList.remove('recipient-file-selected');
            }

            updateCompletion();
        });
    });

    // Checklist kelengkapan formulir.
    const form = document.getElementById('recipientApplicationForm');

    function fieldHasValue(id) {
        const field = document.getElementById(id);
        return !!field && String(field.value).trim() !== '';
    }

    function setChecklistItem(key, complete, completeText) {
        const item = document.querySelector('[data-check="' + key + '"]');
        if (!item) return;

        item.classList.toggle('is-complete', complete);

        const status = item.querySelector('small');
        if (status) {
            status.textContent = complete ? completeText : 'Belum lengkap';
        }
    }

    function updateCompletion() {
        if (!form) return;

        const identityComplete = fieldHasValue('nomor_whatsapp');

        const addressComplete = fieldHasValue('alamat')
            && fieldHasValue('id_rw')
            && fieldHasValue('id_wilayah');

        const houseSelected = form.querySelector('input[name="kondisi_rumah"]:checked');

        const economyComplete = fieldHasValue('penghasilan_bulanan')
            && fieldHasValue('jumlah_tanggungan')
            && !!houseSelected;

        const ktpComplete = fieldHasValue('dokumen_ktp') && document.getElementById('dokumen_ktp').files.length > 0;
        const kkComplete = fieldHasValue('dokumen_kk') && document.getElementById('dokumen_kk').files.length > 0;

        const checks = [
            ['identity', identityComplete, 'Lengkap'],
            ['address', addressComplete, 'Lengkap'],
            ['economy', economyComplete, 'Lengkap'],
            ['ktp', ktpComplete, 'File dipilih'],
            ['kk', kkComplete, 'File dipilih']
        ];

        checks.forEach(function (item) {
            setChecklistItem(item[0], item[1], item[2]);
        });

        const completed = checks.filter(function (item) {
            return item[1];
        }).length;

        const count = document.getElementById('recipientCompletionCount');
        const bar = document.getElementById('recipientCompletionBar');

        if (count) count.textContent = completed + '/5';
        if (bar) bar.style.width = (completed / 5 * 100) + '%';
    }

    if (form) {
        form.addEventListener('input', updateCompletion);
        form.addEventListener('change', updateCompletion);
        updateCompletion();
    }
});
</script>

@endsection