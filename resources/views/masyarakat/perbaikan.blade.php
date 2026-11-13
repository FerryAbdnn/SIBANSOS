@extends('layouts.masyarakat')

@section('title', 'Perbaikan Data')

@section('content')

    <x-page-header title="Pengajuan Perbaikan Data"
        description="Ajukan perubahan apabila terdapat informasi pada data Anda yang sudah tidak sesuai." />

    @if (!$penerima)

        <div class="panel">
            <div class="empty">
                <h3>Data penerima belum tersedia</h3>
                <p>Anda perlu mengajukan pendataan bantuan terlebih dahulu.</p>
                <a href="{{ route('masyarakat.pengajuan.create') }}" class="btn primary">
                    Ajukan Bantuan
                </a>
            </div>
        </div>

    @else

        <div class="panel">
            <h3>Data Anda Saat Ini</h3>
            <p class="muted">
                Periksa data berikut sebelum mengajukan perubahan.
            </p>

            <div class="detail-grid">
                <div>
                    <small>Nama Lengkap</small>
                    <b>{{ $penerima->nama }}</b>
                </div>

                <div>
                    <small>Alamat Saat Ini</small>
                    <b>{{ $penerima->alamat }}</b>
                </div>

                <div>
                    <small>Penghasilan Bulanan</small>
                    <b>Rp {{ number_format((float) $penerima->penghasilan_bulanan, 0, ',', '.') }}</b>
                </div>

                <div>
                    <small>Jumlah Tanggungan</small>
                    <b>{{ $penerima->jumlah_tanggungan }} orang</b>
                </div>

                <div>
                    <small>Kondisi Rumah</small>
                    <b>{{ ucwords(str_replace('_', ' ', $penerima->kondisi_rumah)) }}</b>
                </div>

                <div>
                    <small>Status Pendataan</small>
                    <span class="status {{ $penerima->status }}">
                        {{ ucfirst($penerima->status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="panel form-panel">
            <div class="panel-head">
                <div>
                    <h3>Formulir Perubahan Data</h3>
                    <p>Isi hanya bagian yang ingin Anda ubah. Bagian kosong tidak akan diubah.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert danger">
                    <strong>Pengajuan belum dapat dikirim.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('masyarakat.perbaikan.store') }}">
                @csrf

                <div class="form-grid">

                    <div class="span-2">
                        <label for="alamat">Alamat Baru</label>
                        <textarea id="alamat" name="alamat"
                            placeholder="Masukkan alamat terbaru jika ada perubahan">{{ old('alamat') }}</textarea>
                    </div>

                    <div>
                        <label for="penghasilan_bulanan">
                            Penghasilan Bulanan Baru (Rp)
                        </label>
                        <input type="number" id="penghasilan_bulanan" name="penghasilan_bulanan" min="0" step="1000"
                            value="{{ old('penghasilan_bulanan') }}" placeholder="Contoh: 2000000">
                    </div>

                    <div>
                        <label for="jumlah_tanggungan">
                            Jumlah Tanggungan Baru
                        </label>
                        <input type="number" id="jumlah_tanggungan" name="jumlah_tanggungan" min="0" max="99"
                            value="{{ old('jumlah_tanggungan') }}" placeholder="Contoh: 3">
                    </div>

                    <div class="span-2">
                        <label for="kondisi_rumah">Kondisi Rumah Baru</label>
                        <select id="kondisi_rumah" name="kondisi_rumah">
                            <option value="">Tidak ada perubahan</option>

                            <option value="permanen" @selected(old('kondisi_rumah') === 'permanen')>
                                Permanen
                            </option>

                            <option value="semi_permanen" @selected(old('kondisi_rumah') === 'semi_permanen')>
                                Semi Permanen
                            </option>

                            <option value="tidak_layak" @selected(old('kondisi_rumah') === 'tidak_layak')>
                                Tidak Layak
                            </option>
                        </select>
                    </div>

                </div>

                <div class="alert" style="background:#fff8e8;color:#85651b;">
                    <strong>Perhatian:</strong>
                    Perubahan tidak langsung diterapkan pada data Anda.
                    Admin akan memeriksa dan menyetujui pengajuan terlebih dahulu.
                </div>

                <div class="form-actions">
                    <a href="{{ route('dashboard') }}" class="btn outline">
                        Batal
                    </a>

                    <button type="submit" class="btn primary">
                        Kirim Pengajuan Perbaikan
                    </button>
                </div>
            </form>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div>
                    <h3>Riwayat Pengajuan Perbaikan</h3>
                    <p>Pantau perkembangan permintaan perubahan data Anda.</p>
                </div>
            </div>

            @forelse ($items as $item)

                <div style="padding:16px 0;border-top:1px solid var(--line);">

                    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                        <div>
                            <strong>Pengajuan #{{ $item->id_pengajuan }}</strong>
                            <small class="sub">
                                {{ $item->created_at->format('d/m/Y H:i') }}
                            </small>
                        </div>

                        <span class="status {{ $item->status }}">
                            {{ ucfirst($item->status) }}
                        </span>
                    </div>

                    <div class="detail-grid">
                        @php
                            $labels = [
                                'alamat' => 'Alamat',
                                'penghasilan_bulanan' => 'Penghasilan Bulanan',
                                'jumlah_tanggungan' => 'Jumlah Tanggungan',
                                'kondisi_rumah' => 'Kondisi Rumah',
                            ];
                        @endphp

                        @foreach (($item->data_usulan ?? []) as $key => $value)
                            <div>
                                <small>{{ $labels[$key] ?? ucfirst($key) }}</small>

                                <b>
                                    @if ($key === 'penghasilan_bulanan')
                                        Rp {{ number_format((float) $value, 0, ',', '.') }}
                                    @elseif ($key === 'kondisi_rumah')
                                        {{ ucwords(str_replace('_', ' ', $value)) }}
                                    @else
                                        {{ $value }}
                                    @endif
                                </b>
                            </div>
                        @endforeach
                    </div>

                    @if ($item->catatan_admin)
                        <div class="alert" style="background:#f3f6f4;color:#52635c;">
                            <strong>Catatan Admin:</strong>
                            {{ $item->catatan_admin }}
                        </div>
                    @endif

                </div>

            @empty
                <div class="empty">
                    Anda belum pernah mengajukan perubahan data.
                </div>
            @endforelse
        </div>

    @endif

@endsection