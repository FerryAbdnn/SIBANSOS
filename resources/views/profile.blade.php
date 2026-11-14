@extends(auth()->user()->peran === 'admin' ? 'layouts.admin' : 'layouts.masyarakat')

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

        $wilayahUser = $u->id_wilayah
            ? \App\Models\Wilayah::find($u->id_wilayah)
            : null;

        $jumlahPenerima = \App\Models\Penerima::when(
            $u->id_wilayah,
            fn ($q) => $q->whereIn('id_wilayah', \App\Support\WilayahScope::ids((int) $u->id_wilayah))
        )->count();

        $aktivitas = \App\Models\LogAktivitas::where('id_user', $u->id_user)
            ->select('aksi', \Illuminate\Support\Facades\DB::raw('COUNT(*) as jumlah'))
            ->groupBy('aksi')
            ->orderByDesc('jumlah')
            ->take(5)
            ->get();
    @endphp

    <div class="page-head">
        <div class="page-head-text">
            <h1>Profil Saya</h1>
            <p>Informasi akun dan wilayah penugasan Anda</p>
        </div>
    </div>

    {{-- BANNER --}}
    <div class="card" style="margin-bottom:18px;">
        <div class="card-body" style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">

            <div class="detail-avatar" style="width:58px;height:58px;font-size:19px;">
                {{ $inisial ?: 'U' }}
            </div>

            <div>
                <div class="detail-name">
                    <h1 style="font-size:20px;">{{ $u->nama }}</h1>

                    <span class="badge badge-green">{{ ucfirst($u->peran) }}</span>
                </div>

                <div class="detail-meta">
                    <span>{{ $u->email }}</span>

                    @if ($wilayahUser)
                        <i></i>
                        <span>{{ $wilayahUser->label }}</span>
                    @endif

                    <i></i>
                    <span>Login terakhir {{ $u->updated_at?->diffForHumans() ?? '-' }}</span>
                </div>
            </div>

            <div class="detail-actions">
                <span class="badge badge-outline">
                    <x-icon name="lock" :size="12" />
                    Akun {{ $u->aktif ? 'aktif' : 'nonaktif' }}
                </span>
            </div>

        </div>
    </div>

    <div class="grid-2-even">

        <div class="stack">

            {{-- INFORMASI AKUN --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>Informasi akun</h3>
                        <p>Data identitas akun yang terdaftar pada sistem.</p>
                    </div>
                </div>

                <div class="card-body" style="padding-top:4px;padding-bottom:8px;">
                    <div class="info-list">
                        <div class="info-row">
                            <small>Nama lengkap</small>
                            <strong>{{ $u->nama }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Email</small>
                            <strong>{{ $u->email }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Peran</small>
                            <strong>{{ ucfirst($u->peran) }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Wilayah penugasan</small>
                            <strong>{{ $wilayahUser->label ?? 'Seluruh wilayah' }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Akun dibuat</small>
                            <strong>{{ $u->created_at?->translatedFormat('d F Y') ?? '-' }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Status akun</small>
                            <strong style="color:{{ $u->aktif ? '#2C5320' : '#8E3225' }};">
                                {{ $u->aktif ? 'Aktif' : 'Nonaktif' }}
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- WILAYAH --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>Wilayah penugasan</h3>
                        <p>Akses data dibatasi pada wilayah ini.</p>
                    </div>
                </div>

                <div class="card-body" style="padding-top:4px;padding-bottom:8px;">
                    <div class="info-list">
                        <div class="info-row">
                            <small>Desa / Kelurahan</small>
                            <strong>{{ $wilayahUser->label ?? '—' }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Tipe wilayah</small>
                            <strong>{{ $wilayahUser ? strtoupper($wilayahUser->tipe) : '—' }}</strong>
                        </div>

                        <div class="info-row">
                            <small>Penerima terdata</small>
                            <strong>{{ number_format($jumlahPenerima, 0, ',', '.') }} orang</strong>
                        </div>
                    </div>

                    <div class="alert alert-info" style="margin:14px 0 0;">
                        <x-icon name="shield" :size="14" />
                        <div style="font-size:11.5px;">
                            Anda hanya dapat melihat dan memverifikasi data penerima pada wilayah
                            penugasan. Perubahan wilayah dilakukan oleh administrator sistem.
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="stack">

            {{-- KEAMANAN --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>Keamanan</h3>
                        <p>Informasi sesi dan perlindungan akun.</p>
                    </div>
                </div>

                <div class="card-body" style="padding-top:4px;padding-bottom:8px;">
                    <div class="info-list">
                        <div class="info-row">
                            <small>Kata sandi</small>
                            <strong>Disimpan sebagai hash</strong>
                        </div>

                        <div class="info-row">
                            <small>Algoritma</small>
                            <strong>Bcrypt</strong>
                        </div>

                        <div class="info-row">
                            <small>Sesi</small>
                            <strong>Kedaluwarsa otomatis</strong>
                        </div>

                        <div class="info-row">
                            <small>Login terakhir</small>
                            <strong>{{ $u->updated_at?->format('d M Y H:i') ?? '-' }}</strong>
                        </div>
                    </div>

                    <div class="verify-note" style="margin-top:14px;">
                        <span class="ic"><x-icon name="lock" :size="14" /></span>

                        <div>
                            <b>Kata sandi tidak pernah disimpan sebagai teks biasa</b>
                            <p>Untuk mengganti kata sandi, hubungi administrator sistem desa.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- AKTIVITAS --}}
            <div class="card">
                <div class="card-head">
                    <div>
                        <h3>Aktivitas saya</h3>
                        <p>Rekap tindakan yang tercatat pada log.</p>
                    </div>
                </div>

                <div class="card-body" style="padding-top:6px;padding-bottom:6px;">
                    @forelse ($aktivitas as $a)
                        <div class="mini-row">
                            <span class="ic">
                                <x-icon name="activity" :size="14" />
                            </span>

                            <div>
                                <b>{{ ucfirst(str_replace('_', ' ', $a->aksi)) }}</b>
                                <small>Tercatat pada log aktivitas</small>
                            </div>

                            <strong>{{ $a->jumlah }}</strong>
                        </div>
                    @empty
                        <div class="empty" style="padding:24px 12px;">
                            <strong>Belum ada aktivitas</strong>
                            <p style="font-size:11.5px;">Tindakan Anda akan tercatat di sini.</p>
                        </div>
                    @endforelse
                </div>

                @if ($u->peran === 'admin')
                    <div class="card-foot">
                        <a href="{{ route('admin.perbaikan.index') }}" class="link-quiet">
                            Lihat perbaikan data
                            <x-icon name="arrow-right" :size="13" />
                        </a>
                    </div>
                @endif
            </div>

        </div>

    </div>

@endsection
