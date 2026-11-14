@extends('layouts.admin')

@section('title', 'Detail Penerima')
@section('heading', 'Data Penerima / Detail')

@section('content')

    @php
        $nik = $penerima->nik_decrypted;

        $noKk = $penerima->no_kk_decrypted ?: '-';

        $rt = $penerima->wilayah;
        $rw = $rt && $rt->id_induk ? $wilayahMap->get($rt->id_induk) : null;

        $kodePenerima = 'PNR-' . ($penerima->created_at?->format('Y') ?? date('Y'))
            . '-' . str_pad($penerima->id_penerima, 5, '0', STR_PAD_LEFT);

        $statusLabel = [
            'diajukan' => 'Menunggu Verifikasi',
            'terverifikasi' => 'Terverifikasi',
            'ditolak' => 'Ditolak',
            'nonaktif' => 'Nonaktif',
        ];

        $statusBadge = [
            'diajukan' => 'badge-amber',
            'terverifikasi' => 'badge-green',
            'ditolak' => 'badge-red',
            'nonaktif' => 'badge-gray',
        ];

        $jumlahRiwayat = $penerima->riwayat->count();

        $inisial = collect(explode(' ', trim($penerima->nama)))
            ->filter()
            ->take(2)
            ->map(fn ($k) => strtoupper(substr($k, 0, 1)))
            ->implode('');

        $kondisiRumah = [
            'permanen' => 'Permanen',
            'semi_permanen' => 'Semi permanen',
            'tidak_layak' => 'Tidak layak',
        ][$penerima->kondisi_rumah] ?? '-';
    @endphp

    {{-- HEADER --}}
    <div class="detail-head">

        <div class="detail-avatar">{{ $inisial ?: 'P' }}</div>

        <div>
            <div class="detail-name">
                <h1>{{ $penerima->nama }}</h1>

                <span class="badge {{ $statusBadge[$penerima->status] ?? 'badge-gray' }}">
                    {{ $statusLabel[$penerima->status] ?? ucfirst($penerima->status) }}
                </span>
            </div>

            <div class="detail-meta">
                <span class="mono">{{ $kodePenerima }}</span>
                <i></i>
                <span>NIK {{ $nik ?: 'tidak dapat dibaca' }}</span>
                <i></i>
                <span>{{ $rt->label ?? '-' }} / {{ $rw->label ?? '-' }}</span>
            </div>
        </div>

        <div class="detail-actions">
            <a href="{{ route('admin.penerima.index') }}" class="btn btn-outline">
                <x-icon name="chevron-right" :size="14" style="transform:rotate(180deg)" />
                Kembali
            </a>

            <a href="{{ route('admin.penerima.edit', $penerima) }}" class="btn btn-outline">
                <x-icon name="edit" :size="14" />
                Edit Data
            </a>

            <a href="{{ route('admin.riwayat.index') }}" class="btn btn-primary">
                <x-icon name="plus" :size="14" />
                Catat Penyaluran
            </a>
        </div>

    </div>

    {{-- RINGKASAN --}}
    <div class="summary-grid">

        <div class="summary-card">
            <span>Total Diterima</span>
            <strong>Rp {{ number_format($totalDiterima, 0, ',', '.') }}</strong>
            <small>{{ $jumlahRiwayat }} kali penyaluran</small>
        </div>

        <div class="summary-card">
            <span>Kategori Aktif</span>
            <strong>{{ $kategoriAktif->isNotEmpty() ? $kategoriAktif->first() : 'Belum ada' }}</strong>
            <small>{{ $kategoriAktif->count() }} kategori bantuan</small>
        </div>

        <div class="summary-card">
            <span>Terdaftar Sejak</span>
            <strong>{{ $penerima->created_at?->translatedFormat('M Y') ?? '-' }}</strong>
            <small>{{ $penerima->created_at?->diffForHumans() ?? '-' }}</small>
        </div>

        <div class="summary-card">
            <span>Tanggungan</span>
            <strong>{{ $penerima->jumlah_tanggungan ?? 0 }} orang</strong>
            <small>Anggota keluarga ditanggung</small>
        </div>

    </div>

    {{-- KONTEN --}}
    <div class="grid-side">

        <div class="stack">

            {{-- RIWAYAT --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>Riwayat Bantuan</h3>
                        <p>Rekam jejak penyaluran yang tidak dapat dihapus</p>
                    </div>

                    <div class="card-head-actions">
                        <span class="badge badge-outline">{{ $jumlahRiwayat }} tercatat</span>
                    </div>
                </div>

                <div class="tbl-wrap">
                    <table class="tbl">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kategori</th>
                                <th>Program</th>
                                <th class="num">Nominal</th>
                                <th>Petugas</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($penerima->riwayat->sortByDesc('tanggal_terima') as $r)
                                <tr>
                                    <td>{{ $r->tanggal_terima?->format('d M Y') ?? '-' }}</td>

                                    <td>
                                        <span class="badge badge-gray">
                                            {{ $r->jenis->kategori->nama ?? '-' }}
                                        </span>
                                    </td>

                                    <td class="strong">{{ $r->jenis->nama_program ?? '-' }}</td>

                                    <td class="num">
                                        @if (!is_null($r->jumlah))
                                            Rp {{ number_format((float) $r->jumlah, 0, ',', '.') }}
                                        @else
                                            &mdash;
                                        @endif
                                    </td>

                                    <td class="muted">Petugas #{{ $r->dicatat_oleh }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty" style="padding:28px 20px;">
                                            <strong>Belum ada riwayat penyaluran</strong>
                                            <p>Riwayat akan muncul setelah bantuan dicatat oleh petugas.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-foot">
                    <x-icon name="shield" :size="13" />
                    @if ($jumlahRiwayat > 0)
                        Data penerima ini tidak dapat dihapus karena sudah memiliki
                        {{ $jumlahRiwayat }} riwayat penyaluran.
                    @else
                        Data penerima ini belum memiliki riwayat penyaluran.
                    @endif
                </div>
            </div>

            {{-- DOKUMEN --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>Dokumen Pendukung</h3>
                        <p>Dokumen identitas yang tersimpan dalam sistem</p>
                    </div>

                    <div class="card-head-actions">
                        <span class="badge badge-outline">{{ $penerima->dokumen->count() }} dokumen</span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="upload-grid">
                        @foreach ([
                            ['label' => 'Kartu Tanda Penduduk', 'items' => $penerima->dokumen->where('jenis_dokumen', 'ktp')],
                            ['label' => 'Kartu Keluarga', 'items' => $penerima->dokumen->where('jenis_dokumen', 'kk')],
                        ] as $data)
                            <div class="doc-item">
                                <span class="ic"><x-icon name="file" :size="15" /></span>

                                <div style="min-width:0;">
                                    <b>{{ $data['label'] }}</b>

                                    @forelse ($data['items'] as $dokumen)
                                        <a href="{{ asset('storage/' . $dokumen->path_file) }}"
                                           target="_blank" rel="noopener">
                                            {{ $dokumen->nama_file }}
                                        </a>
                                    @empty
                                        <small>Belum ada dokumen</small>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($penerima->dokumen->whereNotIn('jenis_dokumen', ['ktp', 'kk'])->isNotEmpty())
                        <div class="tag-row" style="margin-top:14px;">
                            @foreach ($penerima->dokumen->whereNotIn('jenis_dokumen', ['ktp', 'kk']) as $dokumen)
                                <a href="{{ asset('storage/' . $dokumen->path_file) }}"
                                   target="_blank" rel="noopener" class="tag">
                                    {{ \Illuminate\Support\Str::limit($dokumen->nama_file, 34) }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <aside class="stack">

            {{-- IDENTITAS --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>Identitas &amp; Wilayah</h3>
                    </div>
                </div>

                <div class="card-body" style="padding-top:4px;padding-bottom:8px;">
                    <div class="info-list">
                        <div class="info-row">
                            <small>NIK</small>
                            <strong class="mono">{{ $nik ?: 'Tidak dapat dibaca' }}</strong>
                        </div>

                        <div class="info-row">
                            <small>No. KK</small>
                            <strong class="mono">{{ $noKk }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Nama Lengkap</small>
                            <strong>{{ $penerima->nama }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Tempat, Tanggal Lahir</small>
                            <strong>{{ $penerima->tanggal_lahir?->translatedFormat('d F Y') ?? '-' }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Jenis Kelamin</small>
                            <strong>{{ $penerima->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</strong>
                        </div>

                        <div class="info-row">
                            <small>RT / RW</small>
                            <strong>{{ $rt->label ?? '-' }} / {{ $rw->label ?? '-' }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Nomor WhatsApp</small>
                            <strong>{{ $penerima->nomor_whatsapp ?: '-' }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Alamat</small>
                            <strong>{{ $penerima->alamat }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KONDISI EKONOMI --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>Kondisi Ekonomi</h3>
                    </div>
                </div>

                <div class="card-body">
                    <div class="info-list">
                        <div class="info-row">
                            <small>Penghasilan</small>
                            <strong>Rp {{ number_format((float) $penerima->penghasilan_bulanan, 0, ',', '.') }} / bulan</strong>
                        </div>

                        <div class="info-row">
                            <small>Tanggungan</small>
                            <strong>{{ $penerima->jumlah_tanggungan }} orang</strong>
                        </div>

                        <div class="info-row">
                            <small>Kondisi rumah</small>
                            <strong>{{ $kondisiRumah }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Disabilitas</small>
                            <strong>{{ $penerima->disabilitas ? 'Ya' : 'Tidak' }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Lansia</small>
                            <strong>{{ $penerima->lansia ? 'Ya' : 'Tidak' }}</strong>
                        </div>
                    </div>

                    <div class="tag-row">
                        <span class="tag">
                            {{ $penerima->penghasilan_bulanan < 1000000 ? 'Penghasilan < Rp 1 jt' : 'Penghasilan ≥ Rp 1 jt' }}
                        </span>

                        <span class="tag">{{ $penerima->jumlah_tanggungan }} tanggungan</span>

                        @if ($penerima->lansia)
                            <span class="tag">Lansia</span>
                        @endif

                        @if ($penerima->disabilitas)
                            <span class="tag">Disabilitas</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- VERIFIKASI --}}
            <div class="card">
                <div class="card-body">
                    <div class="verify-note">
                        <span class="ic">
                            <x-icon name="{{ $penerima->status === 'terverifikasi' ? 'check' : 'info' }}" :size="14" />
                        </span>

                        <div>
                            <b>
                                @if ($penerima->tanggal_verifikasi)
                                    Diverifikasi {{ \Carbon\Carbon::parse($penerima->tanggal_verifikasi)->translatedFormat('d F Y') }}
                                @else
                                    {{ $statusLabel[$penerima->status] ?? ucfirst($penerima->status) }}
                                @endif
                            </b>

                            <p>
                                {{ $penerima->catatan_verifikasi
                                    ?: ($penerima->tanggal_verifikasi
                                        ? 'Data telah diperiksa dan disetujui petugas.'
                                        : 'Belum ada catatan verifikasi untuk data ini.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- AKSI --}}
            <div class="card">
                <div class="card-body" style="display:grid;gap:9px;">

                    @if ($penerima->status !== 'nonaktif')
                        <form method="POST" action="{{ route('admin.penerima.nonaktif', $penerima) }}"
                              onsubmit="return confirm('Nonaktifkan penerima ini? Riwayat tetap tersimpan.')">
                            @csrf
                            @method('PATCH')

                            <button type="submit" class="btn btn-outline btn-block">
                                <x-icon name="x" :size="14" />
                                Nonaktifkan Penerima
                            </button>
                        </form>
                    @endif

                    @if (!$penerima->riwayat()->exists() && !$penerima->dokumen()->exists() && !$penerima->pengajuanPerbaikan()->exists())
                        <form method="POST" action="{{ route('admin.penerima.destroy', $penerima) }}"
                              onsubmit="return confirm('Hapus data penerima ini secara permanen?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger btn-block">
                                <x-icon name="trash" :size="14" />
                                Hapus Data
                            </button>
                        </form>
                    @endif

                </div>
            </div>

        </aside>

    </div>

@endsection
