@extends('layouts.admin')

@section('title', 'Riwayat Penyaluran')
@section('heading', 'Riwayat Penyaluran')

@section('content')

    @php
        $totalNominal = $items->sum(fn ($i) => (float) ($i->jumlah ?? 0));

        $bulanIni = \App\Models\RiwayatBantuan::whereYear('tanggal_terima', now()->year)
            ->whereMonth('tanggal_terima', now()->month)
            ->count();

        $nominalBulanIni = \App\Models\RiwayatBantuan::whereYear('tanggal_terima', now()->year)
            ->whereMonth('tanggal_terima', now()->month)
            ->sum('jumlah');
    @endphp

    {{-- HEADER --}}
    <div class="page-head">
        <div class="page-head-text">
            <h1>Riwayat Penyaluran</h1>

            <p>Seluruh rekap jejak bantuan &middot; tidak dapat dihapus</p>
        </div>

        <div class="page-head-actions">
            <a href="{{ route('admin.riwayat.index') }}" class="btn btn-outline">
                <x-icon name="download" :size="15" />
                Ekspor Excel
            </a>

            <a href="#catat" class="btn btn-primary">
                <x-icon name="plus" :size="15" />
                Catat Penyaluran
            </a>
        </div>
    </div>

    {{-- KPI --}}
    <div class="kpi-grid">

        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Total penyaluran</span>
                <span class="kpi-icon"><x-icon name="history" :size="16" /></span>
            </div>

            <strong class="kpi-value">{{ number_format($items->total(), 0, ',', '.') }}</strong>
            <small class="kpi-caption">Catatan pada wilayah tugas</small>
        </div>

        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Nominal total</span>
                <span class="kpi-icon"><x-icon name="wallet" :size="16" /></span>
            </div>

            <strong class="kpi-value">Rp {{ number_format($totalNominal, 0, ',', '.') }}</strong>
            <small class="kpi-caption">Akumulasi seluruh penyaluran</small>
        </div>

        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Bulan ini</span>
                <span class="kpi-icon amber"><x-icon name="calendar" :size="16" /></span>
            </div>

            <strong class="kpi-value">{{ number_format($bulanIni, 0, ',', '.') }}</strong>
            <small class="kpi-caption">Rp {{ number_format($nominalBulanIni, 0, ',', '.') }}</small>
        </div>

        <div class="kpi">
            <div class="kpi-top">
                <span class="kpi-label">Siap dicatat</span>
                <span class="kpi-icon gray"><x-icon name="check-square" :size="16" /></span>
            </div>

            <strong class="kpi-value">{{ number_format($pengajuanList->count(), 0, ',', '.') }}</strong>
            <small class="kpi-caption">Pengajuan terverifikasi</small>
        </div>

    </div>

    {{-- TABEL --}}
    <div class="card" style="margin-bottom:18px;">

        <div class="card-head">
            <div>
                <h3>Rekam jejak penyaluran</h3>
                <p>Setiap bantuan yang telah dicatat petugas.</p>
            </div>

            <div class="card-head-actions">
                <span class="badge badge-outline">
                    {{ number_format($items->total(), 0, ',', '.') }} catatan
                </span>
            </div>
        </div>

        <div class="tbl-wrap">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Penerima</th>
                        <th>Kategori</th>
                        <th>Program</th>
                        <th class="num">Nominal</th>
                        <th>Petugas</th>
                        <th class="num">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($items as $i)
                        @php
                            $kodePenerima = $i->penerima
                                ? 'PNR-' . ($i->penerima->created_at?->format('Y') ?? date('Y'))
                                    . '-' . str_pad($i->penerima->id_penerima, 5, '0', STR_PAD_LEFT)
                                : '-';
                        @endphp

                        <tr>
                            <td>{{ $i->tanggal_terima?->format('d M Y') ?? '-' }}</td>

                            <td>
                                <div class="tbl-person">
                                    <b>{{ $i->penerima->nama ?? '-' }}</b>
                                    <small class="mono">{{ $kodePenerima }}</small>
                                </div>
                            </td>

                            <td>
                                <span class="badge badge-gray">
                                    {{ $i->jenis->kategori->nama ?? '-' }}
                                </span>
                            </td>

                            <td>{{ $i->jenis->nama_program ?? '-' }}</td>

                            <td class="num strong">
                                @if (!is_null($i->jumlah))
                                    Rp {{ number_format((float) $i->jumlah, 0, ',', '.') }}
                                @else
                                    &mdash;
                                @endif
                            </td>

                            <td class="muted">Petugas #{{ $i->dicatat_oleh }}</td>

                            <td>
                                <div class="row-actions">
                                    <a href="{{ route('admin.riwayat.edit', $i) }}"
                                       class="icon-action" title="Koreksi catatan">
                                        <x-icon name="edit" :size="14" />
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty">
                                    <div class="empty-icon"><x-icon name="history" :size="20" /></div>
                                    <strong>Belum ada riwayat bantuan</strong>
                                    <p>Catat penyaluran pertama melalui formulir di bawah.</p>
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
            Riwayat penyaluran bersifat append-only. Catatan tidak dapat dihapus agar jejak
            akuntabilitas tetap utuh &mdash; koreksi dilakukan dengan entri pembetulan.
        </div>

    </div>

    {{-- FORM CATAT --}}
    <div class="card" id="catat">

        <div class="card-head">
            <div>
                <h3>Catat penerimaan bantuan</h3>
                <p>Pilih pengajuan terverifikasi, lalu isi detail penyaluran.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.riwayat.store') }}">
            @csrf

            <div class="card-body">

                <div class="form-grid">

                    <div class="field span-2">
                        <label for="id_pengajuan">
                            Pengajuan terverifikasi <span class="req">*</span>
                        </label>

                        <select class="select" name="id_pengajuan" id="id_pengajuan" required>
                            <option value="">&mdash; Pilih pengajuan &mdash;</option>

                            @foreach ($pengajuanList as $a)
                                @php
                                    $snapshot = $a->data_penerima ?? [];

                                    $nama = data_get($snapshot, 'nama', $a->penerima->nama ?? '-');
                                @endphp

                                <option value="{{ $a->id_pengajuan }}"
                                    @selected(old('id_pengajuan') == $a->id_pengajuan)>
                                    {{ $nama }} &mdash;
                                    {{ $a->jenisBantuan->nama_program ?? '-' }}
                                    (PGA-{{ str_pad($a->id_pengajuan, 4, '0', STR_PAD_LEFT) }})
                                </option>
                            @endforeach
                        </select>

                        @if ($pengajuanList->isEmpty())
                            <small class="hint">
                                Belum ada pengajuan terverifikasi yang dapat dicatat penyalurannya.
                            </small>
                        @endif
                    </div>

                    <div class="field">
                        <label for="tanggal_terima">Tanggal diterima <span class="req">*</span></label>

                        <input class="input" type="date" id="tanggal_terima" name="tanggal_terima"
                               value="{{ old('tanggal_terima', now()->format('Y-m-d')) }}" required>
                    </div>

                    <div class="field">
                        <label for="jumlah">Jumlah / nilai</label>

                        <div class="input-prefix">
                            <span>Rp</span>

                            <input type="number" id="jumlah" name="jumlah" min="0" step="1000"
                                   value="{{ old('jumlah') }}" placeholder="750000">
                        </div>
                    </div>

                    <div class="field">
                        <label for="satuan">Satuan</label>

                        <input class="input" type="text" id="satuan" name="satuan"
                               value="{{ old('satuan') }}" placeholder="Rupiah, paket, kg">
                    </div>

                    <div class="field">
                        <label for="keterangan">Keterangan</label>

                        <input class="input" type="text" id="keterangan" name="keterangan"
                               value="{{ old('keterangan') }}" placeholder="Tahap IV / transfer bank">
                    </div>

                </div>

            </div>

            <div class="card-foot" style="justify-content:flex-end;">
                <button type="submit" class="btn btn-primary">
                    <x-icon name="check" :size="14" />
                    Simpan Riwayat
                </button>
            </div>

        </form>

    </div>

@endsection
