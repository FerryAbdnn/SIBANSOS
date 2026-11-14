@extends('layouts.admin')

@section('title', 'Perbaikan Data')
@section('heading', 'Perbaikan Data')

@section('content')

    @php
        $jumlahMenunggu = $items->where('status', 'menunggu')->count();
    @endphp

    <div class="page-head">
        <div class="page-head-text">
            <h1>Pengajuan Perbaikan Data</h1>

            <p>
                {{ number_format($items->total(), 0, ',', '.') }} usulan masuk &middot;
                {{ number_format($jumlahMenunggu, 0, ',', '.') }} menunggu keputusan
            </p>
        </div>
    </div>

    <div class="alert alert-info">
        <x-icon name="info" :size="16" />
        <div>
            Perubahan data baru berlaku setelah disetujui admin. Selama menunggu,
            data lama tetap dipakai pada seluruh perhitungan.
        </div>
    </div>

    <div class="card">

        <div class="card-head">
            <div>
                <h3>Permintaan perubahan dari masyarakat</h3>
                <p>Periksa usulan sebelum menyetujui atau menolak.</p>
            </div>

            <div class="card-head-actions">
                <span class="badge badge-amber">{{ $jumlahMenunggu }} menunggu</span>
            </div>
        </div>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Pemohon</th>
                        <th>Data yang Diusulkan</th>
                        <th>Waktu Pengajuan</th>
                        <th>Keputusan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($items as $i)
                        @php
                            $usulan = is_array($i->data_usulan) ? $i->data_usulan : [];

                            $label = [
                                'alamat' => 'Alamat',
                                'nomor_whatsapp' => 'No. WhatsApp',
                                'penghasilan_bulanan' => 'Penghasilan',
                                'jumlah_tanggungan' => 'Tanggungan',
                                'kondisi_rumah' => 'Kondisi rumah',
                                'alasan' => 'Alasan',
                            ];
                        @endphp

                        <tr>
                            <td>
                                <div class="tbl-person">
                                    <b>{{ $i->penerima->nama ?? 'Data tidak ditemukan' }}</b>
                                    <small>{{ $i->pengaju->email ?? '-' }}</small>
                                </div>
                            </td>

                            <td>
                                @if (empty($usulan))
                                    <span class="muted">&mdash;</span>
                                @else
                                    <div style="display:grid;gap:5px;">
                                        @foreach ($usulan as $kunci => $nilai)
                                            @if (filled($nilai))
                                                <div style="display:flex;gap:8px;font-size:11.5px;">
                                                    <span class="muted" style="min-width:96px;">
                                                        {{ $label[$kunci] ?? ucfirst(str_replace('_', ' ', $kunci)) }}
                                                    </span>

                                                    <strong style="font-weight:600;">
                                                        {{ \Illuminate\Support\Str::limit((string) $nilai, 60) }}
                                                    </strong>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>

                            <td class="muted">{{ $i->created_at?->format('d M Y H:i') ?? '-' }}</td>

                            <td>
                                @if ($i->status === 'menunggu')
                                    <form method="POST" action="{{ route('admin.perbaikan.proses', $i) }}"
                                          style="display:grid;gap:7px;min-width:210px;">
                                        @csrf
                                        @method('PATCH')

                                        <input class="input" type="text" name="catatan_admin"
                                               placeholder="Catatan admin (opsional)"
                                               value="{{ old('catatan_admin') }}"
                                               style="height:34px;font-size:12px;">

                                        <div style="display:flex;gap:6px;">
                                            <button type="submit" class="btn btn-primary btn-sm"
                                                    name="status" value="disetujui">
                                                <x-icon name="check" :size="13" />
                                                Setujui
                                            </button>

                                            <button type="submit" class="btn btn-danger btn-sm"
                                                    name="status" value="ditolak">
                                                Tolak
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <span class="badge {{ $i->status === 'disetujui' ? 'badge-green' : 'badge-red' }}">
                                        {{ ucfirst($i->status) }}
                                    </span>

                                    @if ($i->catatan_admin)
                                        <small class="sub">{{ $i->catatan_admin }}</small>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty">
                                    <div class="empty-icon"><x-icon name="edit" :size="20" /></div>
                                    <strong>Tidak ada permintaan perbaikan</strong>
                                    <p>Usulan perubahan data dari masyarakat akan muncul di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($items->hasPages())
            <x-pagination :paginator="$items" />
        @endif

        <div class="card-foot">
            <x-icon name="shield" :size="13" />
            Setiap keputusan perbaikan tercatat pada log aktivitas beserta nama petugas.
        </div>

    </div>

@endsection
