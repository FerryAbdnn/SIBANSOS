
@extends('layouts.admin')

@section('title', 'Edit Riwayat Bantuan')
@section('heading', 'Edit Riwayat Penyaluran')

@section('content')

    <div class="panel form-panel">

        <div class="panel-head">
            <div>
                <h3>Perbarui rekam jejak penyaluran</h3>
                <p>
                    Ubah informasi penyaluran bantuan yang sudah tercatat.
                </p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('admin.riwayat.update', $riwayat) }}"
        >
            @csrf
            @method('PUT')

            <div class="form-grid">

                <div class="span-2">
                    <label>Pengajuan bantuan (penerima dan program)</label>

                    <select name="id_pengajuan">
                        <option value="">-- Pilih pengajuan --</option>

                        @if(!$riwayat->id_pengajuan)
                            <option value="" selected>
                                Riwayat lama — belum terhubung dengan pengajuan
                            </option>
                        @endif

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
                                @selected(
                                    old('id_pengajuan', $riwayat->id_pengajuan)
                                    == $a->id_pengajuan
                                )
                            >
                                {{ $nama }}
                                — {{ $a->jenisBantuan->nama_program ?? '-' }}
                                (Pengajuan #{{ $a->id_pengajuan }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Tanggal diterima</label>

                    <input
                        type="date"
                        name="tanggal_terima"
                        value="{{ old(
                            'tanggal_terima',
                            $riwayat->tanggal_terima
                                ? $riwayat->tanggal_terima->format('Y-m-d')
                                : ''
                        ) }}"
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
                        value="{{ old('jumlah', $riwayat->jumlah) }}"
                    >
                </div>

                <div>
                    <label>Satuan</label>

                    <input
                        type="text"
                        name="satuan"
                        value="{{ old('satuan', $riwayat->satuan) }}"
                        placeholder="Rupiah, paket, kg"
                    >
                </div>

                <div class="span-2">
                    <label>Keterangan</label>

                    <textarea name="keterangan">{{ old(
                        'keterangan',
                        $riwayat->keterangan
                    ) }}</textarea>
                </div>

            </div>

            <div class="form-actions">
                <a
                    href="{{ route('admin.riwayat.index') }}"
                    class="btn outline"
                >
                    Batal
                </a>

                <button type="submit" class="btn primary">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>

@endsection
