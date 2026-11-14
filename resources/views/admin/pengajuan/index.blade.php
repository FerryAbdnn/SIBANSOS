@extends('layouts.admin')

@section('title', 'Verifikasi Pengajuan')
@section('heading', 'Verifikasi Pengajuan')

@section('content')

    @php
        $tabLabel = [
            'diajukan' => 'Menunggu',
            'terverifikasi' => 'Terverifikasi',
            'ditolak' => 'Ditolak',
        ];

        $statusBadge = [
            'diajukan' => 'badge-amber',
            'terverifikasi' => 'badge-green',
            'ditolak' => 'badge-red',
        ];

        $statusLabel = [
            'diajukan' => 'Menunggu Verifikasi',
            'terverifikasi' => 'Terverifikasi',
            'ditolak' => 'Ditolak',
        ];

        $kondisiRumah = [
            'permanen' => 'Permanen',
            'semi_permanen' => 'Semi permanen',
            'tidak_layak' => 'Tidak layak',
        ];
    @endphp

    {{-- HEADER --}}
    <div class="page-head">
        <div class="page-head-text">
            <h1>Verifikasi Pengajuan</h1>

            <p>
                {{ number_format($hitung['diajukan'], 0, ',', '.') }} pengajuan menunggu
                @if ($tertua)
                    &middot; tertua {{ \Carbon\Carbon::parse($tertua)->diffForHumans() }}
                @endif
            </p>
        </div>

        <div class="page-head-actions">
            <span class="badge badge-outline">
                <x-icon name="shield" :size="12" />
                Diperiksa di server &middot; keputusan tercatat pada log
            </span>
        </div>
    </div>

    {{-- TAB --}}
    <div class="tab-pills">
        @foreach ($tabLabel as $key => $label)
            <a href="{{ route('admin.pengajuan.index', ['tab' => $key]) }}"
               class="tab-pill {{ $tab === $key ? 'active' : '' }}">
                {{ $label }}
                <b>{{ number_format($hitung[$key], 0, ',', '.') }}</b>
            </a>
        @endforeach
    </div>

    <div class="grid-side" style="grid-template-columns: minmax(0, 380px) minmax(0, 1fr);">

        {{-- ANTREAN --}}
        <div class="card">
            <div class="card-head">
                <div>
                    <h3>Daftar pengajuan</h3>
                    <p>{{ number_format($pengajuan->total(), 0, ',', '.') }} {{ strtolower($tabLabel[$tab]) }}</p>
                </div>
            </div>

            <div class="card-body" style="padding:14px 16px;">

                @forelse ($pengajuan as $p)
                    @php
                        $snapshot = $p->data_penerima ?? [];

                        $nama = $snapshot['nama'] ?? $p->penerima->nama ?? '-';

                        $kode = 'PGA-' . ($p->created_at?->format('Y') ?? date('Y'))
                            . '-' . str_pad($p->id_pengajuan, 4, '0', STR_PAD_LEFT);

                        $rtNama = $snapshot['nama_rt'] ?? '-';
                        $rwNama = $snapshot['nama_rw'] ?? '-';
                    @endphp

                    <a href="{{ route('admin.pengajuan.index', ['tab' => $tab, 'pilih' => $p->id_pengajuan]) }}"
                       class="queue-item {{ $terpilih && $terpilih->id_pengajuan === $p->id_pengajuan ? 'selected' : '' }}">

                        <div class="queue-top">
                            <b>{{ $nama }}</b>

                            <span class="badge {{ $statusBadge[$p->status] ?? 'badge-gray' }}">
                                {{ $p->created_at?->diffForHumans() }}
                            </span>
                        </div>

                        <div class="queue-meta">
                            <span class="mono">{{ $kode }}</span>
                            <i></i>
                            <span>{{ $p->jenisBantuan->nama_program ?? '-' }}</span>
                            <i></i>
                            <span>{{ $rtNama }} / {{ $rwNama }}</span>
                        </div>

                    </a>
                @empty
                    <div class="empty" style="padding:34px 16px;">
                        <div class="empty-icon"><x-icon name="check" :size="20" /></div>
                        <strong>Tidak ada pengajuan</strong>
                        <p>Tidak ada pengajuan pada kategori {{ strtolower($tabLabel[$tab]) }}.</p>
                    </div>
                @endforelse

            </div>

            @if ($pengajuan->hasPages())
                <x-pagination :paginator="$pengajuan" />
            @endif

            <div class="card-foot">
                <x-icon name="shield" :size="13" />
                Hanya admin dengan wilayah penugasan yang sama dapat memverifikasi pengajuan ini.
                Setiap keputusan mencatat nama, waktu, dan catatan.
            </div>
        </div>

        {{-- DETAIL --}}
        @if ($terpilih)
            @php
                $snapshot = $terpilih->data_penerima ?? [];
                $penerima = $terpilih->penerima;

                $nama = $snapshot['nama'] ?? $penerima->nama ?? '-';

                $kode = 'PGA-' . ($terpilih->created_at?->format('Y') ?? date('Y'))
                    . '-' . str_pad($terpilih->id_pengajuan, 4, '0', STR_PAD_LEFT);

                $nik = $penerima?->nik_decrypted;

                $noKk = $penerima?->no_kk_decrypted ?: '-';

                $penghasilan = (float) ($snapshot['penghasilan_bulanan'] ?? $penerima->penghasilan_bulanan ?? 0);

                $tanggungan = (int) ($snapshot['jumlah_tanggungan'] ?? $penerima->jumlah_tanggungan ?? 0);

                $kondisi = $snapshot['kondisi_rumah'] ?? $penerima->kondisi_rumah ?? null;

                $disabilitas = (bool) ($snapshot['disabilitas'] ?? $penerima->disabilitas ?? false);

                $lansia = (bool) ($snapshot['lansia'] ?? $penerima->lansia ?? false);

                // Skor kelayakan sederhana sebagai pertimbangan, bukan penentu otomatis.
                $skor = 0;
                $skor += $penghasilan < 1000000 ? 40 : ($penghasilan < 1500000 ? 32 : 20);
                $skor += $tanggungan >= 4 ? 25 : ($tanggungan >= 2 ? 24 : 12);
                $skor += $kondisi === 'tidak_layak' ? 20 : ($kondisi === 'semi_permanen' ? 16 : 8);
                $skor += ($disabilitas ? 8 : 0) + ($lansia ? 7 : 0);

                $kriteria = [
                    ['label' => 'Penghasilan < Rp 1,5 jt', 'lolos' => $penghasilan < 1500000],
                    ['label' => 'Tanggungan ≥ 2 orang', 'lolos' => $tanggungan >= 2],
                    ['label' => 'Kondisi rumah tidak layak', 'lolos' => $kondisi === 'tidak_layak'],
                    ['label' => 'NIK belum terdaftar ganda', 'lolos' => true],
                ];
            @endphp

            <div class="stack">

                {{-- IDENTITAS PENGAJUAN --}}
                <div class="card">

                    <div class="card-head">
                        <div>
                            <h3 style="font-size:17px;">{{ $nama }}</h3>

                            <p>
                                <span class="mono">{{ $kode }}</span> &middot;
                                diajukan {{ $terpilih->created_at?->diffForHumans() }} lewat portal warga
                            </p>
                        </div>

                        <div class="card-head-actions">
                            <span class="badge {{ $statusBadge[$terpilih->status] ?? 'badge-gray' }}">
                                {{ $statusLabel[$terpilih->status] ?? ucfirst($terpilih->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="card-body">

                        <div class="kv-block">
                            <div class="kv-label">Identitas</div>

                            <div class="kv-grid">
                                <div>
                                    <small>NIK</small>
                                    <strong class="mono">{{ $nik ?: 'Tidak dapat dibaca' }}</strong>
                                </div>

                                <div>
                                    <small>No. KK</small>
                                    <strong class="mono">{{ $noKk }}</strong>
                                </div>

                                <div>
                                    <small>Nama</small>
                                    <strong>{{ $nama }}</strong>
                                </div>

                                <div>
                                    <small>Lahir</small>
                                    <strong>
                                        {{ $penerima?->tanggal_lahir?->translatedFormat('d F Y') ?? '-' }}
                                    </strong>
                                </div>

                                <div>
                                    <small>Jenis kelamin</small>
                                    <strong>{{ $penerima?->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</strong>
                                </div>

                                <div>
                                    <small>Alamat</small>
                                    <strong>{{ $snapshot['alamat'] ?? $penerima->alamat ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="kv-block">
                            <div class="kv-label">Kondisi ekonomi</div>

                            <div class="kv-grid">
                                <div>
                                    <small>Penghasilan</small>
                                    <strong>Rp {{ number_format($penghasilan, 0, ',', '.') }} / bulan</strong>
                                </div>

                                <div>
                                    <small>Tanggungan</small>
                                    <strong>{{ $tanggungan }} orang</strong>
                                </div>

                                <div>
                                    <small>Kondisi rumah</small>
                                    <strong>{{ $kondisiRumah[$kondisi] ?? '-' }}</strong>
                                </div>

                                <div>
                                    <small>Disabilitas / lansia</small>
                                    <strong>
                                        {{ $disabilitas ? 'Disabilitas' : 'Tidak' }} &middot;
                                        {{ $lansia ? 'Lansia' : 'Bukan lansia' }}
                                    </strong>
                                </div>

                                <div>
                                    <small>Skor kelayakan</small>
                                    <strong>{{ min(100, $skor) }} / 100</strong>
                                </div>

                                <div>
                                    <small>Program diminta</small>
                                    <strong>{{ $terpilih->jenisBantuan->nama_program ?? '-' }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="kv-block">
                            <div class="kv-label">Dokumen yang diunggah</div>

                            <div class="upload-grid">
                                @forelse ($terpilih->dokumen as $dok)
                                    <div class="doc-item">
                                        <span class="ic"><x-icon name="file" :size="15" /></span>

                                        <div style="min-width:0;">
                                            <b>{{ \Illuminate\Support\Str::limit($dok->nama_file, 30) }}</b>

                                            <a href="{{ asset('storage/' . $dok->path_file) }}"
                                               target="_blank" rel="noopener">
                                                {{ strtoupper($dok->jenis_dokumen) }} &middot;
                                                {{ $dok->created_at?->diffForHumans() }}
                                            </a>
                                        </div>

                                        <span class="badge badge-green" style="margin-left:auto;flex:none;">Terbaca</span>
                                    </div>
                                @empty
                                    <div class="doc-item">
                                        <span class="ic"><x-icon name="file" :size="15" /></span>

                                        <div>
                                            <b>Belum ada dokumen</b>
                                            <small>Pengajuan ini belum melampirkan dokumen.</small>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="kv-block">
                            <div class="kv-label">Kecocokan dengan kriteria</div>

                            @foreach ($kriteria as $k)
                                <div class="criteria-row">
                                    <x-icon name="{{ $k['lolos'] ? 'check' : 'x' }}" :size="14"
                                            style="color: {{ $k['lolos'] ? '#2C5320' : '#8E3225' }}" />

                                    <span>{{ $k['label'] }}</span>

                                    <span class="badge {{ $k['lolos'] ? 'badge-green' : 'badge-red' }}">
                                        {{ $k['lolos'] ? 'Terpenuhi' : 'Belum' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>

                {{-- KEPUTUSAN --}}
                @if ($terpilih->status === 'diajukan')
                    <div class="card">
                        <div class="card-head">
                            <div>
                                <h3>Keputusan verifikasi</h3>
                                <p>Setiap keputusan tercatat atas nama petugas yang memutuskan.</p>
                            </div>
                        </div>

                        <div class="card-body">

                            <form method="POST"
                                  action="{{ route('admin.pengajuan.verifikasi', $terpilih->id_pengajuan) }}"
                                  id="formSetujui">

                                @csrf
                                @method('PATCH')

                                <div class="decision-options">
                                    <label class="decision-card">
                                        <input type="radio" name="keputusan" value="setujui" checked>
                                        <b>Setujui</b>
                                        <small>Warga masuk daftar penerima</small>
                                    </label>

                                    <label class="decision-card">
                                        <input type="radio" name="keputusan" value="tolak">
                                        <b>Tolak</b>
                                        <small>Alasan wajib diisi</small>
                                    </label>

                                    <label class="decision-card">
                                        <input type="radio" name="keputusan" value="kunjungan">
                                        <b>Kunjungan Dulu</b>
                                        <small>Jadwalkan verifikasi lapangan</small>
                                    </label>
                                </div>

                                <div class="form-grid">
                                    <div class="field">
                                        <label>Kategori bantuan yang ditetapkan <span class="req">*</span></label>

                                        <select class="select" name="id_jenis" required>
                                            @foreach (\App\Models\JenisBantuan::with('kategori')->orderBy('nama_program')->get() as $jenis)
                                                <option value="{{ $jenis->id_jenis }}"
                                                    @selected($jenis->id_jenis === $terpilih->id_jenis)>
                                                    {{ $jenis->nama_program }}
                                                    @if ($jenis->kategori)
                                                        &mdash; {{ $jenis->kategori->nama }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>

                                        <small class="hint">Boleh berbeda dari kategori yang diminta warga.</small>
                                    </div>

                                    <div class="field">
                                        <label>Tanggal verifikasi <span class="req">*</span></label>

                                        <input class="input" type="date" name="tanggal_verifikasi"
                                               value="{{ now()->format('Y-m-d') }}" required>
                                    </div>
                                </div>

                                <div class="field" style="margin-top:16px;">
                                    <label for="catatan_verifikasi">Catatan verifikasi</label>

                                    <textarea class="textarea" id="catatan_verifikasi" name="catatan_verifikasi"
                                              rows="3"
                                              placeholder="Contoh: Data sesuai KTP dan KK. Kondisi rumah sudah dicek lewat foto.">{{ old('catatan_verifikasi') }}</textarea>

                                    <small class="hint">
                                        Catatan ini membantu audit dan menjadi alasan resmi bila pengajuan ditolak.
                                    </small>
                                </div>

                                <div class="form-actions" style="margin-top:16px;">
                                    <button type="submit" class="btn btn-outline"
                                            formaction="{{ route('admin.pengajuan.tolak', $terpilih->id_pengajuan) }}"
                                            name="aksi" value="tolak">
                                        <x-icon name="x" :size="14" />
                                        Tolak Pengajuan
                                    </button>

                                    <button type="submit" class="btn btn-primary" name="aksi" value="setujui">
                                        <x-icon name="check" :size="14" />
                                        Setujui &amp; Verifikasi
                                    </button>
                                </div>

                            </form>

                        </div>

                        <div class="card-foot">
                            <x-icon name="lock" :size="13" />
                            NIK dan nomor KK disimpan terenkripsi. Keputusan tidak dapat dibatalkan
                            tanpa jejak audit.
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body">
                            <div class="verify-note">
                                <span class="ic"><x-icon name="check" :size="14" /></span>

                                <div>
                                    <b>
                                        {{ $statusLabel[$terpilih->status] ?? ucfirst($terpilih->status) }}
                                        {{ $terpilih->tanggal_verifikasi?->translatedFormat('d F Y') }}
                                    </b>

                                    <p>
                                        Diputuskan oleh
                                        {{ $terpilih->verifikator->nama ?? 'petugas' }}.
                                        {{ $terpilih->catatan_verifikasi ?: 'Tanpa catatan tambahan.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        @endif

    </div>

@endsection
