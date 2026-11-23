@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/riwayat-penyaluran-admin.css') }}">
@endpush

@section('title', 'Riwayat Bantuan')
@section('heading', 'Riwayat Bantuan')

@section('content')

    {{-- Header halaman --}}
    <div class="rp-page-header">
        <div>
            <h2>Riwayat Penyaluran</h2>
            <p>Seluruh rekam jejak bantuan yang telah diterima oleh masyarakat.</p>
        </div>
    </div>

    {{-- Statistik penyaluran --}}
    <div class="rp-stat-grid">

        <div class="rp-stat-card">
            <div class="rp-stat-top">
                <span>Total Penyaluran</span>
                <span class="rp-stat-icon" aria-hidden="true">📦</span>
                <i class="bi bi-collection"></i>
                </span>
            </div>
            <strong>{{ number_format($totalPenyaluran, 0, ',', '.') }}</strong>
            <small>Seluruh catatan penyaluran</small>
        </div>

        <div class="rp-stat-card">
            <div class="rp-stat-top">
                <span>Penyaluran Bulan Ini</span>
                <span class="rp-stat-icon" aria-hidden="true">📅</span>
                <i class="bi bi-calendar-check"></i>
                </span>
            </div>
            <strong>{{ number_format($penyaluranBulanIni, 0, ',', '.') }}</strong>
            <small>{{ now()->translatedFormat('F Y') }}</small>
        </div>

        <div class="rp-stat-card">
            <div class="rp-stat-top">
                <span>Penerima Unik</span>
                <span class="rp-stat-icon" aria-hidden="true">👥</span>
                <i class="bi bi-people"></i>
                </span>
            </div>
            <strong>{{ number_format($totalPenerima, 0, ',', '.') }}</strong>
            <small>Penerima yang tercatat menerima bantuan</small>
        </div>

        <div class="rp-stat-card">
            <div class="rp-stat-top">
                <span>Program Tersalurkan</span>
                <span class="rp-stat-icon" aria-hidden="true">🎁</span>
                <i class="bi bi-box-seam"></i>
                </span>
            </div>
            <strong>{{ number_format($totalProgram, 0, ',', '.') }}</strong>
            <small>Jenis program yang sudah tersalurkan</small>
        </div>

    </div>

    {{-- Form Catat Penyaluran --}}
    <details class="rp-create-panel" @if($errors->any()) open @endif>
        <summary class="rp-create-panel-summary">
            <span>
                <i class="bi bi-clipboard-plus"></i>
                Form Pencatatan Penyaluran
            </span>
            <i class="bi bi-chevron-down rp-create-chevron"></i>
        </summary>

        <div class="rp-create-panel-body">
            <p class="rp-form-description">
                Pilih pengajuan yang telah terverifikasi, kemudian isi informasi
                bantuan sesuai dengan penyaluran yang benar-benar diterima.
            </p>

            <form method="POST" action="{{ route('admin.riwayat.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="span-2">
                        <label>Pengajuan Terverifikasi <span>*</span></label>

                        <select name="id_pengajuan" required>
                            <option value="">-- Pilih penerima dan program bantuan --</option>

                            @foreach($pengajuanList as $a)
                                @php
                                    $snapshot = $a->data_penerima ?? [];
                                    $nama = data_get(
                                        $snapshot,
                                        'nama',
                                        $a->penerima->nama ?? '-'
                                    );
                                @endphp

                                <option value="{{ $a->id_pengajuan }}" @selected(old('id_pengajuan') == $a->id_pengajuan)>
                                    {{ $nama }}
                                    — {{ $a->jenisBantuan->nama_program ?? '-' }}
                                    (Pengajuan #{{ $a->id_pengajuan }})
                                </option>
                            @endforeach
                        </select>

                        @error('id_pengajuan')
                            <small class="rp-field-error">{{ $message }}</small>
                        @enderror

                        @if($pengajuanList->isEmpty())
                            <p class="rp-form-hint">
                                Belum ada pengajuan terverifikasi yang dapat dicatat penyalurannya.
                            </p>
                        @endif
                    </div>

                    <div>
                        <label>Tanggal Diterima <span>*</span></label>
                        <input type="date" name="tanggal_terima" value="{{ old('tanggal_terima') }}" required>
                        @error('tanggal_terima')
                            <small class="rp-field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label>Jumlah / Nilai Bantuan</label>
                        <input type="number" step="0.01" min="0" name="jumlah" value="{{ old('jumlah') }}"
                            placeholder="Masukkan jumlah bantuan">
                        @error('jumlah')
                            <small class="rp-field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label>Satuan</label>
                        <input type="text" name="satuan" value="{{ old('satuan') }}" maxlength="30"
                            placeholder="Contoh: Rupiah, paket, kg">
                        @error('satuan')
                            <small class="rp-field-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="span-2">
                        <label>Keterangan</label>
                        <textarea name="keterangan" rows="3"
                            placeholder="Tambahkan keterangan jika diperlukan...">{{ old('keterangan') }}</textarea>
                        @error('keterangan')
                            <small class="rp-field-error">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="rp-form-actions">
                    <button type="submit" class="rp-btn-primary">
                        <i class="bi bi-check-lg"></i>
                        Simpan Penyaluran
                    </button>
                </div>
            </form>
        </div>
    </details>

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