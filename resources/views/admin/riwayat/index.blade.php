
@extends('layouts.admin')

@section('title', 'Riwayat Bantuan')
@section('heading', 'Riwayat Bantuan')

@section('content')

    {{-- Form Catat Penyaluran --}}
    <div class="panel form-panel">
        <h3>Catat penerimaan bantuan</h3>

        <form method="POST" action="{{ route('admin.riwayat.store') }}">
            @csrf

            <div class="form-grid">
                <div class="span-2">
                    <label>Pengajuan terverifikasi (penerima dan program)</label>

                    <select name="id_pengajuan" required>
                        <option value="">-- Pilih pengajuan --</option>

                        @foreach($pengajuanList as $a)
                            @php
                                $snapshot = $a->data_penerima ?? [];
                                $nama = data_get(
                                    $snapshot,
                                    'nama',
                                    $a->penerima->nama ?? '-'
                                );
                            @endphp

                            <option
                                value="{{ $a->id_pengajuan }}"
                                @selected(old('id_pengajuan') == $a->id_pengajuan)
                            >
                                {{ $nama }}
                                — {{ $a->jenisBantuan->nama_program ?? '-' }}
                                (Pengajuan #{{ $a->id_pengajuan }})
                            </option>
                        @endforeach
                    </select>

                    @if($pengajuanList->isEmpty())
                        <p class="muted">
                            Belum ada pengajuan terverifikasi yang dapat dicatat penyalurannya.
                        </p>
                    @endif
                </div>


                <div>
                    <label>Tanggal diterima</label>
                    <input
                        type="date"
                        name="tanggal_terima"
                        value="{{ old('tanggal_terima') }}"
                        required
                    >
                </div>

                <div>
                    <label>Jumlah / nilai</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="jumlah"
                        value="{{ old('jumlah') }}"
                    >
                </div>

                <div>
                    <label>Satuan</label>
                    <input
                        name="satuan"
                        value="{{ old('satuan') }}"
                        placeholder="Rupiah, paket, kg"
                    >
                </div>

                <div class="span-2">
                    <label>Keterangan</label>
                    <textarea name="keterangan">{{ old('keterangan') }}</textarea>
                </div>
            </div>

            <button type="submit" class="btn primary">
                Simpan riwayat
            </button>
        </form>
    </div>

    {{-- Daftar Rekam Jejak --}}
    <div class="panel">
        <h3>Rekam jejak penyaluran</h3>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Penerima</th>
                        <th>Program</th>
                        <th>Tanggal</th>
                        <th>Jumlah</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($items as $i)
                        <tr>
                            <td>
                                {{ $i->penerima->nama ?? '-' }}
                            </td>

                            <td>
                                {{ $i->jenis->nama_program ?? '-' }}
                            </td>

                            <td>
                                {{ $i->tanggal_terima
                                    ? $i->tanggal_terima->format('d/m/Y')
                                    : '-' }}
                            </td>

                            <td>
                                {{ $i->jumlah ?? '-' }}
                                {{ $i->satuan }}
                            </td>

                            <td>
                                {{ $i->keterangan ?? '-' }}
                            </td>

                            <td class="actions">
                                <a href="{{ route('admin.riwayat.edit', $i) }}">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">
                                Belum ada riwayat bantuan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-pagination :paginator="$items" />
    </div>

@endsection
