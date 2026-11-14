@extends('layouts.masyarakat')

@section('title', 'Profil Saya')
@section('heading', 'Profil Saya')

@section('content')

    @php
        $u = auth()->user();

        $inisial = collect(explode(' ', trim($u->nama)))
            ->filter()
            ->take(2)
            ->map(fn ($k) => strtoupper(substr($k, 0, 1)))
            ->implode('');

        $nikWarga = $p?->nik_decrypted;

        $rtWarga = $p?->wilayah;
        $rwWarga = $rtWarga && $rtWarga->id_induk
            ? \App\Models\Wilayah::find($rtWarga->id_induk)
            : null;

        $kategoriAktif = $p
            ? $p->riwayat
                ->map(fn ($r) => optional(optional($r->jenis)->kategori)->nama)
                ->filter()
                ->first()
            : null;
    @endphp

    <div class="page-head">
        <div class="page-head-text">
            <h1>Profil Saya</h1>
            <p>Informasi akun dan data diri yang terhubung dengan SIBANSOS</p>
        </div>
    </div>

    {{-- BANNER --}}
    <div class="card" style="margin-bottom:18px;">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">

            <div class="detail-avatar" style="width:58px;height:58px;font-size:19px;">
                {{ $inisial ?: 'W' }}
            </div>

            <div>
                <div class="detail-name">
                    <h1 style="font-size:20px;">{{ $u->nama }}</h1>

                    <span class="badge {{ $p ? 'badge-green' : 'badge-gray' }}">
                        {{ $kategoriAktif ? 'Penerima ' . $kategoriAktif : 'Masyarakat' }}
                    </span>
                </div>

                <div class="detail-meta">
                    <span>{{ $u->email }}</span>

                    @if ($rtWarga)
                        <i></i>
                        <span>{{ $rtWarga->label }} / {{ $rwWarga->label ?? '-' }}</span>
                    @endif

                    <i></i>
                    <span>Bergabung {{ $u->created_at?->translatedFormat('M Y') ?? '-' }}</span>
                </div>
            </div>

            <div class="detail-actions">
                <a href="{{ route('masyarakat.perbaikan.create') }}" class="btn btn-outline">
                    <x-icon name="edit" :size="14" />
                    Perbaiki Data
                </a>
            </div>

        </div>
    </div>

    <div class="grid-2-even">

        <div class="stack">

            {{-- AKUN --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>Informasi akun</h3>
                        <p>Informasi login yang Anda gunakan.</p>
                    </div>
                </div>

                <div class="card-body" style="padding-top:4px;padding-bottom:8px;">
                    <div class="info-list">
                        <div class="info-row">
                            <small>Nama akun</small>
                            <strong>{{ $u->nama }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Email</small>
                            <strong>{{ $u->email }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Peran</small>
                            <strong>Masyarakat</strong>
                        </div>

                        <div class="info-row">
                            <small>Status akun</small>
                            <strong style="color:{{ $u->aktif ? '#2C5320' : '#8E3225' }};">
                                {{ $u->aktif ? 'Aktif' : 'Nonaktif' }}
                            </strong>
                        </div>

                        <div class="info-row">
                            <small>Terdaftar sejak</small>
                            <strong>{{ $u->created_at?->translatedFormat('d F Y') ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DATA PENERIMA --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>Data penerima bantuan</h3>
                        <p>Data yang terhubung dengan akun Anda.</p>
                    </div>

                    <div class="card-head-actions">
                        <span class="badge {{ $p ? 'badge-green' : 'badge-gray' }}">
                            {{ $p ? ([
                                'diajukan' => 'Menunggu',
                                'terverifikasi' => 'Terverifikasi',
                                'ditolak' => 'Ditolak',
                                'nonaktif' => 'Nonaktif',
                            ][$p->status] ?? ucfirst($p->status)) : 'Belum terdaftar' }}
                        </span>
                    </div>
                </div>

                <div class="card-body" style="padding-top:4px;padding-bottom:8px;">
                    @if ($p)
                        <div class="info-list">
                            <div class="info-row">
                                <small>Nama lengkap</small>
                                <strong>{{ $p->nama }}</strong>
                            </div>

                            <div class="info-row">
                                <small>NIK</small>
                                <strong class="mono">
                                    {{ $nikWarga ? substr($nikWarga, 0, 4) . '••••••' . substr($nikWarga, -4) : 'Tidak dapat dibaca' }}
                                </strong>
                            </div>

                            <div class="info-row">
                                <small>Nomor KK</small>
                                <strong class="mono">
                                    {{ $p->no_kk_decrypted ? substr($p->no_kk_decrypted, 0, 4) . '••••' . substr($p->no_kk_decrypted, -4) : '—' }}
                                </strong>
                            </div>

                            <div class="info-row">
                                <small>Tanggal lahir</small>
                                <strong>{{ $p->tanggal_lahir?->translatedFormat('d F Y') ?? '-' }}</strong>
                            </div>

                            <div class="info-row">
                                <small>Alamat</small>
                                <strong>{{ $p->alamat }}</strong>
                            </div>

                            <div class="info-row">
                                <small>Penghasilan</small>
                                <strong>Rp {{ number_format((float) $p->penghasilan_bulanan, 0, ',', '.') }}</strong>
                            </div>

                            <div class="info-row">
                                <small>Tanggungan</small>
                                <strong>{{ $p->jumlah_tanggungan }} orang</strong>
                            </div>
                        </div>
                    @else
                        <div class="empty" style="padding:26px 12px;">
                            <strong>Data penerima belum tersedia</strong>
                            <p>Ajukan bantuan agar data Anda tercatat pada sistem.</p>

                            <a href="{{ route('masyarakat.pengajuan.create') }}" class="btn btn-primary">
                                Ajukan Bantuan
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <div class="stack">

            {{-- KEAMANAN --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>Keamanan</h3>
                        <p>Perlindungan data pribadi Anda.</p>
                    </div>
                </div>

                <div class="card-body">
                    <div class="verify-note">
                        <span class="ic"><x-icon name="lock" :size="14" /></span>

                        <div>
                            <b>NIK dan nomor KK disimpan terenkripsi</b>
                            <p>
                                Data identitas hanya dapat dilihat petugas berwenang pada wilayah
                                penugasan Anda, sesuai UU No. 27 Tahun 2022.
                            </p>
                        </div>
                    </div>

                    <div class="info-list" style="margin-top:14px;">
                        <div class="info-row">
                            <small>Kata sandi</small>
                            <strong>Disimpan sebagai hash</strong>
                        </div>

                        <div class="info-row">
                            <small>Login terakhir</small>
                            <strong>{{ $u->updated_at?->format('d M Y H:i') ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- AKSES CEPAT --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>Akses cepat</h3>
                    </div>
                </div>

                <div class="card-body" style="display:grid;gap:8px;">
                    <a href="{{ route('masyarakat.status') }}" class="btn btn-outline btn-block" style="justify-content:flex-start;">
                        <x-icon name="file" :size="14" />
                        Status pengajuan
                    </a>

                    <a href="{{ route('masyarakat.riwayat') }}" class="btn btn-outline btn-block" style="justify-content:flex-start;">
                        <x-icon name="history" :size="14" />
                        Riwayat bantuan
                    </a>

                    <a href="{{ route('masyarakat.perbaikan.create') }}" class="btn btn-outline btn-block" style="justify-content:flex-start;">
                        <x-icon name="edit" :size="14" />
                        Perbaikan data
                    </a>
                </div>
            </div>

        </div>

    </div>

@endsection
