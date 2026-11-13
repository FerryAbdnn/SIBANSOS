@extends('layouts.admin')

@section('title', 'Perbaikan Data')

@section('heading', 'Pengajuan Perbaikan Data')

@section('content')

    @if (session('warning'))
        <div class="alert alert-warning">
            {{ session('warning') }}
        </div>
    @endif

    <div class="panel">

        <div class="panel-head">
            <div>
                <h3>Permintaan perubahan dari masyarakat</h3>

                <p class="muted">
                    Perubahan data baru akan diterapkan setelah disetujui admin.
                </p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
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

                        <tr>
                            <td>
                                <b>
                                    {{ $i->penerima->nama ?? 'Data penerima tidak ditemukan' }}
                                </b>

                                <small class="sub">
                                    {{ $i->pengaju->email ?? '-' }}
                                </small>
                            </td>

                            <td>
                                <pre
                                    style="white-space: pre-wrap; font: 11px monospace;">{{ json_encode($i->data_usulan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </td>

                            <td>
                                {{ $i->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>

                            <td>
                                @if ($i->status === 'menunggu')

                                    <form method="POST" action="{{ route('admin.perbaikan.proses', $i) }}">
                                        @csrf
                                        @method('PATCH')

                                        <input type="text" name="catatan_admin" placeholder="Catatan admin"
                                            value="{{ old('catatan_admin') }}">

                                        <button type="submit" class="btn primary small" name="status" value="disetujui">
                                            Setujui
                                        </button>

                                        <button type="submit" class="btn danger-btn small" name="status" value="ditolak">
                                            Tolak
                                        </button>
                                    </form>

                                @else

                                    <span class="status {{ $i->status }}">
                                        {{ ucfirst($i->status) }}
                                    </span>

                                    @if ($i->catatan_admin)
                                        <small class="sub">
                                            Catatan: {{ $i->catatan_admin }}
                                        </small>
                                    @endif

                                @endif
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="empty">
                                Tidak ada permintaan perbaikan data.
                            </td>
                        </tr>

                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <x-pagination :paginator="$items" />
        </div>

    </div>

@endsection