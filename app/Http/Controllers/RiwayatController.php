<?php

namespace App\Http\Controllers;

use App\Models\RiwayatBantuan;
use App\Models\Penerima;
use App\Models\JenisBantuan;
use Illuminate\Http\Request;
use App\Models\LogAktivitas;
use App\Support\WilayahScope;
use App\Models\PengajuanBantuan;
use Illuminate\Support\Facades\DB;

class RiwayatController extends Controller
{
    public function index()
    {
        $q = RiwayatBantuan::with([
            'penerima',
            'jenis.kategori',
            'pengajuan',
        ]);

        $user = auth()->user();

        if ($user->id_wilayah) {
            $ids = array_map(
                'intval',
                WilayahScope::ids($user->id_wilayah)
            );

            $q->where(function ($query) use ($ids) {
                // Riwayat yang memiliki snapshot wilayah.
                $query->whereHas('pengajuan', function ($pengajuan) use ($ids) {
                    $pengajuan->whereIn(
                        'data_penerima->id_wilayah',
                        $ids
                    );

                    // Jika wilayah snapshot kosong, gunakan wilayah penerima.
                    $pengajuan->orWhere(function ($fallback) use ($ids) {
                        $fallback->whereNull('data_penerima->id_wilayah')
                            ->whereHas('penerima', function ($penerima) use ($ids) {
                                $penerima->whereIn('id_wilayah', $ids);
                            });
                    });
                });

                // Riwayat lama yang belum terhubung dengan pengajuan.
                $query->orWhere(function ($legacy) use ($ids) {
                    $legacy->whereNull('id_pengajuan')
                        ->whereHas('penerima', function ($penerima) use ($ids) {
                            $penerima->whereIn('id_wilayah', $ids);
                        });
                });
            });
        }

        $items = $q->latest()->paginate(15);

        // Hanya pengajuan yang sudah terverifikasi
        $pengajuanQuery = PengajuanBantuan::with([
            'penerima',
            'jenisBantuan',
        ])->where('status', 'terverifikasi');

        $user = auth()->user();

        if ($user->id_wilayah) {
            $wilayahIds = array_map(
                'intval',
                WilayahScope::ids($user->id_wilayah)
            );

            $pengajuanQuery->where(function ($query) use ($wilayahIds) {
                $query->whereIn(
                    'data_penerima->id_wilayah',
                    $wilayahIds
                );

                // Gunakan wilayah penerima jika snapshot wilayah kosong.
                $query->orWhere(function ($fallback) use ($wilayahIds) {
                    $fallback->whereNull('data_penerima->id_wilayah')
                        ->whereHas('penerima', function ($p) use ($wilayahIds) {
                            $p->whereIn('id_wilayah', $wilayahIds);
                        });
                });
            });
        }

        $pengajuanList = $pengajuanQuery
            ->latest('created_at')
            ->get();

        return view(
            'admin.riwayat.index',
            compact('items', 'pengajuanList')
        );
    }

