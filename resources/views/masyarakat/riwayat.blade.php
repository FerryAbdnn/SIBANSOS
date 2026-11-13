@extends('layouts.masyarakat')

@section('title', 'Riwayat Bantuan')

@section('content')

    <x-page-header title="Riwayat Bantuan Saya"
        description="Lihat bantuan sosial yang pernah Anda terima berdasarkan catatan penyaluran." />

    @if (!$penerima)

        <div class="panel">
            <div class="empty">
                <h3>Data penerima belum tersedia</h3>
                <p>Riwayat bantuan akan ditampilkan setelah data penerima terhubung dengan akun Anda.</p>

                <a href="{{ route('masyarakat.pengajuan.create') }}" class="btn primary">
                    Ajukan Bantuan
                </a>
            </div>
        </div>

    @else

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3>Informasi Penerima</h3>
                    <p>Ringkasan identitas penerima bantuan.</p>
                </div>

                <span class="status {{ $penerima->status }}">
                    {{ ucfirst($penerima->status) }}
                </span>
            </div>

            <div class="detail-grid">
                <div>
                    <small>Nama Penerima</small>
                    <b>{{ $penerima->nama }}</b>
                </div>

                <div>
                    <small>NIK</small>
                    <b>••••••••••••{{ substr(decrypt($penerima->nik), -4) }}</b>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3>Daftar Riwayat Penyaluran</h3>
                    <p>Daftar bantuan yang tercatat telah disalurkan kepada Anda.</p>
                </div>

                <span class="status terverifikasi">
                    {{ $items->total() }} Catatan
                </span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Program Bantuan</th>
                            <th>Kategori</th>
                            <th>Tanggal Diterima</th>
                            <th>Jumlah</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td>
                                    {{ $items->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <b>{{ $item->jenis?->nama_program ?? 'Program tidak tersedia' }}</b>
                                    <span class="sub">
                                        {{ $item->jenis?->penyelenggara ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->jenis?->kategori?->nama ?? '-' }}
                                </td>

                                <td>
                                    {{ $item->tanggal_terima?->format('d/m/Y') ?? '-' }}
                                </td>

                                <td>
                                    @if (!is_null($item->jumlah))
                                        {{ number_format((float) $item->jumlah, 0, ',', '.') }}
                                        {{ $item->satuan }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>
                                    {{ $item->keterangan ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty">
                                    Belum ada riwayat bantuan yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <x-pagination :paginator="$items" />
            @endif
        </div>

    @endif

@endsection