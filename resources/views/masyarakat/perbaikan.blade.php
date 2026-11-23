@extends('layouts.masyarakat')

@section('title', 'Perbaikan Data')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/perbaikan-masyarakat.css') }}">
@endpush

@section('content')

    <x-page-header title="Pengajuan Perbaikan Data"
        description="Ajukan perubahan apabila terdapat informasi pada data Anda yang sudah tidak sesuai." />

    @if (!$penerima)

        <div class="panel">
            <div class="empty">
                <h3>Data penerima belum tersedia</h3>
                <p>Anda perlu mengajukan pendataan bantuan terlebih dahulu.</p>
                <a href="{{ route('masyarakat.pengajuan.create') }}" class="btn primary">
                    Ajukan Bantuan
                </a>
            </div>
        </div>

    @else

        <div class="perbaikan-page">
            <div class="perbaikan-layout">
                <div class="perbaikan-main">

                    <div class="panel">
                        <h3>Data Anda Saat Ini</h3>
                        <p class="muted">
                            Periksa data berikut sebelum mengajukan perubahan.
                        </p>

                        <div class="detail-grid">
                            <div>
                                <small>Nama Lengkap</small>
                                <b>{{ $penerima->nama }}</b>
                            </div>

                            <div>
                                <small>Alamat Saat Ini</small>
                                <b>{{ $penerima->alamat }}</b>
                            </div>

                            <div>
                                <small>Penghasilan Bulanan</small>
                                <b>Rp {{ number_format((float) $penerima->penghasilan_bulanan, 0, ',', '.') }}</b>
                            </div>

                            <div>
                                <small>Jumlah Tanggungan</small>
                                <b>{{ $penerima->jumlah_tanggungan }} orang</b>
                            </div>

                            <div>
                                <small>Status Pendataan</small>
                                <span class="status {{ $penerima->status }}">
                                    {{ ucfirst($penerima->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="panel form-panel">
                        <div class="panel-head">
                            <div>
                                <h3>Formulir Perubahan Data</h3>
                                <p>Isi hanya bagian yang ingin Anda ubah. Bagian kosong tidak akan diubah.</p>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert danger">
                                <strong>Pengajuan belum dapat dikirim.</strong>
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('masyarakat.perbaikan.store') }}" enctype="multipart/form-data">
                            @csrf

                            
                            <div class="form-grid">

                                {{-- Identitas penerima --}}
                                <div>
                                    <label for="nama">Nama Lengkap</label>
                                    <input type="text" id="nama" name="nama"
                                        value="{{ old('nama', $penerima->nama) }}"
                                        maxlength="100">
                                </div>

                                <div>
                                    <label for="nik">NIK</label>
                                    <input type="text" id="nik" name="nik"
                                        value="{{ old('nik', $penerima->nik_decrypted) }}"
                                        inputmode="numeric" maxlength="16" minlength="16"
                                        pattern="[0-9]{16}">
                                </div>

                                <div>
                                    <label for="no_kk">Nomor KK</label>
                                    <input type="text" id="no_kk" name="no_kk"
                                        value="{{ old('no_kk', $penerima->no_kk_decrypted) }}"
                                        inputmode="numeric" maxlength="16" minlength="16"
                                        pattern="[0-9]{16}">
                                </div>

                                <div>
                                    <label for="tanggal_lahir">Tanggal Lahir</label>
                                    <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                                        value="{{ old('tanggal_lahir', $penerima->tanggal_lahir?->format('Y-m-d')) }}"
                                        >
                                </div>

                                <div>
                                    <label for="jenis_kelamin">Jenis Kelamin</label>
                                    <select id="jenis_kelamin" name="jenis_kelamin">
                                        <option value="">Pilih jenis kelamin</option>
                                        <option value="L" @selected(old('jenis_kelamin', $penerima->jenis_kelamin) === 'L')>
                                            Laki-laki
                                        </option>
                                        <option value="P" @selected(old('jenis_kelamin', $penerima->jenis_kelamin) === 'P')>
                                            Perempuan
                                        </option>
                                    </select>
                                </div>

                                {{-- Wilayah --}}
                                <div>
                                    <label for="id_rw">RW</label>
                                    <select id="id_rw" name="id_rw">
                                        <option value="">Pilih RW</option>
                                        @foreach ($rwList as $rw)
                                            <option value="{{ $rw->id_wilayah }}"
                                                @selected((string) old('id_rw', $penerima->wilayah?->id_induk) === (string) $rw->id_wilayah)>
                                                {{ $rw->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="id_wilayah">RT</label>
                                    <select id="id_wilayah" name="id_wilayah">
                                        <option value="">Pilih RT</option>
                                        @foreach ($rtList as $rt)
                                            <option value="{{ $rt->id_wilayah }}"
                                                data-rw="{{ $rt->id_induk }}"
                                                @selected((string) old('id_wilayah', $penerima->id_wilayah) === (string) $rt->id_wilayah)>
                                                {{ $rt->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Data tambahan --}}
                                <div class="span-2">
                                    <label for="alamat">Alamat</label>
                                    <textarea id="alamat" name="alamat"
                                        placeholder="Masukkan alamat terbaru jika ada perubahan">{{ old('alamat', $penerima->alamat) }}</textarea>
                                </div>

                                <div>
                                    <label for="penghasilan_bulanan">Penghasilan Bulanan (Rp)</label>
                                    <input type="number" id="penghasilan_bulanan"
                                        name="penghasilan_bulanan" min="0" step="1000"
                                        value="{{ old('penghasilan_bulanan', $penerima->penghasilan_bulanan) }}"
                                        placeholder="Contoh: 2000000">
                                </div>

                                <div>
                                    <label for="jumlah_tanggungan">Jumlah Tanggungan</label>
                                    <input type="number" id="jumlah_tanggungan"
                                        name="jumlah_tanggungan" min="0" max="99"
                                        value="{{ old('jumlah_tanggungan', $penerima->jumlah_tanggungan) }}"
                                        placeholder="Contoh: 3">
                                </div>

                                {{-- Alasan dan dokumen --}}
                                <div class="span-2">
                                    <label for="alasan">Alasan Perbaikan Data *</label>
                                    <textarea id="alasan" name="alasan" rows="4"
                                        maxlength="2000" required
                                        placeholder="Jelaskan alasan perubahan data yang Anda ajukan...">{{ old('alasan') }}</textarea>
                                    <small class="sub">
                                        Jelaskan kondisi atau perubahan yang sebenarnya agar petugas dapat melakukan verifikasi.
                                    </small>
                                </div>

                                <div class="span-2">
                                    <label for="dokumen_surat_rt">
                                        Surat Keterangan RT <span class="muted">(Opsional)</span>
                                    </label>
                                    <input type="file" id="dokumen_surat_rt"
                                        name="dokumen_surat_rt" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="sub">
                                        Unggah jika tersedia sebagai bukti pendukung.
                                        Format PDF, JPG, JPEG, atau PNG. Maksimal 2 MB.
                                    </small>
                                </div>

                                <div class="span-2">
                                    <label for="dokumen_berhenti_kerja">
                                        Surat Keterangan Berhenti Kerja
                                        <span class="muted">(Opsional)</span>
                                    </label>
                                    <input type="file" id="dokumen_berhenti_kerja"
                                        name="dokumen_berhenti_kerja" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="sub">
                                        Unggah jika perubahan penghasilan disebabkan berhenti bekerja.
                                        Format PDF, JPG, JPEG, atau PNG. Maksimal 2 MB.
                                    </small>
                                </div>

                            </div>

                            <div class="alert" style="background:#fff8e8;color:#85651b;">
                                <strong>Perhatian:</strong>
                                Perubahan tidak langsung diterapkan pada data Anda.
                                Admin akan memeriksa dan menyetujui pengajuan terlebih dahulu.
                            </div>

                            <div class="form-actions">
                                <a href="{{ route('dashboard') }}" class="btn outline">
                                    Batal
                                </a>

                                <button type="submit" class="btn primary">
                                    Kirim Pengajuan Perbaikan
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="panel">
                        <div class="panel-head">
                            <div>
                                <h3>Riwayat Pengajuan Perbaikan</h3>
                                <p>Pantau perkembangan permintaan perubahan data Anda.</p>
                            </div>
                        </div>

                        @forelse ($items as $item)

                            <div style="padding:16px 0;border-top:1px solid var(--line);">

                                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                                    <div>
                                        <strong>Pengajuan #{{ $item->id_pengajuan }}</strong>
                                        <small class="sub">
                                            {{ $item->created_at->format('d/m/Y H:i') }}
                                        </small>
                                    </div>

                                    <span class="status {{ $item->status }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </div>

                                                                
                                @php
                                    $nikSaatIni = $penerima->nik_decrypted ?? '';
                                    $kkSaatIni = $penerima->no_kk_decrypted ?? '';

                                    $nikTersamar = $nikSaatIni
                                        ? str_repeat('*', max(0, strlen($nikSaatIni) - 4)) . substr($nikSaatIni, -4)
                                        : '-';

                                    $kkTersamar = $kkSaatIni
                                        ? str_repeat('*', max(0, strlen($kkSaatIni) - 4)) . substr($kkSaatIni, -4)
                                        : '-';
                                @endphp

                                @php
                                    $nikSaatIni = $penerima->nik_decrypted ?? '';
                                    $kkSaatIni = $penerima->no_kk_decrypted ?? '';

                                    $samarkanNomor = function ($nomor) {
                                        if (!$nomor) {
                                            return '-';
                                        }

                                        return str_repeat('*', max(0, strlen($nomor) - 4))
                                            . substr($nomor, -4);
                                    };

                                    $rwSaatIni = $penerima->wilayah?->induk?->nama
                                        ? str_ireplace('RW ', '', $penerima->wilayah->induk->nama)
                                        : '-';

                                    $rtSaatIni = $penerima->wilayah?->nama
                                        ? str_ireplace('RT ', '', $penerima->wilayah->nama)
                                        : '-';
                                @endphp

                                <div class="detail-grid">
                                    <div>
                                        <small>Nama Lengkap</small>
                                        <b>{{ $penerima->nama }}</b>
                                    </div>

                                    <div>
                                        <small>NIK</small>
                                        <b>{{ $samarkanNomor($nikSaatIni) }}</b>
                                    </div>

                                    <div>
                                        <small>Nomor KK</small>
                                        <b>{{ $samarkanNomor($kkSaatIni) }}</b>
                                    </div>

                                    <div>
                                        <small>Tanggal Lahir</small>
                                        <b>
                                            {{ $penerima->tanggal_lahir
                                                ? \Carbon\Carbon::parse($penerima->tanggal_lahir)->format('d-m-Y')
                                                : '-' }}
                                        </b>
                                    </div>

                                    <div>
                                        <small>Jenis Kelamin</small>
                                        <b>
                                            {{ $penerima->jenis_kelamin === 'L'
                                                ? 'Laki-laki'
                                                : ($penerima->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}
                                        </b>
                                    </div>

                                    <div>
                                        <small>RW</small>
                                        <b>{{ $rwSaatIni }}</b>
                                    </div>

                                    <div>
                                        <small>RT</small>
                                        <b>{{ $rtSaatIni }}</b>
                                    </div>

                                    <div>
                                        <small>Alamat Saat Ini</small>
                                        <b>{{ $penerima->alamat ?: '-' }}</b>
                                    </div>

                                    <div>
                                        <small>Penghasilan Bulanan</small>
                                        <b>Rp {{ number_format((float) $penerima->penghasilan_bulanan, 0, ',', '.') }}</b>
                                    </div>

                                    <div>
                                        <small>Jumlah Tanggungan</small>
                                        <b>{{ $penerima->jumlah_tanggungan }} orang</b>
                                    </div>

                                    <div>
                                        <small>Status Pendataan</small>
                                        <span class="status {{ $penerima->status }}">
                                            {{ ucfirst($penerima->status) }}
                                        </span>
                                    </div>
                                </div>

                                @if ($item->catatan_admin)
                                    <div class="alert" style="background:#f3f6f4;color:#52635c;">
                                        <strong>Catatan Admin:</strong>
                                        {{ $item->catatan_admin }}
                                    </div>
                                @endif

                            </div>

                        @empty
                            <div class="empty">
                                Anda belum pernah mengajukan perubahan data.
                            </div>
                        @endforelse
                    </div>

                    <aside class="perbaikan-sidebar">
                        <div class="panel">
                            <h3>Informasi Perbaikan Data</h3>
                            <p class="muted">
                                Periksa kembali usulan perubahan sebelum dikirim.
                            </p>

                            <div class="perbaikan-info">
                                <div>
                                    <strong>Data identitas</strong>
                                    <p>
                                        Nama dan identitas penerima tidak dapat diubah
                                        melalui halaman ini.
                                    </p>
                                </div>

                                <div>
                                    <strong>Verifikasi petugas</strong>
                                    <p>
                                        Setiap perubahan akan diperiksa oleh admin
                                        sebelum diterapkan pada data penerima.
                                    </p>
                                </div>

                                <div>
                                    <strong>Dokumen pendukung</strong>
                                    <p>
                                        Surat Keterangan RT dan Surat Keterangan Berhenti Kerja
                                        bersifat opsional. Lampirkan jika tersedia dan relevan
                                        dengan perubahan data yang diajukan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="panel comparison-panel">
                            <div class="comparison-heading">
                                <h3>Perbandingan Data</h3>
                                <p class="muted">
                                    Periksa perbedaan antara data yang tersimpan dan usulan perubahan Anda.
                                </p>
                            </div>

                            @php
                                $nikUsulan = old('nik', $penerima->nik_decrypted ?? '');
                                $kkUsulan = old('no_kk', $penerima->no_kk_decrypted ?? '');

                                $samarkan = function ($nomor) {
                                    if (!$nomor) return '-';

                                    return str_repeat('*', max(0, strlen($nomor) - 4))
                                        . substr($nomor, -4);
                                };

                                $rwSaatIni = $penerima->wilayah?->induk?->nama
                                    ? str_ireplace('RW ', '', $penerima->wilayah->induk->nama)
                                    : '-';

                                $rtSaatIni = $penerima->wilayah?->nama
                                    ? str_ireplace('RT ', '', $penerima->wilayah->nama)
                                    : '-';
                            @endphp

                            <div class="comparison-list">
                                <div class="comparison-item">
                                    <strong>Nama Lengkap</strong>
                                    <div class="comparison-values">
                                        <div class="comparison-current">
                                            <small>Data saat ini</small>
                                            <p>{{ $penerima->nama }}</p>
                                        </div>
                                        <div class="comparison-proposed">
                                            <small>Usulan baru</small>
                                            <b data-preview="nama">{{ old('nama', $penerima->nama) }}</b>
                                        </div>
                                    </div>
                                </div>

                                <div class="comparison-item">
                                    <strong>NIK</strong>
                                    <div class="comparison-values">
                                        <div class="comparison-current">
                                            <small>Data saat ini</small>
                                            <p>{{ $samarkan($penerima->nik_decrypted ?? '') }}</p>
                                        </div>
                                        <div class="comparison-proposed">
                                            <small>Usulan baru</small>
                                            <b data-preview="nik">{{ $samarkan($nikUsulan) }}</b>
                                        </div>
                                    </div>
                                </div>

                                <div class="comparison-item">
                                    <strong>Nomor KK</strong>
                                    <div class="comparison-values">
                                        <div class="comparison-current">
                                            <small>Data saat ini</small>
                                            <p>{{ $samarkan($penerima->no_kk_decrypted ?? '') }}</p>
                                        </div>
                                        <div class="comparison-proposed">
                                            <small>Usulan baru</small>
                                            <b data-preview="no_kk">{{ $samarkan($kkUsulan) }}</b>
                                        </div>
                                    </div>
                                </div>

                                <div class="comparison-item">
                                    <strong>Tanggal Lahir</strong>
                                    <div class="comparison-values">
                                        <div class="comparison-current">
                                            <small>Data saat ini</small>
                                            <p>{{ $penerima->tanggal_lahir?->format('d-m-Y') ?? '-' }}</p>
                                        </div>
                                        <div class="comparison-proposed">
                                            <small>Usulan baru</small>
                                            <b data-preview="tanggal_lahir">
                                                {{ old('tanggal_lahir', $penerima->tanggal_lahir?->format('Y-m-d')) ?: 'Belum diisi' }}
                                            </b>
                                        </div>
                                    </div>
                                </div>

                                <div class="comparison-item">
                                    <strong>Jenis Kelamin</strong>
                                    <div class="comparison-values">
                                        <div class="comparison-current">
                                            <small>Data saat ini</small>
                                            <p>{{ $penerima->jenis_kelamin === 'L' ? 'Laki-laki' : ($penerima->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</p>
                                        </div>
                                        <div class="comparison-proposed">
                                            <small>Usulan baru</small>
                                            <b data-preview="jenis_kelamin">
                                                {{ old('jenis_kelamin', $penerima->jenis_kelamin) === 'L' ? 'Laki-laki' : (old('jenis_kelamin', $penerima->jenis_kelamin) === 'P' ? 'Perempuan' : 'Belum diisi') }}
                                            </b>
                                        </div>
                                    </div>
                                </div>

                                <div class="comparison-item">
                                    <strong>Wilayah RT/RW</strong>
                                    <div class="comparison-values">
                                        <div class="comparison-current">
                                            <small>Data saat ini</small>
                                            <p>RT {{ $rtSaatIni }} / RW {{ $rwSaatIni }}</p>
                                        </div>
                                        <div class="comparison-proposed">
                                            <small>Usulan baru</small>
                                            <b data-preview="wilayah">
                                                RT {{ $rtSaatIni }} / RW {{ $rwSaatIni }}
                                            </b>
                                        </div>
                                    </div>
                                </div>

                                <div class="comparison-item">
                                    <strong>Alamat</strong>
                                    <div class="comparison-values">
                                        <div class="comparison-current">
                                            <small>Data saat ini</small>
                                            <p>{{ $penerima->alamat ?: '-' }}</p>
                                        </div>
                                        <div class="comparison-proposed">
                                            <small>Usulan baru</small>
                                            <b data-preview="alamat">{{ old('alamat', $penerima->alamat) }}</b>
                                        </div>
                                    </div>
                                </div>

                                <div class="comparison-item">
                                    <strong>Penghasilan Bulanan</strong>
                                    <div class="comparison-values">
                                        <div class="comparison-current">
                                            <small>Data saat ini</small>
                                            <p>Rp {{ number_format((float) $penerima->penghasilan_bulanan, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="comparison-proposed">
                                            <small>Usulan baru</small>
                                            <b data-preview="penghasilan_bulanan">
                                                Rp {{ number_format((float) old('penghasilan_bulanan', $penerima->penghasilan_bulanan), 0, ',', '.') }}
                                            </b>
                                        </div>
                                    </div>
                                </div>

                                <div class="comparison-item">
                                    <strong>Jumlah Tanggungan</strong>
                                    <div class="comparison-values">
                                        <div class="comparison-current">
                                            <small>Data saat ini</small>
                                            <p>{{ $penerima->jumlah_tanggungan ?? 0 }} orang</p>
                                        </div>
                                        <div class="comparison-proposed">
                                            <small>Usulan baru</small>
                                            <b data-preview="jumlah_tanggungan">
                                                {{ old('jumlah_tanggungan', $penerima->jumlah_tanggungan ?? 0) }} orang
                                            </b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

    @endif
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {

                    // Pilihan RT mengikuti RW yang dipilih.
                    const rwSelect = document.getElementById('id_rw');
                    const rtSelect = document.getElementById('id_wilayah');

                    if (rwSelect && rtSelect) {
                        const semuaRt = Array.from(rtSelect.options)
                            .slice(1)
                            .map(option => option.cloneNode(true));

                        function filterRt(resetPilihan = false) {
                            const rwTerpilih = rwSelect.value;
                            const rtSebelumnya = resetPilihan ? '' : rtSelect.value;

                            rtSelect.innerHTML = '<option value="">Pilih RT</option>';

                            semuaRt.forEach(option => {
                                if (option.dataset.rw === rwTerpilih) {
                                    rtSelect.appendChild(option.cloneNode(true));
                                }
                            });

                            const pilihanValid = Array.from(rtSelect.options)
                                .some(option => option.value === rtSebelumnya);

                            rtSelect.value = pilihanValid ? rtSebelumnya : '';
                        }

                        rwSelect.addEventListener('change', function () {
                            filterRt(true);
                        });

                        // Menjaga pilihan lama saat halaman pertama kali dibuka.
                        filterRt(false);
                    }

                    const fields = {
                        nama: {
                            input: document.getElementById('nama'),
                            output: document.querySelector('[data-preview="nama"]'),
                            fallback: 'Tidak diubah'
                        },
                        nik: {
                            input: document.getElementById('nik'),
                            output: document.querySelector('[data-preview="nik"]'),
                            fallback: 'Tidak diubah'
                        },
                        no_kk: {
                            input: document.getElementById('no_kk'),
                            output: document.querySelector('[data-preview="no_kk"]'),
                            fallback: 'Tidak diubah'
                        },
                        tanggal_lahir: {
                            input: document.getElementById('tanggal_lahir'),
                            output: document.querySelector('[data-preview="tanggal_lahir"]'),
                            fallback: 'Tidak diubah'
                        },
                        jenis_kelamin: {
                            input: document.getElementById('jenis_kelamin'),
                            output: document.querySelector('[data-preview="jenis_kelamin"]'),
                            fallback: 'Tidak diubah'
                        },
                        alamat: {
                            input: document.getElementById('alamat'),
                            output: document.querySelector('[data-preview="alamat"]'),
                            fallback: 'Tidak diubah'
                        },
                        penghasilan_bulanan: {
                            input: document.getElementById('penghasilan_bulanan'),
                            output: document.querySelector('[data-preview="penghasilan_bulanan"]'),
                            fallback: 'Tidak diubah'
                        },
                        jumlah_tanggungan: {
                            input: document.getElementById('jumlah_tanggungan'),
                            output: document.querySelector('[data-preview="jumlah_tanggungan"]'),
                            fallback: 'Tidak diubah'
                        }
                    };

                    function formatRupiah(value) {
                        const number = Number(value);

                        if (!Number.isFinite(number)) {
                            return 'Tidak diubah';
                        }

                        return 'Rp ' + new Intl.NumberFormat('id-ID', {
                            maximumFractionDigits: 0
                        }).format(number);
                    }

                    function samarkanNomor(value) {
                        if (!value) return 'Tidak diubah';

                        return '*'.repeat(Math.max(0, value.length - 4))
                            + value.slice(-4);
                    }

                    function formatTanggal(value) {
                        if (!value) return 'Tidak diubah';

                        const bagian = value.split('-');

                        if (bagian.length !== 3) return value;

                        return `${bagian[2]}-${bagian[1]}-${bagian[0]}`;
                    }

                    Object.entries(fields).forEach(([key, field]) => {
                        if (!field.input || !field.output) return;

                        function updatePreview() {
                            const value = field.input.value.trim();

                            if (!value) {
                                field.output.textContent = field.fallback;
                                return;
                            }

                            switch (key) {
                                case 'nik':
                                case 'no_kk':
                                    field.output.textContent = samarkanNomor(value);
                                    break;

                                case 'tanggal_lahir':
                                    field.output.textContent = formatTanggal(value);
                                    break;

                                case 'jenis_kelamin':
                                    field.output.textContent =
                                        value === 'L' ? 'Laki-laki' :
                                        value === 'P' ? 'Perempuan' : value;
                                    break;

                                case 'penghasilan_bulanan':
                                    field.output.textContent = formatRupiah(value);
                                    break;

                                case 'jumlah_tanggungan':
                                    field.output.textContent = value + ' orang';
                                    break;

                                default:
                                    field.output.textContent = value;
                            }
                        }

                        field.input.addEventListener('input', updatePreview);
                        field.input.addEventListener('change', updatePreview);
                        updatePreview();
                    });

                    // Preview perubahan wilayah RT/RW.
                    const previewWilayah = document.querySelector('[data-preview="wilayah"]');

                    function updatePreviewWilayah() {
                        if (!previewWilayah) return;

                        const rwText = rwSelect?.selectedOptions[0]?.textContent.trim() || '';
                        const rtText = rtSelect?.selectedOptions[0]?.textContent.trim() || '';

                        const rw = rwSelect?.value
                            ? rwText.replace(/^RW\s*/i, '')
                            : 'Pilih RW';

                        const rt = rtSelect?.value
                            ? rtText.replace(/^RT\s*/i, '')
                            : 'Pilih RT';

                        previewWilayah.textContent = `RT ${rt} / RW ${rw}`;
                    }

                    if (rwSelect && rtSelect) {
                        rwSelect.addEventListener('change', updatePreviewWilayah);
                        rtSelect.addEventListener('change', updatePreviewWilayah);
                        updatePreviewWilayah();
                    }
                });
            </script>
        @endpush
@endsection