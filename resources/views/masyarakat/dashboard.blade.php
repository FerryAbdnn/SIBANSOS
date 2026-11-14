@extends('layouts.masyarakat')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')

    @php
        $riwayat = $p ? $p->riwayat->sortByDesc('tanggal_terima') : collect();

        $totalBantuan = $riwayat->sum(fn ($r) => (float) ($r->jumlah ?? 0));

        $terakhir = $riwayat->first();

        $kategoriAktif = $riwayat
            ->map(fn ($r) => optional(optional($r->jenis)->kategori)->nama)
            ->filter()
            ->first();

        $pengajuanAktif = $p
            ? $p->pengajuanBantuan->whereIn('status', ['diajukan'])->count()
            : 0;

        $statusBadge = [
            'diajukan' => 'badge-amber',
            'terverifikasi' => 'badge-green',
            'ditolak' => 'badge-red',
            'nonaktif' => 'badge-gray',
        ];

        $statusLabel = [
            'diajukan' => 'Menunggu Verifikasi',
            'terverifikasi' => 'Terverifikasi',
            'ditolak' => 'Ditolak',
            'nonaktif' => 'Nonaktif',
        ];

        $nikWarga = $p?->nik_decrypted;

        $rtWarga = $p?->wilayah;
        $rwWarga = $rtWarga && $rtWarga->id_induk
            ? \App\Models\Wilayah::find($rtWarga->id_induk)
            : null;

        $perbaikanTerakhir = $p
            ? $p->pengajuanPerbaikan()->latest()->first()
            : null;
    @endphp

    {{-- HEADER --}}
    <div class="page-head">
        <div class="page-head-text">
            <h1>Status Pendaftaran</h1>
            <p>Ringkasan status pendaftaran dan bantuan Anda</p>
        </div>

        <div class="page-head-actions">
            <a href="{{ route('masyarakat.perbaikan.create') }}" class="btn btn-outline">
                <x-icon name="edit" :size="14" />
                Perbaiki Data
            </a>

            <a href="{{ route('masyarakat.pengajuan.create') }}" class="btn btn-primary">
                <x-icon name="send" :size="14" />
                Ajukan Bantuan
            </a>
        </div>
    </div>

    {{-- HERO --}}
    <div class="warga-hero">

        @if ($p)
            <span class="pill">
                <x-icon name="check" :size="12" />
                {{ $statusLabel[$p->status] ?? ucfirst($p->status) }}
                @if ($kategoriAktif)
                    &middot; {{ $kategoriAktif }}
                @endif
            </span>

            <h2>{{ $p->nama }}</h2>

            <p>
                NIK {{ $p->nik_masked }}
                &middot; {{ $rtWarga->label ?? '-' }} / {{ $rwWarga->label ?? '-' }}
                @if ($terakhir && $terakhir->jumlah)
                    &middot; Rp {{ number_format((float) $terakhir->jumlah, 0, ',', '.') }} terakhir
                @endif
            </p>
        @else
            <span class="pill">
                <x-icon name="info" :size="12" />
                Belum terdaftar
            </span>

            <h2>Halo, {{ auth()->user()->nama }}</h2>

            <p>
                Anda belum memiliki data penerima. Mulai dengan mengajukan bantuan
                agar petugas desa dapat memeriksa kelayakan Anda.
            </p>
        @endif

    </div>

    {{-- KPI --}}
    <div class="kpi-grid">

        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Bantuan terakhir</span>
                <span class="kpi-icon amber"><x-icon name="wallet" :size="16" /></span>
            </div>

            <strong class="kpi-value">
                @if ($terakhir && $terakhir->jumlah)
                    Rp {{ number_format((float) $terakhir->jumlah, 0, ',', '.') }}
                @else
                    &mdash;
                @endif
            </strong>

            <small class="kpi-caption">
                {{ $terakhir?->tanggal_terima?->format('d M Y') ?? 'Belum ada penyaluran' }}
            </small>
        </div>

        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Pengajuan aktif</span>
                <span class="kpi-icon"><x-icon name="file" :size="16" /></span>
            </div>

            <strong class="kpi-value">{{ $pengajuanAktif }}</strong>
            <small class="kpi-caption">
                {{ $pengajuanAktif > 0 ? 'Menunggu verifikasi petugas' : 'Tidak ada pengajuan berjalan' }}
            </small>
        </div>

        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Dokumen terunggah</span>
                <span class="kpi-icon"><x-icon name="upload" :size="16" /></span>
            </div>

            <strong class="kpi-value">{{ $p ? $p->dokumen->count() : 0 }}</strong>
            <small class="kpi-caption">KTP, KK, dan berkas pendukung</small>
        </div>

        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Data diperbarui</span>
                <span class="kpi-icon gray"><x-icon name="refresh" :size="16" /></span>
            </div>

            <strong class="kpi-value">{{ $p?->updated_at?->format('d M Y') ?? '—' }}</strong>
            <small class="kpi-caption">{{ $p?->updated_at?->diffForHumans() ?? 'Belum ada data' }}</small>
        </div>

    </div>

    <div class="grid-2">

        {{-- TIMELINE --}}
        <div class="card">
            <div class="card-head">
                <div>
                    <h3>Alur pengajuan Anda</h3>
                    <p>Riwayat tahapan sejak pendaftaran</p>
                </div>

                <div class="card-head-actions">
                    <span class="badge {{ $statusBadge[$p->status ?? 'diajukan'] ?? 'badge-gray' }}">
                        {{ $statusLabel[$p->status ?? 'diajukan'] ?? 'Belum terdaftar' }}
                    </span>
                </div>
            </div>

            <div class="card-body">

                @if ($p)
                    @php
                        $pengajuanTerakhir = $p->pengajuanBantuan->sortByDesc('created_at')->first();

                        $sudahVerifikasi = in_array($p->status, ['terverifikasi', 'nonaktif'], true);

                        $ditolak = $p->status === 'ditolak';
                    @endphp

                    <div class="timeline">

                        <div class="tl-item">
                            <span class="tl-node done"><x-icon name="check" :size="11" /></span>

                            <div class="tl-body">
                                <b>Pendaftaran dikirim</b>
                                <small>
                                    {{ $p->created_at?->translatedFormat('d M Y') }} &middot;
                                    {{ $p->created_at?->format('H:i') }} &middot; melalui portal warga
                                </small>
                            </div>
                        </div>

                        <div class="tl-item">
                            <span class="tl-node {{ $p->dokumen->count() > 0 ? 'done' : 'current' }}">
                                @if ($p->dokumen->count() > 0)
                                    <x-icon name="check" :size="11" />
                                @endif
                            </span>

                            <div class="tl-body">
                                <b>Dokumen diperiksa</b>
                                <small>
                                    {{ $p->dokumen->count() }} dokumen tersimpan pada sistem
                                </small>
                            </div>
                        </div>

                        <div class="tl-item">
                            <span class="tl-node {{ $sudahVerifikasi ? 'done' : ($ditolak ? 'done' : 'current') }}">
                                @if ($sudahVerifikasi || $ditolak)
                                    <x-icon name="{{ $ditolak ? 'x' : 'check' }}" :size="11" />
                                @endif
                            </span>

                            <div class="tl-body">
                                <b>Pemeriksaan petugas desa</b>

                                <small>
                                    @if ($p->tanggal_verifikasi)
                                        {{ \Carbon\Carbon::parse($p->tanggal_verifikasi)->translatedFormat('d M Y') }}
                                        &middot; oleh {{ $p->diverifikasi_oleh ? 'petugas desa' : 'sistem' }}
                                    @else
                                        Sedang berlangsung &middot; biasanya 3&ndash;7 hari kerja
                                    @endif
                                </small>
                            </div>
                        </div>

                        <div class="tl-item">
                            <span class="tl-node {{ $sudahVerifikasi ? 'done' : 'pending' }}">
                                @if ($sudahVerifikasi)
                                    <x-icon name="check" :size="11" />
                                @endif
                            </span>

                            <div class="tl-body">
                                <b>Penetapan kategori bantuan</b>
                                <small>
                                    {{ $kategoriAktif
                                        ? 'Ditetapkan pada kategori ' . $kategoriAktif
                                        : 'Menunggu hasil pemeriksaan petugas' }}
                                </small>
                            </div>
                        </div>

                        <div class="tl-item">
                            <span class="tl-node {{ $terakhir ? 'done' : 'pending' }}">
                                @if ($terakhir)
                                    <x-icon name="check" :size="11" />
                                @endif
                            </span>

                            <div class="tl-body">
                                <b>Penyaluran bantuan</b>
                                <small>
                                    {{ $terakhir
                                        ? $terakhir->tanggal_terima?->translatedFormat('d M Y') . ' · ' . $terakhir->jenis->nama_program
                                        : 'Menunggu jadwal penyaluran tahap berikutnya' }}
                                </small>
                            </div>
                        </div>

                        @if ($p->status === 'nonaktif')
                            <div class="tl-item">
                                <span class="tl-node pending">
                                    <x-icon name="x" :size="10" />
                                </span>

                                <div class="tl-body">
                                    <b>Status pendataan dinonaktifkan</b>
                                    <small>
                                        @if ($p->updated_at)
                                            {{ $p->updated_at->translatedFormat('d M Y') }} &middot;
                                        @endif
                                        Data Anda tidak aktif untuk penyaluran berikutnya.
                                        Hubungi petugas desa bila ingin diaktifkan kembali.
                                    </small>
                                </div>
                            </div>
                        @endif

                    </div>
                @else
                    <div class="empty">
                        <div class="empty-icon"><x-icon name="send" :size="20" /></div>
                        <strong>Belum ada pengajuan</strong>
                        <p>Ajukan bantuan untuk memulai proses pendataan dan verifikasi.</p>

                        <a href="{{ route('masyarakat.pengajuan.create') }}" class="btn btn-primary">
                            Ajukan Bantuan
                        </a>
                    </div>
                @endif

            </div>
        </div>

        {{-- KOLOM KANAN --}}
        <div class="stack">

            {{-- RIWAYAT --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>Riwayat bantuan</h3>
                        <p>
                            {{ $riwayat->count() }} penyaluran &middot;
                            total Rp {{ number_format($totalBantuan, 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="card-head-actions">
                        <a href="{{ route('masyarakat.riwayat') }}" class="link-quiet">
                            Semua
                            <x-icon name="arrow-right" :size="13" />
                        </a>
                    </div>
                </div>

                <div class="card-body" style="padding-top:6px;padding-bottom:6px;">

                    @forelse ($riwayat->take(3) as $r)
                        <div class="mini-row">
                            <span class="ic">
                                <x-icon name="{{ $r->satuan === 'paket' ? 'gift' : 'banknote' }}" :size="15" />
                            </span>

                            <div>
                                <b>{{ $r->jenis->nama_program ?? 'Bantuan' }}</b>
                                <small>
                                    {{ $r->tanggal_terima?->format('d M Y') }}
                                    {{ $r->keterangan ? '· ' . $r->keterangan : '' }}
                                </small>
                            </div>

                            <strong>
                                @if (!is_null($r->jumlah))
                                    Rp {{ number_format((float) $r->jumlah, 0, ',', '.') }}
                                @else
                                    &mdash;
                                @endif
                            </strong>
                        </div>
                    @empty
                        <div class="empty" style="padding:26px 12px;">
                            <strong>Belum ada riwayat bantuan</strong>
                            <p>Bantuan yang Anda terima akan tercatat di sini.</p>
                        </div>
                    @endforelse

                </div>
            </div>

            {{-- PERBAIKAN --}}
            @if ($perbaikanTerakhir)
                <div class="card">
                    <div class="card-body">
                        <div class="verify-note"
                             style="{{ $perbaikanTerakhir->status === 'menunggu' ? 'background:#FBF5E2;border-color:#F2E6BE;' : '' }}">
                            <span class="ic" style="{{ $perbaikanTerakhir->status === 'menunggu' ? 'background:#FBF3DC;color:#7A5B14;' : '' }}">
                                <x-icon name="edit" :size="14" />
                            </span>

                            <div>
                                <b style="{{ $perbaikanTerakhir->status === 'menunggu' ? 'color:#7A5B14;' : '' }}">
                                    @if ($perbaikanTerakhir->status === 'menunggu')
                                        Perbaikan data sedang ditinjau
                                    @elseif ($perbaikanTerakhir->status === 'disetujui')
                                        Perbaikan data disetujui
                                        @if ($perbaikanTerakhir->diproses_at)
                                            &middot; {{ $perbaikanTerakhir->diproses_at->translatedFormat('d M Y') }}
                                        @endif
                                    @else
                                        Perbaikan data ditolak
                                        @if ($perbaikanTerakhir->diproses_at)
                                            &middot; {{ $perbaikanTerakhir->diproses_at->translatedFormat('d M Y') }}
                                        @endif
                                    @endif
                                </b>

                                <p>
                                    Anda mengajukan perubahan pada
                                    {{ $perbaikanTerakhir->created_at?->translatedFormat('d M Y') }}.
                                    @if ($perbaikanTerakhir->status === 'menunggu')
                                        Menunggu keputusan admin.
                                    @elseif ($perbaikanTerakhir->status === 'disetujui')
                                        Perubahan sudah diterapkan pada data Anda.
                                        {{ $perbaikanTerakhir->catatan_admin }}
                                    @else
                                        Usulan tidak disetujui.
                                        {{ $perbaikanTerakhir->catatan_admin }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- BANTUAN --}}
            <div class="card">
                <div class="card-body">
                    <b style="font-size:13px;">Butuh bantuan?</b>

                    <p class="muted" style="font-size:12px;margin-top:4px;line-height:1.6;">
                        Jika proses melebihi 7 hari kerja, hubungi petugas desa
                        untuk menanyakan status pengajuan Anda.
                    </p>

                    <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap;">
                        <a href="{{ route('masyarakat.status') }}" class="btn btn-outline btn-sm">
                            <x-icon name="file" :size="13" />
                            Status pengajuan
                        </a>

                        <a href="https://wa.me/6281234567890" target="_blank" rel="noopener"
                           class="btn btn-outline btn-sm">
                            <x-icon name="phone" :size="13" />
                            Hubungi petugas
                        </a>
                    </div>
                </div>
            </div>

        </div>

    </div>

@endsection
