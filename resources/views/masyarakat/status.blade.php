
@extends('layouts.masyarakat')

@section('title', 'Status Pengajuan')
@section('heading', 'Status Pengajuan Saya')

@section('content')
    <div class="panel">
        @if($p)
            <h3>{{ $p->nama }}</h3>

            <h3>Riwayat pengajuan bantuan</h3>

            @forelse($p->pengajuanBantuan->sortByDesc('created_at') as $a)
                @php
                    $snapshot = $a->data_penerima ?? [];
                @endphp

                <div class="panel">
                    <p>
                        <strong>Program:</strong>
                        {{ $a->jenisBantuan->nama_program ?? '-' }}
                    </p>

                    <p>
                        <strong>Status:</strong>
                        <span class="status {{ $a->status }}">
                            {{ ucfirst($a->status) }}
                        </span>
                    </p>

                    <p>
                        <strong>Tanggal pengajuan:</strong>
                        {{ $a->created_at?->format('d/m/Y H:i') ?? '-' }}
                    </p>

                    <p>
                        <strong>Alamat saat pengajuan:</strong>
                        {{ $snapshot['alamat'] ?? $p->alamat }}
                    </p>

                    @if($a->tanggal_verifikasi)
                        <p>
                            <strong>Tanggal diproses:</strong>
                            {{ $a->tanggal_verifikasi->format('d/m/Y H:i') }}
                        </p>
                    @endif

                    @if($a->catatan_verifikasi)
                        <p>
                            <strong>Catatan admin:</strong>
                            {{ $a->catatan_verifikasi }}
                        </p>
                    @endif
                </div>
            @empty
                <p class="muted">
                    Belum ada riwayat pengajuan bantuan.
                </p>
            @endforelse

            <hr>

            <h3>Riwayat bantuan diterima</h3>

            @forelse($p->riwayat as $r)
                <p>
                    {{ $r->jenis->nama_program ?? '-' }}
                    ·
                    {{ $r->tanggal_terima
                        ? $r->tanggal_terima->format('d/m/Y')
                        : '-' }}
                </p>
            @empty
                <p class="muted">
                    Belum ada riwayat bantuan yang diterima.
                </p>
            @endforelse

            <hr>

            <h3>Ajukan perbaikan data</h3>

            <form method="POST"
                action="{{ route('masyarakat.perbaikan.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="span-2">
                        <label>Alamat baru (opsional)</label>
                        <textarea name="alamat"></textarea>
                    </div>

                    <div>
                        <label>Penghasilan bulanan baru</label>
                        <input type="number"
                            name="penghasilan_bulanan"
                            min="0">
                    </div>

                    <div>
                        <label>Jumlah tanggungan baru</label>
                        <input type="number"
                            name="jumlah_tanggungan"
                            min="0">
                    </div>

                    <div>
                        <label>Kondisi rumah baru</label>
                        <select name="kondisi_rumah">
                            <option value="">Tidak diubah</option>
                            <option value="permanen">Permanen</option>
                            <option value="semi_permanen">Semi permanen</option>
                            <option value="tidak_layak">Tidak layak</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn primary">
                    Kirim perbaikan
                </button>
            </form>
        @else
            <p>
                Belum ada data pengajuan yang terhubung dengan akun ini.
                <a href="{{ route('masyarakat.pengajuan.create') }}">
                    Ajukan bantuan
                </a>.
            </p>
        @endif
    </div>
@endsection
