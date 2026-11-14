@extends('layouts.admin')

@section('title', 'Edit Riwayat Bantuan')
@section('heading', 'Riwayat Penyaluran / Edit')

@section('content')

    <div class="page-head">
        <div class="page-head-text">
            <h1>Edit Riwayat Penyaluran</h1>
            <p>Perbarui rekam jejak penyaluran bantuan yang sudah tercatat.</p>
        </div>
    </div>

    <div class="card" style="max-width:760px;">

        <div class="card-head">
            <div>
                <h3>Detail penyaluran</h3>
                <p>Perubahan tercatat pada log aktivitas.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.riwayat.update', $riwayat) }}">
            @csrf
            @method('PUT')

            <div class="card-body">

                <div class="form-grid">

                    <div class="field span-2">
                        <label for="id_pengajuan">Pengajuan bantuan</label>

                        <select class="select" name="id_pengajuan" id="id_pengajuan">
                            <option value="">Riwayat lama &mdash; belum terhubung dengan pengajuan</option>

                            @foreach ($pengajuanList as $a)
                                @php
                                    $snapshot = $a->data_penerima ?? [];

                                    $nama = data_get($snapshot, 'nama', $a->penerima->nama ?? '-');
                                @endphp

                                <option value="{{ $a->id_pengajuan }}"
                                    @selected(old('id_pengajuan', $riwayat->id_pengajuan) == $a->id_pengajuan)>
                                    {{ $nama }} &mdash; {{ $a->jenisBantuan->nama_program ?? '-' }}
                                    (PGA-{{ str_pad($a->id_pengajuan, 4, '0', STR_PAD_LEFT) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="tanggal_terima">Tanggal diterima <span class="req">*</span></label>

                        <input class="input" type="date" id="tanggal_terima" name="tanggal_terima"
                               value="{{ old('tanggal_terima', $riwayat->tanggal_terima?->format('Y-m-d')) }}"
                               required>
                    </div>

                    <div class="field">
                        <label for="jumlah">Jumlah / nilai</label>

                        <div class="input-prefix">
                            <span>Rp</span>

                            <input type="number" step="1000" min="0" id="jumlah" name="jumlah"
                                   value="{{ old('jumlah', $riwayat->jumlah) }}">
                        </div>
                    </div>

                    <div class="field">
                        <label for="satuan">Satuan</label>

                        <input class="input" type="text" id="satuan" name="satuan"
                               value="{{ old('satuan', $riwayat->satuan) }}"
                               placeholder="Rupiah, paket, kg">
                    </div>

                    <div class="field">
                        <label for="keterangan">Keterangan</label>

                        <input class="input" type="text" id="keterangan" name="keterangan"
                               value="{{ old('keterangan', $riwayat->keterangan) }}"
                               placeholder="Tahap IV / transfer bank">
                    </div>

                </div>

            </div>

            <div class="card-foot" style="justify-content:flex-end;">
                <a href="{{ route('admin.riwayat.index') }}" class="btn btn-outline">Batal</a>

                <button type="submit" class="btn btn-primary">
                    <x-icon name="check" :size="14" />
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>

@endsection
