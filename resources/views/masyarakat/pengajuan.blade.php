@extends('layouts.masyarakat')

@section('title', 'Pengajuan Bantuan')
@section('heading', 'Pengajuan Bantuan')

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

    {{-- HEADER --}}
    <div class="page-head">
        <div class="page-head-text">
            <h1>Ajukan Bantuan Sosial</h1>
            <p>Isi formulir berikut &mdash; data Anda aman dan hanya dilihat petugas</p>
        </div>

        <div class="page-head-actions">
            <a href="{{ route('masyarakat.status') }}" class="btn btn-outline">
                <x-icon name="file" :size="14" />
                Status Pengajuan
            </a>
        </div>
    </div>

    {{-- STEPPER --}}
    <div class="card" style="margin-bottom:18px;">
        <div class="card-body" style="padding:16px 22px;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;font-size:12px;">

                @foreach ([
                    ['Identitas & alamat', 'user'],
                    ['Kondisi ekonomi', 'wallet'],
                    ['Dokumen', 'file'],
                    ['Tinjau & kirim', 'send'],
                ] as $i => $step)
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="width:22px;height:22px;border-radius:50%;display:grid;place-items:center;font-size:10.5px;font-weight:700;
                            {{ $i === 0
                                ? 'background:#15370D;color:#fff;'
                                : 'background:#F1F2EA;color:#8C9784;' }}">
                            {{ $i + 1 }}
                        </span>

                        <span style="font-weight:{{ $i === 0 ? '700' : '500' }};color:{{ $i === 0 ? '#16210F' : '#8C9784' }};">
                            {{ $step[0] }}
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
          action="{{ route('masyarakat.pengajuan.store') }}" id="formPengajuan">

        @csrf

        <div class="grid-side" style="grid-template-columns: minmax(0,1fr) 340px;">

            <div class="stack">

                {{-- 01 IDENTITAS --}}
                <div class="form-card" style="margin-bottom:0;">
                    <div class="form-card-head">
                        <span class="form-card-num">01</span>

                        <div>
                            <h3>Identitas pemohon</h3>
                            <p>
                                @if ($sudahTerdaftar)
                                    NIK dan nama terisi otomatis dari akun Anda dan tidak dapat diubah di sini.
                                @else
                                    Lengkapi identitas sesuai KTP dan Kartu Keluarga.
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="form-card-body">

                        @if ($sudahTerdaftar)
                            <div class="alert alert-info" style="margin-bottom:16px;">
                                <x-icon name="lock" :size="15" />
                                <div style="font-size:11.5px;">
                                    Identitas terkunci. Untuk memperbaiki NIK atau nama, gunakan
                                    <a href="{{ route('masyarakat.perbaikan.create') }}" style="font-weight:700;text-decoration:underline;">
                                        perbaikan data
                                    </a>.
                                </div>
                            </div>
                        @endif

                        <div class="form-grid">

                            <div class="field">
                                <label for="nik">NIK <span class="req">*</span></label>

                                <input class="input" type="text" id="nik" name="nik"
                                       maxlength="16" minlength="16" inputmode="numeric"
                                       pattern="[0-9]{16}"
                                       value="{{ old('nik', $nik ?? '') }}"
                                       placeholder="{{ $sudahTerdaftar && !$nik ? 'NIK tersimpan terenkripsi — tidak dapat ditampilkan' : '16 digit NIK' }}"
                                       {{ $sudahTerdaftar ? 'readonly' : 'required' }}>

                                <small class="hint">
                                    @if (!$sudahTerdaftar)
                                        16 digit sesuai KTP.
                                    @elseif ($nik)
                                        NIK terdaftar pada akun Anda.
                                    @else
                                        NIK tersimpan dalam bentuk terenkripsi dan tidak dapat ditampilkan
                                        pada perangkat ini. Data tetap tersimpan dan dipakai saat verifikasi.
                                    @endif
                                </small>

                                @error('nik')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="no_kk">Nomor Kartu Keluarga <span class="req">*</span></label>

                                <input class="input" type="text" id="no_kk" name="no_kk"
                                       maxlength="16" minlength="16" inputmode="numeric"
                                       pattern="[0-9]{16}"
                                       value="{{ old('no_kk', $penerima->no_kk_decrypted ?? '') }}"
                                       placeholder="{{ $sudahTerdaftar && !$penerima->no_kk_decrypted ? 'Nomor KK tersimpan terenkripsi — tidak dapat ditampilkan' : '16 digit nomor KK' }}"
                                       {{ $sudahTerdaftar ? 'readonly' : 'required' }}>

                                <small class="hint">Sesuai Kartu Keluarga.</small>

                                @error('no_kk')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="nama">Nama Lengkap <span class="req">*</span></label>

                                <input class="input" type="text" id="nama" name="nama" maxlength="100"
                                       value="{{ old('nama', $penerima->nama ?? '') }}"
                                       placeholder="Sesuai KTP"
                                       {{ $sudahTerdaftar ? 'readonly' : 'required' }}>

                                @error('nama')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="tanggal_lahir">Tanggal Lahir <span class="req">*</span></label>

                                <input class="input" type="date" id="tanggal_lahir" name="tanggal_lahir"
                                       value="{{ old('tanggal_lahir', isset($penerima) ? $penerima->tanggal_lahir?->format('Y-m-d') : '') }}"
                                       {{ $sudahTerdaftar ? 'readonly' : 'required' }}>

                                @error('tanggal_lahir')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="jenis_kelamin">Jenis Kelamin <span class="req">*</span></label>

                                <select class="select" id="jenis_kelamin" name="jenis_kelamin"
                                        {{ $sudahTerdaftar ? 'disabled' : 'required' }}>
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="L" @selected(old('jenis_kelamin', $penerima->jenis_kelamin ?? '') === 'L')>Laki-laki</option>
                                    <option value="P" @selected(old('jenis_kelamin', $penerima->jenis_kelamin ?? '') === 'P')>Perempuan</option>
                                </select>

                                @if ($sudahTerdaftar)
                                    <input type="hidden" name="jenis_kelamin" value="{{ $penerima->jenis_kelamin }}">
                                @endif

                                @error('jenis_kelamin')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="nomor_whatsapp">Nomor WhatsApp <span class="req">*</span></label>

                                <input class="input" type="tel" id="nomor_whatsapp" name="nomor_whatsapp"
                                       maxlength="20"
                                       value="{{ old('nomor_whatsapp', $penerima->nomor_whatsapp ?? '') }}"
                                       placeholder="081234567890" required>

                                <small class="hint">Dipakai untuk notifikasi status.</small>

                                @error('nomor_whatsapp')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="field span-2">
                                <label for="alamat">Alamat Lengkap <span class="req">*</span></label>

                                <textarea class="textarea" id="alamat" name="alamat" rows="2" required
                                          placeholder="Nama jalan, nomor rumah, kelurahan">{{ old('alamat', $penerima->alamat ?? '') }}</textarea>

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
                                            @selected((string) old('id_jenis') === (string) $jenis->id_jenis)>
                                            {{ $jenis->nama_program }}
                                            @if ($jenis->kategori)
                                                &mdash; {{ $jenis->kategori->nama }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>

                                <small class="hint">
                                    Pilihan RT akan menyesuaikan RW yang dipilih.
                                </small>

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
                                    Penghasilan bulanan keluarga <span class="req">*</span>
                                </label>

                                <div class="input-prefix">
                                    <span>Rp</span>

                                    <input type="number" id="penghasilan_bulanan" name="penghasilan_bulanan"
                                           min="0" step="1000"
                                           value="{{ old('penghasilan_bulanan', $penerima->penghasilan_bulanan ?? '') }}"
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
                                       value="{{ old('jumlah_tanggungan', $penerima->jumlah_tanggungan ?? 0) }}"
                                       required>

                                <small class="hint">Anggota keluarga yang menjadi tanggungan.</small>

                                @error('jumlah_tanggungan')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>

                        <div class="field" style="margin-top:16px;">
                            <label>Kondisi rumah <span class="req">*</span></label>

                            <div class="tile-group">
                                @foreach ([
                                    'permanen' => ['Permanen', 'Dinding bata, atap beton'],
                                    'semi_permanen' => ['Semi permanen', 'Sebagian kayu / batako'],
                                    'tidak_layak' => ['Tidak layak', 'Bambu, lantai tanah'],
                                ] as $value => $item)
                                    <label class="tile">
                                        <input type="radio" name="kondisi_rumah" value="{{ $value }}"
                                            @checked(old('kondisi_rumah', $penerima->kondisi_rumah ?? '') === $value)
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
                                        @checked((string) old('disabilitas', (int) ($penerima->disabilitas ?? 0)) === '1')>

                                    <span>
                                        <strong>Ada anggota keluarga disabilitas</strong>
                                        <small>Menambah prioritas penilaian</small>
                                    </span>
                                </label>

                                <label class="check-card">
                                    <input type="hidden" name="lansia" value="0">

                                    <input type="checkbox" name="lansia" value="1"
                                        @checked((string) old('lansia', (int) ($penerima->lansia ?? 0)) === '1')>

                                    <span>
                                        <strong>Ada anggota keluarga lansia</strong>
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
                            <p>Unggah seluruh dokumen yang diperlukan untuk pengajuan bantuan.</p>
                        </div>
                    </div>

                    <div class="form-card-body">

                        @php
                            $documents = [
                                ['id' => 'dokumen_ktp', 'title' => 'Foto KTP', 'description' => 'JPG atau PNG · maksimal 2 MB', 'accept' => '.jpg,.jpeg,.png'],
                                ['id' => 'dokumen_kk', 'title' => 'Kartu Keluarga', 'description' => 'JPG, PNG, atau PDF · maksimal 2 MB', 'accept' => '.jpg,.jpeg,.png,.pdf'],
                                ['id' => 'dokumen_dtks_p3ke', 'title' => 'Bukti DTKS / P3KE', 'description' => 'JPG, PNG, atau PDF · maksimal 2 MB', 'accept' => '.jpg,.jpeg,.png,.pdf'],
                                ['id' => 'foto_rumah_depan', 'title' => 'Foto Rumah Depan', 'description' => 'JPG atau PNG · maksimal 2 MB', 'accept' => '.jpg,.jpeg,.png'],
                                ['id' => 'foto_rumah_dalam', 'title' => 'Foto Bagian Dalam Rumah', 'description' => 'JPG atau PNG · maksimal 2 MB', 'accept' => '.jpg,.jpeg,.png'],
                                ['id' => 'dokumen_sktm', 'title' => 'Surat Keterangan Tidak Mampu', 'description' => 'JPG, PNG, atau PDF · maksimal 2 MB', 'accept' => '.jpg,.jpeg,.png,.pdf'],
                            ];
                        @endphp

                        <div class="upload-grid">
                            @foreach ($documents as $document)
                                <div>
                                    <label for="{{ $document['id'] }}" class="upload-zone">
                                        <span class="upload-icon">
                                            <x-icon name="upload" :size="16" />
                                        </span>

                                        <strong>{{ $document['title'] }}</strong>
                                        <span style="font-size:11px;color:#8C9784;">{{ $document['description'] }}</span>

                                        <span class="upload-btn">Pilih File</span>

                                        <input type="file" id="{{ $document['id'] }}"
                                               name="{{ $document['id'] }}"
                                               accept="{{ $document['accept'] }}"
                                               data-file-input required>
                                    </label>

                                    <small id="nama-{{ $document['id'] }}" class="file-note">
                                        Belum ada file dipilih.
                                    </small>

                                    @error($document['id'])
                                        <small class="err">{{ $message }}</small>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        <p class="hint" style="margin-top:14px;">
                            Pastikan seluruh dokumen terbaca jelas. Bukti DTKS/P3KE digunakan untuk
                            mendukung pemeriksaan status kesejahteraan, sedangkan foto rumah dan SKTM
                            digunakan untuk asesmen kondisi tempat tinggal.
                        </p>

                    </div>
                </div>

            </div>

            {{-- SIDEBAR --}}
            <aside class="stack">

                <div class="cta-card">
                    <span class="eyebrow">Setelah dikirim</span>

                    <b>Pengajuan masuk dengan status Diajukan</b>

                    <p style="margin-top:8px;">
                        Petugas desa akan memeriksa dokumen dan kondisi ekonomi Anda.
                        Proses verifikasi biasanya memakan waktu 3&ndash;7 hari kerja.
                    </p>

                    <div style="display:flex;gap:24px;margin-top:16px;">
                        <div>
                            <b style="font-size:16px;">3&ndash;7 hari</b>
                            <p style="font-size:10.5px;margin-top:2px;">waktu verifikasi</p>
                        </div>

                        <div>
                            <b style="font-size:16px;">4 tahap</b>
                            <p style="font-size:10.5px;margin-top:2px;">pemeriksaan</p>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">
                        <div>
                            <h3>Kelengkapan berkas</h3>
                            <p>Diperbarui saat Anda mengunggah</p>
                        </div>
                    </div>

                    <div class="card-body" style="padding-top:6px;">

                        <div class="dot-list" id="checklistBerkas">
                            <div class="dot-row" data-check="identitas">
                                <i style="background:#15370D;"></i>
                                <span>Identitas</span>
                                <b class="muted" style="font-weight:600;">Lengkap</b>
                            </div>

                            <div class="dot-row" data-check="alamat">
                                <i style="background:#15370D;"></i>
                                <span>Alamat</span>
                                <b class="muted" style="font-weight:600;">Lengkap</b>
                            </div>

                            <div class="dot-row" data-check="ekonomi">
                                <i style="background:#15370D;"></i>
                                <span>Kondisi ekonomi</span>
                                <b class="muted" style="font-weight:600;">Lengkap</b>
                            </div>

                            <div class="dot-row" data-check="dokumen_ktp">
                                <i style="background:#C9CFC3;"></i>
                                <span>Foto KTP</span>
                                <b class="muted" style="font-weight:600;">Belum</b>
                            </div>

                            <div class="dot-row" data-check="dokumen_kk">
                                <i style="background:#C9CFC3;"></i>
                                <span>Kartu Keluarga</span>
                                <b class="muted" style="font-weight:600;">Belum</b>
                            </div>
                        </div>

                        <div class="progress">
                            <i id="progressBerkas" style="width:60%;"></i>
                        </div>

                        <small class="hint" id="progressLabel">3 dari 5 berkas lengkap</small>

                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div style="display:flex;gap:9px;">
                            <span class="ic" style="width:28px;height:28px;border-radius:8px;background:#EAF2E4;color:#15370D;display:grid;place-items:center;flex:none;">
                                <x-icon name="lock" :size="14" />
                            </span>

                            <p style="font-size:11.5px;color:#6B7764;line-height:1.6;">
                                Dengan mengirim pengajuan, Anda menyetujui data pribadi digunakan
                                untuk pendataan bantuan sosial sesuai UU No. 27 Tahun 2022.
                                Data tidak dibagikan ke pihak lain tanpa persetujuan.
                            </p>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" style="margin-top:14px;">
                            <x-icon name="send" :size="14" />
                            Kirim Pengajuan
                        </button>

                        <small class="hint" style="text-align:center;display:block;">
                            Anda dapat menarik pengajuan selama status masih Diajukan.
                        </small>
                    </div>
                </div>

            </aside>

        </div>

    </form>

@endsection

@push('scripts')
<script>
    (function () {
        // Filter pilihan RT berdasarkan RW.
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

        // Nama file terpilih + kelengkapan berkas.
        var inputs = document.querySelectorAll('[data-file-input]');
        var lengkap = 3;
        var total = 5;

        function segarkan() {
            var persen = Math.round((lengkap / total) * 100);

            var bar = document.getElementById('progressBerkas');
            var label = document.getElementById('progressLabel');

            if (bar) { bar.style.width = persen + '%'; }
            if (label) { label.textContent = lengkap + ' dari ' + total + ' berkas lengkap'; }
        }

        inputs.forEach(function (input) {
            input.addEventListener('change', function () {
                var catatan = document.getElementById('nama-' + input.id);
                var baris = document.querySelector('[data-check="' + input.id + '"]');
                var sudah = baris && baris.dataset.done === '1';

                if (input.files && input.files.length) {
                    if (catatan) {
                        catatan.textContent = input.files[0].name + ' · '
                            + Math.round(input.files[0].size / 1024) + ' KB';
                        catatan.classList.add('ok');
                    }

                    if (baris && !sudah) {
                        baris.dataset.done = '1';
                        baris.querySelector('i').style.background = '#15370D';
                        baris.querySelector('b').textContent = 'Terunggah';
                        lengkap = Math.min(total, lengkap + 1);
                        segarkan();
                    }
                } else if (catatan) {
                    catatan.textContent = 'Belum ada file dipilih.';
                    catatan.classList.remove('ok');
                }
            });
        });

        segarkan();
    })();
</script>
@endpush
