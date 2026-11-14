@extends('layouts.masyarakat')

@section('title', 'Perbaikan Data')
@section('heading', 'Perbaikan Data')

@section('content')

    @php
        $labelUsulan = [
            'alamat' => 'Alamat',
            'nomor_whatsapp' => 'No. WhatsApp',
            'penghasilan_bulanan' => 'Penghasilan',
            'jumlah_tanggungan' => 'Tanggungan',
            'kondisi_rumah' => 'Kondisi rumah',
        ];

        $nikWarga = $penerima?->nik_decrypted;

        $menunggu = $items->where('status', 'menunggu')->count();
    @endphp

    <div class="page-head">
        <div class="page-head-text">
            <h1>Ajukan Perbaikan Data</h1>
            <p>Usulan perubahan diperiksa admin sebelum berlaku</p>
        </div>
    </div>

    @if (!$penerima)
        <div class="card">
            <div class="empty" style="padding:56px 24px;">
                <div class="empty-icon"><x-icon name="edit" :size="22" /></div>
                <strong>Data penerima belum tersedia</strong>
                <p>Anda perlu mengajukan pendataan bantuan terlebih dahulu.</p>

                <a href="{{ route('masyarakat.pengajuan.create') }}" class="btn btn-primary">
                    Ajukan Bantuan
                </a>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            <x-icon name="alert" :size="16" />

            <div>
                <strong>Perubahan tidak langsung berlaku.</strong>
                Admin akan memeriksa usulan Anda terlebih dahulu. Selama menunggu,
                data lama tetap digunakan pada seluruh perhitungan.
            </div>
        </div>

        <div class="grid-side" style="grid-template-columns: minmax(0,1fr) 340px;">

            <div class="stack">

                {{-- IDENTITAS TETAP --}}
                <div class="form-card">
                    <div class="form-card-head">
                        <span class="form-card-num"><x-icon name="lock" :size="13" /></span>

                        <div>
                            <h3>Identitas &mdash; tidak dapat diubah sendiri</h3>
                            <p>
                                NIK, nama, dan tanggal lahir hanya dapat diubah petugas
                                dengan verifikasi dokumen.
                            </p>
                        </div>
                    </div>

                    <div class="form-card-body">
                        <div class="form-grid cols-3">

                            <div class="field">
                                <label>NIK</label>

                                <input class="input" type="text" readonly
                                       value="{{ $nikWarga ?? 'Tidak dapat dibaca' }}">
                            </div>

                            <div class="field">
                                <label>Nama</label>

                                <input class="input" type="text" readonly
                                       value="{{ $penerima->nama }}">
                            </div>

                            <div class="field">
                                <label>Tanggal lahir</label>

                                <input class="input" type="text" readonly
                                       value="{{ $penerima->tanggal_lahir?->translatedFormat('d F Y') ?? '—' }}">
                            </div>

                        </div>
                    </div>
                </div>

                {{-- FORM --}}
                <form method="POST" action="{{ route('masyarakat.perbaikan.store') }}" id="formPerbaikan">
                    @csrf

                    <div class="form-card">

                        <div class="form-card-head">
                            <span class="form-card-num">02</span>

                            <div>
                                <h3>Yang dapat Anda usulkan</h3>
                                <p>Isi hanya bagian yang ingin diubah. Bagian kosong tidak akan diubah.</p>
                            </div>
                        </div>

                        <div class="form-card-body">

                            <div class="form-grid">

                                <div class="field">
                                    <label for="nomor_whatsapp">Nomor WhatsApp</label>

                                    <input class="input" type="tel" id="nomor_whatsapp"
                                           name="nomor_whatsapp" maxlength="20"
                                           value="{{ old('nomor_whatsapp') }}"
                                           placeholder="{{ $penerima->nomor_whatsapp ?: '081234567890' }}">

                                    <small class="hint">
                                        Nilai sekarang: {{ $penerima->nomor_whatsapp ?: 'belum diisi' }}
                                    </small>
                                </div>

                                <div class="field">
                                    <label for="jumlah_tanggungan">Jumlah tanggungan</label>

                                    <input class="input" type="number" id="jumlah_tanggungan"
                                           name="jumlah_tanggungan" min="0" max="99"
                                           value="{{ old('jumlah_tanggungan') }}"
                                           placeholder="{{ $penerima->jumlah_tanggungan }}">

                                    <small class="hint">
                                        Sebelumnya {{ $penerima->jumlah_tanggungan }} orang
                                    </small>
                                </div>

                                <div class="field span-2">
                                    <label for="alamat">Alamat lengkap</label>

                                    <textarea class="textarea" id="alamat" name="alamat" rows="2"
                                              placeholder="{{ $penerima->alamat }}">{{ old('alamat') }}</textarea>

                                    <small class="hint">Alamat saat ini: {{ $penerima->alamat }}</small>
                                </div>

                                <div class="field span-2">
                                    <label for="penghasilan_bulanan">Penghasilan bulanan</label>

                                    <div class="input-prefix">
                                        <span>Rp</span>

                                        <input type="number" id="penghasilan_bulanan"
                                               name="penghasilan_bulanan" min="0" step="1000"
                                               value="{{ old('penghasilan_bulanan') }}"
                                               placeholder="{{ (int) $penerima->penghasilan_bulanan }}">
                                    </div>

                                    <small class="hint">
                                        Sebelumnya Rp {{ number_format((float) $penerima->penghasilan_bulanan, 0, ',', '.') }}
                                    </small>
                                </div>

                            </div>

                            <div class="field" style="margin-top:16px;">
                                <label>Kondisi rumah</label>

                                <div class="tile-group">

                                    @foreach ([
                                        'permanen' => ['Permanen', 'Dinding bata atau beton', 'home'],
                                        'semi_permanen' => ['Semi permanen', 'Sebagian kayu atau batako', 'home'],
                                        'tidak_layak' => ['Tidak layak', 'Bambu, lantai tanah', 'home'],
                                    ] as $value => $item)
                                        <label class="tile">
                                            <input type="radio" name="kondisi_rumah" value="{{ $value }}"
                                                @checked(old('kondisi_rumah') === $value)>

                                            <span class="tile-icon">
                                                <x-icon :name="$item[2]" :size="15" />
                                            </span>

                                            <span>
                                                <strong>{{ $item[0] }}</strong>
                                                <small>{{ $item[1] }}</small>
                                            </span>

                                            <span class="tile-check">
                                                <x-icon name="check" :size="9" />
                                            </span>
                                        </label>
                                    @endforeach

                                </div>

                                <small class="hint">
                                    Kondisi saat ini:
                                    {{ [
                                        'permanen' => 'Permanen',
                                        'semi_permanen' => 'Semi permanen',
                                        'tidak_layak' => 'Tidak layak',
                                    ][$penerima->kondisi_rumah] ?? '-' }}
                                </small>
                            </div>

                            <div class="field" style="margin-top:16px;">
                                <label for="alasan">Alasan perbaikan <span class="req">*</span></label>

                                <textarea class="textarea" id="alasan" name="alasan" rows="3" required
                                          placeholder="Contoh: Saya berhenti bekerja sejak Desember karena sakit, sehingga penghasilan menurun.">{{ old('alasan') }}</textarea>

                                <small class="hint">
                                    Jelaskan secara singkat dan dapat diverifikasi petugas.
                                </small>

                                @error('alasan')
                                    <small class="err">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>

                        <div class="card-foot" style="justify-content:flex-end;">
                            <a href="{{ route('dashboard') }}" class="btn btn-outline">Batal</a>

                            <button type="submit" class="btn btn-primary">
                                <x-icon name="send" :size="14" />
                                Kirim Usulan Perbaikan
                            </button>
                        </div>

                    </div>

                </form>

            </div>

            {{-- SIDEBAR --}}
            <aside class="stack">

                <div class="card">
                    <div class="card-head">
                        <div>
                            <h3>Ringkasan usulan</h3>
                            <p>Perubahan akan dibandingkan sebelum disetujui</p>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="info-list">
                            <div class="info-row">
                                <small>No. WhatsApp</small>
                                <strong>{{ $penerima->nomor_whatsapp ?: '—' }}</strong>
                            </div>

                            <div class="info-row">
                                <small>Penghasilan</small>
                                <strong>Rp {{ number_format((float) $penerima->penghasilan_bulanan, 0, ',', '.') }}</strong>
                            </div>

                            <div class="info-row">
                                <small>Tanggungan</small>
                                <strong>{{ $penerima->jumlah_tanggungan }} orang</strong>
                            </div>

                            <div class="info-row">
                                <small>Kondisi rumah</small>
                                <strong>
                                    {{ [
                                        'permanen' => 'Permanen',
                                        'semi_permanen' => 'Semi permanen',
                                        'tidak_layak' => 'Tidak layak',
                                    ][$penerima->kondisi_rumah] ?? '-' }}
                                </strong>
                            </div>
                        </div>

                        @if ($menunggu > 0)
                            <div class="alert alert-warning" style="margin:14px 0 0;">
                                <x-icon name="clock" :size="14" />
                                <div style="font-size:11.5px;">
                                    {{ $menunggu }} usulan masih menunggu keputusan admin.
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                <div class="card">
                    <div class="card-head">
                        <div>
                            <h3>Usulan sebelumnya</h3>
                            <p>{{ $items->count() }} riwayat usulan</p>
                        </div>
                    </div>

                    <div class="card-body" style="padding-top:6px;padding-bottom:6px;">

                        @forelse ($items->take(4) as $item)
                            <div class="mini-row">
                                <span class="ic">
                                    <x-icon name="edit" :size="14" />
                                </span>

                                <div>
                                    <b>
                                        {{ $item->created_at?->format('d M Y') }}
                                    </b>

                                    <small>
                                        @php
                                            $kunci = collect(array_keys($item->data_usulan ?? []))
                                                ->reject(fn ($k) => $k === 'alasan')
                                                ->map(fn ($k) => $labelUsulan[$k] ?? $k)
                                                ->take(2)
                                                ->implode(', ');
                                        @endphp

                                        {{ $kunci ?: 'Tanpa perubahan' }}
                                    </small>
                                </div>

                                <span class="badge {{ $item->status === 'menunggu' ? 'badge-amber' : ($item->status === 'disetujui' ? 'badge-green' : 'badge-red') }}"
                                      style="margin-left:auto;flex:none;">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </div>
                        @empty
                            <div class="empty" style="padding:24px 12px;">
                                <strong>Belum ada usulan</strong>
                                <p style="font-size:11.5px;">Usulan perubahan akan tampil di sini.</p>
                            </div>
                        @endforelse

                    </div>
                </div>

            </aside>

        </div>
    @endif

@endsection
