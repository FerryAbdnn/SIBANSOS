
@extends('layouts.admin')

@section('title', 'Verifikasi Pengajuan')
@section('heading', 'Verifikasi Pengajuan')

@section('content')
    <div class="panel">
        <h3>Pengajuan menunggu verifikasi</h3>
        <p class="muted">
            Periksa data dan kelengkapan dokumen sebelum menyetujui atau menolak pengajuan.
        </p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Program Bantuan</th>
                        <th>Tanggal lahir</th>
                        <th>Alamat</th>
                        <th>Tanggal pengajuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($pengajuan as $p)
                        @php
                            $snapshot = $p->data_penerima ?? [];
                            $penerima = $p->penerima;
                        @endphp

                        <tr>
                            <td>
                                <b>{{ $penerima->nama ?? '-' }}</b>
                            </td>

                            <td>
                                {{ $p->jenisBantuan->nama_program ?? '-' }}
                            </td>

                            <td>
                                {{ $penerima && $penerima->tanggal_lahir
                                    ? $penerima->tanggal_lahir->format('d/m/Y')
                                    : '-' }}
                            </td>

                            <td>
                                {{ $snapshot['alamat'] ?? $penerima->alamat ?? '-' }}
                            </td>

                            <td>
                                {{ $p->created_at
                                    ? $p->created_at->format('d/m/Y')
                                    : '-' }}
                            </td>

                            <td>
                                <form method="POST"
                                    action="{{ route('admin.pengajuan.verifikasi', $p->id_pengajuan) }}"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menyetujui pengajuan ini?')">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="btn primary small">
                                        Setujui
                                    </button>
                                </form>

                                <form method="POST"
                                    action="{{ route('admin.pengajuan.tolak', $p->id_pengajuan) }}">
                                    @csrf
                                    @method('PATCH')

                                    <input
                                        type="text"
                                        name="catatan"
                                        placeholder="Alasan penolakan"
                                        required
                                    >

                                    <button type="submit" class="btn danger-btn small">
                                        Tolak
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                Tidak ada pengajuan baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$pengajuan" />
    </div>
@endsection