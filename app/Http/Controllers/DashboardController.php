<?php

namespace App\Http\Controllers;

use App\Models\Penerima;
use App\Models\KategoriBantuan;
use App\Support\WilayahScope;

class DashboardController extends Controller
{
    public function index()
    {
        $u = auth()->user();

        // Dashboard Masyarakat
        if ($u->peran === 'masyarakat') {
            $p = Penerima::where('id_user', $u->id_user)
                ->with([
                    // Wilayah RT -> RW -> Desa/Kelurahan
                    'wilayah.induk.induk',
                    'dokumen',

                    // Pengajuan bantuan terbaru beserta penyalurannya
                    'pengajuanTerbaru.jenisBantuan',
                    'pengajuanTerbaru.riwayat',

                    // Riwayat bantuan terbaru
                    'riwayat' => function ($query) {
                        $query->with('jenis')
                            ->orderByDesc('tanggal_terima')
                            ->orderByDesc('id_riwayat')
                            ->limit(5);
                    },

                    // Pengajuan perbaikan data terbaru
                    'pengajuanPerbaikan' => function ($query) {
                        $query->latest('created_at')
                            ->limit(1);
                    },
                ])
                ->withCount([
                    'dokumen',

                    'riwayat',

                    'pengajuanBantuan as pengajuan_aktif_count' => function ($query) {
                        $query->whereIn('status', [
                            'diajukan',
                            'terverifikasi',
                        ]);
                    },
                ])
                ->first();

            return view('masyarakat.dashboard', compact('p'));
        }

        // Query data penerima sesuai wilayah admin
        $q = Penerima::query();

        if ($u->id_wilayah) {
            $q->whereIn(
                'id_wilayah',
                WilayahScope::ids($u->id_wilayah)
            );
        }

        // Statistik berdasarkan pengajuan terbaru setiap penerima
        $stats = [
            'total' => (clone $q)->count(),

            'nonaktif' => (clone $q)
                ->where('status', 'nonaktif')
                ->count(),

            'diajukan' => (clone $q)
                ->whereHas('pengajuanTerbaru', function ($query) {
                    $query->where('status', 'diajukan');
                })
                ->count(),

            'terverifikasi' => (clone $q)
                ->whereHas('pengajuanTerbaru', function ($query) {
                    $query->where('status', 'terverifikasi');
                })
                ->count(),

            'ditolak' => (clone $q)
                ->whereHas('pengajuanTerbaru', function ($query) {
                    $query->where('status', 'ditolak');
                })
                ->count(),
        ];

        // Data penerima terbaru beserta status pengajuan terakhir
        $recent = (clone $q)
            ->with([
                'wilayah.induk.induk',
                'pengajuanTerbaru',
            ])
            ->latest()
            ->take(8)
            ->get();

        // Ringkasan kategori bantuan
        $kategori = KategoriBantuan::with('jenis')
            ->withCount('jenis')
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'recent',
            'kategori'
        ));
    }
}
