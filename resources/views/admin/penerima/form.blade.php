@extends('layouts.admin')

@section('title', $p->exists ? 'Edit Penerima' : 'Tambah Penerima')
@section('heading', 'Data Penerima / ' . ($p->exists ? 'Edit' : 'Tambah'))

@section('content')

    @php
        $nikValue = old('nik', '');

        if ($p->exists && $nikValue === '') {
            try {
                $nikValue = decrypt($p->nik);
            } catch (\Throwable $e) {
                $nikValue = '';
            }
        }

        $wilayahRows = collect($wilayah);

        $rtList = $wilayahRows->where('tipe', 'rt');
        $rwList = $wilayahRows->where('tipe', 'rw');

        $selectedRtId = old('id_wilayah', $p->id_wilayah);

        $selectedRt = $rtList->firstWhere('id_wilayah', (int) $selectedRtId);

        $selectedRwId = old('id_rw', $selectedRt->id_induk ?? '');

        $hasKtp = $p->exists && $p->dokumen()->where('jenis_dokumen', 'ktp')->exists();

        $hasKk = $p->exists && $p->dokumen()->where('jenis_dokumen', 'kk')->exists();

        $hasDtks = $p->exists && $p->dokumen()->where('jenis_dokumen', 'lainnya')
            ->where('nama_file', 'like', 'Bukti DTKS/P3KE - %')->exists();

        $hasFotoDepan = $p->exists && $p->dokumen()->where('jenis_dokumen', 'lainnya')
            ->where('nama_file', 'like', 'Foto Rumah Depan - %')->exists();

        $hasFotoDalam = $p->exists && $p->dokumen()->where('jenis_dokumen', 'lainnya')
            ->where('nama_file', 'like', 'Foto Rumah Dalam - %')->exists();

        $hasSktm = $p->exists && $p->dokumen()->where('jenis_dokumen', 'surat_keterangan')
            ->where('nama_file', 'like', 'SKTM - %')->exists();

        $kodePenerima = $p->exists
            ? 'PNR-' . ($p->created_at?->format('Y') ?? date('Y'))
                . '-' . str_pad($p->id_penerima, 5, '0', STR_PAD_LEFT)
            : null;
    @endphp

    {{-- HEADER --}}
    <div class="page-head">
        <div class="page-head-text">
            <h1>{{ $p->exists ? 'Edit Penerima' : 'Tambah Penerima' }}</h1>

            <p>
                {{ $p->exists
                    ? 'Perbarui informasi penerima bantuan sosial.'
                    : 'Pendaftaran oleh petugas · NIK diperiksa otomatis terhadap data yang ada' }}
            </p>
        </div>

        <div class="page-head-actions">
            <a href="{{ route('admin.penerima.index') }}" class="btn btn-outline">
                <x-icon name="chevron-right" :size="14" style="transform:rotate(180deg)" />
                Kembali
            </a>
        </div>
    </div>

    {{-- STEPPER --}}
    <div class="card" style="margin-bottom:18px;">
        <div class="card-body" style="padding:15px 22px;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;font-size:12px;">
                @foreach (['Identitas', 'Kondisi ekonomi', 'Dokumen', 'Konfirmasi'] as $i => $label)
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="width:22px;height:22px;border-radius:50%;display:grid;place-items:center;font-size:10.5px;font-weight:700;
                            {{ $i === 0 ? 'background:#15370D;color:#fff;' : 'background:#F1F2EA;color:#8C9784;' }}">
                            {{ $i + 1 }}
                        </span>

                        <span style="font-weight:{{ $i === 0 ? '700' : '500' }};color:{{ $i === 0 ? '#16210F' : '#8C9784' }};">
                            {{ $label }}
                        </span>
                    </div>

                    @if (!$loop->last)
                        <span style="flex:1;min-width:16px;height:1px;background:#E6E8E2;"></span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data"
          action="{{ $p->exists ? route('admin.penerima.update', $p) : route('admin.penerima.store') }}"
          id="formPenerima">

        @csrf

        @if ($p->exists)
            @method('PUT')
        @endif

        <div class="grid-side" style="grid-template-columns: minmax(0,1fr) 330px;">

            <div class="stack">

                {{-- 01 IDENTITAS --}}
                <div class="form-card" style="margin-bottom:0;">
                    <div class="form-card-head">
                        <span class="form-card-num">01</span>

                        <div>
                            <h3>Identitas penerima</h3>
                            <p>Informasi identitas dasar penerima bantuan sosial.</p>
                        </div>
                    </div>

                    <div class="form-card-body">

                        <div class="form-grid">

                            <div class="field">
                                <label for="nik">NIK <span class="req">*</span></label>

                                <input class="input" type="text" id="nik" name="nik"
                                       maxlength="16" minlength="16" inputmode="numeric"
                                       pattern="[0-9]{16}"
                                       value="{{ $nikValue }}"
                                       placeholder="{{ $p->exists && $nikValue === '' ? 'NIK tersimpan terenkripsi — isi ulang bila ingin mengganti' : '16 digit NIK' }}"
                                       required>

                                <small class="hint">
                                    @if ($p->exists && $nikValue === '')
                                        NIK lama tidak dapat ditampilkan pada perangkat ini karena kunci
                                        enkripsi berbeda. Isi ulang hanya bila NIK memang perlu diubah.
                                    @else
                                        NIK harus terdiri dari 16 digit angka.
                                    @endif
                                </small>

                                @error('nik')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="no_kk">Nomor Kartu Keluarga</label>

                                <input class="input" type="text" id="no_kk" name="no_kk"
                                       maxlength="16" minlength="16" inputmode="numeric"
                                       pattern="[0-9]{16}"
                                       value="{{ old('no_kk', $p->no_kk_decrypted) }}"
                                       placeholder="{{ $p->exists && !$p->no_kk_decrypted ? 'Nomor KK tersimpan terenkripsi — isi ulang bila ingin mengganti' : '16 digit nomor KK' }}">

                                <small class="hint">16 digit sesuai Kartu Keluarga.</small>

                                @error('no_kk')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="nama">Nama Lengkap <span class="req">*</span></label>

                                <input class="input" type="text" id="nama" name="nama" maxlength="100"
                                       value="{{ old('nama', $p->nama) }}"
                                       placeholder="Sesuai KTP" required>

                                @error('nama')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="tanggal_lahir">Tanggal Lahir <span class="req">*</span></label>

                                <input class="input" type="date" id="tanggal_lahir" name="tanggal_lahir"
                                       value="{{ old('tanggal_lahir', $p->tanggal_lahir?->format('Y-m-d')) }}"
                                       required>

                                @error('tanggal_lahir')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="jenis_kelamin">Jenis Kelamin <span class="req">*</span></label>

                                <select class="select" id="jenis_kelamin" name="jenis_kelamin" required>
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="L" @selected(old('jenis_kelamin', $p->jenis_kelamin) === 'L')>Laki-laki</option>
                                    <option value="P" @selected(old('jenis_kelamin', $p->jenis_kelamin) === 'P')>Perempuan</option>
                                </select>

                                @error('jenis_kelamin')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="nomor_whatsapp">Nomor WhatsApp <span class="req">*</span></label>

                                <input class="input" type="tel" id="nomor_whatsapp" name="nomor_whatsapp"
                                       maxlength="20" value="{{ old('nomor_whatsapp', $p->nomor_whatsapp) }}"
                                       placeholder="081234567890" required>

                                <small class="hint">Nomor aktif untuk notifikasi.</small>

                                @error('nomor_whatsapp')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field span-2">
                                <label for="alamat">Alamat Lengkap <span class="req">*</span></label>

                                <textarea class="textarea" id="alamat" name="alamat" rows="2" required
                                          placeholder="Nama jalan, nomor rumah, kelurahan">{{ old('alamat', $p->alamat) }}</textarea>

                                @error('alamat')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="id_rw">RW <span class="req">*</span></label>

                                <select class="select" id="id_rw" name="id_rw" required>
                                    <option value="">Pilih RW</option>

                                    @foreach ($rwList as $rw)
                                        <option value="{{ $rw->id_wilayah }}"
                                            @selected((string) $selectedRwId === (string) $rw->id_wilayah)>
                                            {{ $rw->nama }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('id_rw')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="id_wilayah">RT <span class="req">*</span></label>

                                <select class="select" id="id_wilayah" name="id_wilayah" required>
                                    <option value="">Pilih RT</option>

                                    @foreach ($rtList as $rt)
                                        <option value="{{ $rt->id_wilayah }}"
                                                data-rw="{{ $rt->id_induk }}"
                                            @selected((string) $selectedRtId === (string) $rt->id_wilayah)>
                                            {{ $rt->nama }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('id_wilayah')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field span-2">
                                <label for="id_jenis">Program bantuan yang dituju <span class="req">*</span></label>

                                <select class="select" id="id_jenis" name="id_jenis" required>
                                    <option value="">Pilih program bantuan</option>

                                    @foreach ($jenisBantuan as $jenis)
                                        <option value="{{ $jenis->id_jenis }}"
                                            @selected((string) old('id_jenis', $p->id_jenis) === (string) $jenis->id_jenis)>
                                            {{ $jenis->nama_program }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('id_jenis')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>

                    </div>
                </div>

                {{-- 02 KONDISI EKONOMI --}}
                <div class="form-card" style="margin-bottom:0;">
                    <div class="form-card-head">
                        <span class="form-card-num">02</span>

                        <div>
                            <h3>Kondisi ekonomi</h3>
                            <p>Isian terstandar agar calon penerima dapat dibandingkan secara adil.</p>
                        </div>
                    </div>

                    <div class="form-card-body">

                        <div class="form-grid">

                            <div class="field">
                                <label for="penghasilan_bulanan">
                                    Penghasilan bulanan <span class="req">*</span>
                                </label>

                                <div class="input-prefix">
                                    <span>Rp</span>

                                    <input type="number" id="penghasilan_bulanan" name="penghasilan_bulanan"
                                           min="0" step="1000"
                                           value="{{ old('penghasilan_bulanan', $p->penghasilan_bulanan) }}"
                                           placeholder="1450000" required>
                                </div>

                                <small class="hint">Rata-rata penghasilan gabungan keluarga.</small>

                                @error('penghasilan_bulanan')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="jumlah_tanggungan">
                                    Jumlah tanggungan <span class="req">*</span>
                                </label>

                                <input class="input" type="number" id="jumlah_tanggungan"
                                       name="jumlah_tanggungan" min="0" max="99"
                                       value="{{ old('jumlah_tanggungan', $p->jumlah_tanggungan ?? 0) }}"
                                       required>

                                @error('jumlah_tanggungan')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>

                        <div class="field" style="margin-top:16px;">
                            <label>Kondisi rumah <span class="req">*</span></label>

                            <div class="tile-group">
                                @foreach ([
                                    'permanen' => ['Permanen', 'Dinding bata atau beton'],
                                    'semi_permanen' => ['Semi permanen', 'Sebagian kayu atau batako'],
                                    'tidak_layak' => ['Tidak layak', 'Kondisi rumah tidak layak huni'],
                                ] as $value => $item)
                                    <label class="tile">
                                        <input type="radio" name="kondisi_rumah" value="{{ $value }}"
                                            @checked(old('kondisi_rumah', $p->kondisi_rumah) === $value)
                                            required>

                                        <span class="tile-icon"><x-icon name="home" :size="15" /></span>

                                        <span>
                                            <strong>{{ $item[0] }}</strong>
                                            <small>{{ $item[1] }}</small>
                                        </span>

                                        <span class="tile-check"><x-icon name="check" :size="9" /></span>
                                    </label>
                                @endforeach
                            </div>

                            @error('kondisi_rumah')
                                <small class="err">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="field" style="margin-top:16px;">
                            <label>Kondisi khusus</label>

                            <div class="check-grid">

                                <label class="check-card">
                                    <input type="hidden" name="disabilitas" value="0">

                                    <input type="checkbox" name="disabilitas" value="1"
                                        @checked((bool) old('disabilitas', $p->disabilitas))>

                                    <span>
                                        <strong>Penyandang disabilitas</strong>
                                        <small>Menambah prioritas penilaian</small>
                                    </span>
                                </label>

                                <label class="check-card">
                                    <input type="hidden" name="lansia" value="0">

                                    <input type="checkbox" name="lansia" value="1"
                                        @checked((bool) old('lansia', $p->lansia))>

                                    <span>
                                        <strong>Lansia</strong>
                                        <small>Usia 60 tahun ke atas</small>
                                    </span>
                                </label>

                            </div>
                        </div>

                    </div>
                </div>

                {{-- 03 DOKUMEN --}}
                <div class="form-card" style="margin-bottom:0;">
                    <div class="form-card-head">
                        <span class="form-card-num">03</span>

                        <div>
                            <h3>Dokumen pendukung</h3>
                            <p>Dokumen dapat dilengkapi kemudian, namun pengajuan belum dapat diverifikasi sebelum KTP dan KK terunggah.</p>
                        </div>
                    </div>

                    <div class="form-card-body">

                        <div class="upload-grid">
                            @foreach ([
                                ['id' => 'dokumen_ktp', 'title' => 'Unggah foto KTP', 'desc' => 'JPG atau PNG · maksimal 2 MB', 'accept' => '.jpg,.jpeg,.png', 'ada' => $hasKtp],
                                ['id' => 'dokumen_kk', 'title' => 'Unggah Kartu Keluarga', 'desc' => 'JPG, PNG, atau PDF · maksimal 2 MB', 'accept' => '.jpg,.jpeg,.png,.pdf', 'ada' => $hasKk],
                                ['id' => 'dokumen_dtks_p3ke', 'title' => 'Bukti DTKS / P3KE', 'desc' => 'JPG, PNG, atau PDF · maksimal 2 MB', 'accept' => '.jpg,.jpeg,.png,.pdf', 'ada' => $hasDtks],
                                ['id' => 'foto_rumah_depan', 'title' => 'Foto rumah tampak depan', 'desc' => 'JPG atau PNG · maksimal 2 MB', 'accept' => '.jpg,.jpeg,.png', 'ada' => $hasFotoDepan],
                                ['id' => 'foto_rumah_dalam', 'title' => 'Foto bagian dalam rumah', 'desc' => 'JPG atau PNG · maksimal 2 MB', 'accept' => '.jpg,.jpeg,.png', 'ada' => $hasFotoDalam],
                                ['id' => 'dokumen_sktm', 'title' => 'Surat keterangan tidak mampu', 'desc' => 'JPG, PNG, atau PDF · maksimal 2 MB', 'accept' => '.jpg,.jpeg,.png,.pdf', 'ada' => $hasSktm],
                            ] as $doc)
                                <div>
                                    <label for="{{ $doc['id'] }}" class="upload-zone">
                                        <span class="upload-icon">
                                            <x-icon name="upload" :size="16" />
                                        </span>

                                        <strong>{{ $doc['title'] }}</strong>
                                        <span style="font-size:11px;color:#8C9784;">{{ $doc['desc'] }}</span>

                                        <span class="upload-btn">Pilih File</span>

                                        <input type="file" id="{{ $doc['id'] }}" name="{{ $doc['id'] }}"
                                               accept="{{ $doc['accept'] }}" data-file-input>
                                    </label>

                                    <small id="nama-{{ $doc['id'] }}" class="file-note {{ $doc['ada'] ? 'ok' : '' }}">
                                        {{ $doc['ada'] ? 'Dokumen sudah tersimpan.' : 'Belum ada file dipilih.' }}
                                    </small>

                                    @error($doc['id'])
                                        <small class="err">{{ $message }}</small>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>

            </div>

            {{-- SIDEBAR --}}
            <aside class="stack">

                <div class="card">
                    <div class="card-head">
                        <div>
                            <h3>Pemeriksaan otomatis</h3>
                            <p>Dilakukan saat data disimpan</p>
                        </div>
                    </div>

                    <div class="card-body" style="padding-top:6px;padding-bottom:8px;">
                        <div class="info-list">
                            <div class="info-row">
                                <small>Format NIK</small>
                                <strong style="color:#2C5320;">
                                    {{ strlen((string) $nikValue) === 16 ? 'Valid · 16 digit' : 'Belum lengkap' }}
                                </strong>
                            </div>

                            <div class="info-row">
                                <small>NIK ganda</small>
                                <strong>Diperiksa sistem</strong>
                            </div>

                            <div class="info-row">
                                <small>Nomor KK</small>
                                <strong>Opsional</strong>
                            </div>

                            <div class="info-row">
                                <small>Status awal</small>
                                <strong>Terverifikasi</strong>
                            </div>
                        </div>

                        <div class="alert alert-info" style="margin:14px 0 0;">
                            <x-icon name="info" :size="14" />
                            <div style="font-size:11.5px;">
                                Data yang disimpan tercatat atas nama Anda pada log aktivitas.
                                NIK dan nomor KK disimpan terenkripsi.
                            </div>
                        </div>
                    </div>
                </div>

                @if ($p->exists)
                    <div class="card">
                        <div class="card-head">
                            <div>
                                <h3>Ringkasan</h3>
                                <p>{{ $kodePenerima }}</p>
                            </div>
                        </div>

                        <div class="card-body" style="padding-top:6px;">
                            <div class="info-list">
                                <div class="info-row">
                                    <small>Status</small>
                                    <strong>
                                        {{ [
                                            'diajukan' => 'Menunggu',
                                            'terverifikasi' => 'Terverifikasi',
                                            'ditolak' => 'Ditolak',
                                            'nonaktif' => 'Nonaktif',
                                        ][$p->status] ?? ucfirst($p->status) }}
                                    </strong>
                                </div>

                                <div class="info-row">
                                    <small>Terdaftar</small>
                                    <strong>{{ $p->created_at?->format('d M Y') ?? '-' }}</strong>
                                </div>

                                <div class="info-row">
                                    <small>Riwayat</small>
                                    <strong>{{ $p->riwayat()->count() }} penyaluran</strong>
                                </div>

                                <div class="info-row">
                                    <small>Dokumen</small>
                                    <strong>{{ $p->dokumen()->count() }} berkas</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body" style="display:grid;gap:9px;">
                        <button type="submit" class="btn btn-primary btn-block">
                            <x-icon name="check" :size="14" />
                            {{ $p->exists ? 'Simpan Perubahan' : 'Simpan Data' }}
                        </button>

                        <a href="{{ route('admin.penerima.index') }}" class="btn btn-outline btn-block">
                            Batal
                        </a>
                    </div>
                </div>

            </aside>

        </div>

    </form>

@endsection

@push('scripts')
<script>
    (function () {
        var rw = document.getElementById('id_rw');
        var rt = document.getElementById('id_wilayah');

        if (rw && rt) {
            var semuaRt = Array.prototype.slice.call(rt.options).map(function (o) {
                return { value: o.value, text: o.text, rw: o.getAttribute('data-rw') };
            });

            function saringRt() {
                var dipilih = rw.value;
                var terpilih = rt.value;

                rt.innerHTML = '';

                semuaRt.forEach(function (o) {
                    if (!o.value) {
                        rt.appendChild(new Option(o.text, o.value));
                        return;
                    }

                    if (!dipilih || o.rw === dipilih) {
                        var opt = new Option(o.text, o.value);
                        opt.setAttribute('data-rw', o.rw);
                        rt.appendChild(opt);
                    }
                });

                rt.value = terpilih;
            }

            rw.addEventListener('change', function () {
                rt.value = '';
                saringRt();
            });

            saringRt();
        }

        document.querySelectorAll('[data-file-input]').forEach(function (input) {
            input.addEventListener('change', function () {
                var catatan = document.getElementById('nama-' + input.id);

                if (!catatan) {
                    return;
                }

                if (input.files && input.files.length) {
                    catatan.textContent = input.files[0].name + ' · '
                        + Math.round(input.files[0].size / 1024) + ' KB';
                    catatan.classList.add('ok');
                } else {
                    catatan.textContent = 'Belum ada file dipilih.';
                    catatan.classList.remove('ok');
                }
            });
        });
    })();
</script>
@endpush