    public function masyarakatIndex()
    {
        $penerima = Penerima::where(
            'id_user',
            auth()->id()
        )->first();

        if ($penerima) {
            $items = $penerima->riwayat()
                ->with('jenis.kategori')
                ->orderByDesc('tanggal_terima')
                ->paginate(10)
                ->withQueryString();
        } else {
            $items = collect();
        }

        return view(
            'masyarakat.riwayat',
            compact('penerima', 'items')
        );
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'id_pengajuan' => 'required|exists:pengajuan_bantuan,id_pengajuan',
            'tanggal_terima' => 'required|date',
            'jumlah' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:30',
            'keterangan' => 'nullable|string',
        ]);

        $item = DB::transaction(function () use ($d, $r) {
            $pengajuan = PengajuanBantuan::with([
                'penerima',
                'jenisBantuan',
            ])
                ->where('id_pengajuan', $d['id_pengajuan'])
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $pengajuan->status === 'terverifikasi',
                422,
                'Penyaluran hanya dapat dicatat untuk pengajuan terverifikasi.'
            );

            abort_unless(
                $pengajuan->penerima && $pengajuan->jenisBantuan,
                422,
                'Data penerima atau program bantuan tidak ditemukan.'
            );

            // Periksa kewenangan wilayah berdasarkan snapshot pengajuan.
            $wid = auth()->user()->id_wilayah;

            if ($wid) {
                $idWilayah = data_get(
                    $pengajuan->data_penerima,
                    'id_wilayah'
                );

                if (!$idWilayah) {
                    $idWilayah = $pengajuan->penerima->id_wilayah;
                }

                abort_unless(
                    $idWilayah &&
                    in_array(
                        (int) $idWilayah,
                        array_map(
                            'intval',
                            WilayahScope::ids($wid)
                        )
                    ),
                    403
                );
            }

            $item = RiwayatBantuan::create([
                'id_pengajuan' => $pengajuan->id_pengajuan,
                'id_penerima' => $pengajuan->id_penerima,
                'id_jenis' => $pengajuan->id_jenis,
                'tanggal_terima' => $d['tanggal_terima'],
                'jumlah' => $d['jumlah'] ?? null,
                'satuan' => $d['satuan'] ?? null,
                'keterangan' => $d['keterangan'] ?? null,
                'dicatat_oleh' => auth()->id(),
            ]);

            LogAktivitas::create([
                'id_user' => auth()->id(),
                'aksi' => 'tambah',
                'tabel' => 'riwayat_bantuan',
                'id_record' => $item->id_riwayat,
                'data_baru' => $item->toArray(),
                'ip_address' => $r->ip(),
                'created_at' => now(),
            ]);

            return $item;
        });

        return back()->with(
            'success',
            'Riwayat bantuan berhasil disimpan.'
        );
    }

    public function edit(RiwayatBantuan $riwayat)
    {
        $riwayat->load([
            'penerima',
            'jenis.kategori',
            'pengajuan.penerima',
            'pengajuan.jenisBantuan',
        ]);

        $user = auth()->user();
        $wid = $user->id_wilayah;

        // Periksa wilayah berdasarkan pengajuan atau penerima lama.
        if ($wid) {
            $wilayahIds = array_map(
                'intval',
                WilayahScope::ids($wid)
            );

            $idWilayah = data_get(
                $riwayat->pengajuan?->data_penerima,
                'id_wilayah'
            );

            if (!$idWilayah) {
                $idWilayah = $riwayat->penerima?->id_wilayah;
            }

            abort_unless(
                $idWilayah &&
                in_array((int) $idWilayah, $wilayahIds),
                403
            );
        }

        // Daftar pengajuan terverifikasi untuk pilihan penggantian.
        $pengajuanQuery = PengajuanBantuan::with([
            'penerima',
            'jenisBantuan',
        ])->where('status', 'terverifikasi');

        if ($wid) {
            $wilayahIds = WilayahScope::ids($wid);

            $pengajuanQuery->where(function ($query) use ($wilayahIds) {
                $query->whereIn(
                    'data_penerima->id_wilayah',
                    $wilayahIds
                );

                $query->orWhere(function ($legacy) use ($wilayahIds) {
                    $legacy->whereNull('data_penerima')
                        ->whereHas('penerima', function ($p) use ($wilayahIds) {
                            $p->whereIn('id_wilayah', $wilayahIds);
                        });
                });
            });
        }

        $pengajuanList = $pengajuanQuery
            ->latest('created_at')
            ->get();

        // Sertakan pengajuan asal meskipun statusnya sudah berubah,
        // agar hubungan riwayat lama tidak hilang dari pilihan.
        if (
            $riwayat->pengajuan &&
            !$pengajuanList->contains('id_pengajuan', $riwayat->id_pengajuan)
        ) {
            $pengajuanList->prepend($riwayat->pengajuan);
        }

        return view(
            'admin.riwayat.edit',
            compact('riwayat', 'pengajuanList')
        );
    }

    public function update(Request $r, RiwayatBantuan $riwayat)
    {
        $d = $r->validate([
            'id_pengajuan' => 'nullable|exists:pengajuan_bantuan,id_pengajuan',
            'tanggal_terima' => 'required|date',
            'jumlah' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:30',
            'keterangan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($d, $r, $riwayat) {
            $riwayat = RiwayatBantuan::where(
                'id_riwayat',
                $riwayat->id_riwayat
            )->lockForUpdate()->firstOrFail();

            $wid = auth()->user()->id_wilayah;

            // Periksa pengajuan baru jika admin memilihnya.
            if (!empty($d['id_pengajuan'])) {
                $pengajuan = PengajuanBantuan::with([
                    'penerima',
                    'jenisBantuan',
                ])
                    ->where('id_pengajuan', $d['id_pengajuan'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // Pengajuan asal boleh dipertahankan meskipun
                // statusnya sudah berubah.
                $pengajuanAsal = (int) $riwayat->id_pengajuan
                    === (int) $pengajuan->id_pengajuan;

                abort_unless(
                    $pengajuan->status === 'terverifikasi' || $pengajuanAsal,
                    422,
                    'Pengajuan yang dipilih harus berstatus terverifikasi.'
                );

                abort_unless(
                    $pengajuan->penerima && $pengajuan->jenisBantuan,
                    422,
                    'Data penerima atau program bantuan tidak ditemukan.'
                );

                if ($wid) {
                    $idWilayah = data_get(
                        $pengajuan->data_penerima,
                        'id_wilayah'
                    ) ?: $pengajuan->penerima->id_wilayah;

                    $wilayahIds = array_map(
                        'intval',
                        WilayahScope::ids($wid)
                    );

                    abort_unless(
                        $idWilayah &&
                        in_array((int) $idWilayah, $wilayahIds),
                        403
                    );
                }

                $riwayat->id_pengajuan = $pengajuan->id_pengajuan;
                $riwayat->id_penerima = $pengajuan->id_penerima;
                $riwayat->id_jenis = $pengajuan->id_jenis;
            } else {
                // Riwayat lama tanpa hubungan pengajuan.
                abort_if(
                    $riwayat->id_pengajuan !== null,
                    422,
                    'Riwayat yang sudah terhubung dengan pengajuan tidak boleh dilepaskan.'
                );

                // Pastikan penerima lama masih dalam wilayah admin.
                if ($wid) {
                    $riwayat->load('penerima');

                    $wilayahIds = array_map(
                        'intval',
                        WilayahScope::ids($wid)
                    );

                    abort_unless(
                        $riwayat->penerima &&
                        in_array(
                            (int) $riwayat->penerima->id_wilayah,
                            $wilayahIds
                        ),
                        403
                    );
                }
            }

            $riwayat->tanggal_terima = $d['tanggal_terima'];
            $riwayat->jumlah = $d['jumlah'] ?? null;
            $riwayat->satuan = $d['satuan'] ?? null;
            $riwayat->keterangan = $d['keterangan'] ?? null;

            $riwayat->save();

            LogAktivitas::create([
                'id_user' => auth()->id(),
                'aksi' => 'edit',
                'tabel' => 'riwayat_bantuan',
                'id_record' => $riwayat->id_riwayat,
                'data_baru' => $riwayat->fresh()->toArray(),
                'ip_address' => $r->ip(),
                'created_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.riwayat.index')
            ->with(
                'success',
                'Riwayat penyaluran berhasil diperbarui.'
            );
    }

    public function destroy(RiwayatBantuan $riwayat)
    {
        return back()->withErrors([
            'delete' => 'Riwayat bantuan merupakan rekam jejak dan tidak dapat dihapus melalui aplikasi.',
        ]);
    }
}
