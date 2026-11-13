@extends('layouts.masyarakat')

@section('title', 'Profil Saya')

@section('content')

    <x-page-header title="Profil Saya"
        description="Informasi akun dan data diri yang terhubung dengan akun SIBANSOS Anda." />

    <div class="panel">
        <div class="panel-head">
            <div>
                <h3>Informasi Akun</h3>
                <p>Informasi login dan identitas akun Anda.</p>
            </div>
            <span class="avatar" style="width:48px;height:48px;font-size:18px;">
                {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
            </span>
        </div>

        <div class="detail-grid">
            <div>
                <small>Nama Akun</small>
                <b>{{ auth()->user()->nama }}</b>
            </div>

            <div>
                <small>Email</small>
                <b>{{ auth()->user()->email }}</b>
            </div>

            <div>
                <small>Peran</small>
                <b>{{ ucfirst(auth()->user()->peran) }}</b>
            </div>

            <div>
                <small>Status Akun</small>
                <span class="status {{ auth()->user()->aktif ? 'terverifikasi' : 'nonaktif' }}">
                    {{ auth()->user()->aktif ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
        </div>
    </div>

    <div class="panel">
        <h3>Data Penerima Bantuan</h3>
        <p class="muted">
            Data berikut terhubung dengan akun Anda dan digunakan dalam proses pendataan bantuan sosial.
        </p>

        @if ($p)

            <div class="detail-grid">

                <div>
                    <small>Nama Lengkap</small>
                    <b>{{ $p->nama }}</b>
                </div>

                <div>
                    <small>NIK</small>
                    <b>••••••••••••{{ substr(decrypt($p->nik), -4) }}</b>
                </div>

                <div>
                    <small>Nomor Kartu Keluarga</small>
                    <b>
                        @if ($p->no_kk)
                            ••••••••••••{{ substr(decrypt($p->no_kk), -4) }}
                        @else
                            Belum tersedia
                        @endif
                    </b>
                </div>

                <div>
                    <small>Tanggal Lahir</small>
                    <b>{{ $p->tanggal_lahir?->format('d/m/Y') ?? '-' }}</b>
                </div>

                <div>
                    <small>Jenis Kelamin</small>
                    <b>{{ $p->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</b>
                </div>

                <div>
                    <small>Wilayah</small>
                    <b>{{ $p->wilayah?->nama ?? '-' }}</b>
                </div>

                <div class="span-2">
                    <small>Alamat</small>
                    <b>{{ $p->alamat }}</b>
                </div>

                <div>
                    <small>Penghasilan Bulanan</small>
                    <b>Rp {{ number_format((float) $p->penghasilan_bulanan, 0, ',', '.') }}</b>
                </div>

                <div>
                    <small>Jumlah Tanggungan</small>
                    <b>{{ $p->jumlah_tanggungan }} orang</b>
                </div>

                <div>
                    <small>Kondisi Rumah</small>
                    <b>{{ ucwords(str_replace('_', ' ', $p->kondisi_rumah)) }}</b>
                </div>

                <div>
                    <small>Status Pendataan</small>
                    <span class="status {{ $p->status }}">
                        {{ ucfirst($p->status) }}
                    </span>
                </div>

                <div>
                    <small>Disabilitas</small>
                    <b>{{ $p->disabilitas ? 'Ya' : 'Tidak' }}</b>
                </div>

                <div>
                    <small>Lansia</small>
                    <b>{{ $p->lansia ? 'Ya' : 'Tidak' }}</b>
                </div>

            </div>

            <div class="alert" style="background:#fff8e8;color:#85651b;">
                Untuk mengubah alamat, penghasilan, jumlah tanggungan, atau kondisi rumah,
                silakan gunakan menu Pengajuan Perbaikan Data.
            </div>

            <a href="{{ route('masyarakat.perbaikan.create') }}" class="btn primary">
                Ajukan Perbaikan Data
            </a>

        @else

            <div class="empty">
                <p>Belum ada data penerima yang terhubung dengan akun Anda.</p>

                <a href="{{ route('masyarakat.pengajuan.create') }}" class="btn primary">
                    Ajukan Bantuan
                </a>
            </div>

        @endif
    </div>

@endsection