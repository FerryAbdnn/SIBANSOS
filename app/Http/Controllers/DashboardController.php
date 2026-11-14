<?php

namespace App\Http\Controllers;

use App\Models\Penerima;
use App\Models\KategoriBantuan;
use App\Models\RiwayatBantuan;
use App\Models\PengajuanBantuan;
use App\Models\Wilayah;
use App\Support\WilayahScope;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $u = auth()->user();

        if ($u->peran === 'masyarakat') {
            $p = Penerima::where('id_user', $u->id_user)
                ->with('riwayat.jenis.kategori', 'pengajuanBantuan.jenisBantuan', 'dokumen', 'wilayah')
                ->first();

            return view('masyarakat.dashboard', compact('p'));
        }

        $q = Penerima::query();

        if ($u->id_wilayah) {
            $q->whereIn('id_wilayah', WilayahScope::ids($u->id_wilayah));
        }

        $stats = [
            'total' => (clone $q)->count(),
            'diajukan' => (clone $q)->where('status', 'diajukan')->count(),
            'terverifikasi' => (clone $q)->where('status', 'terverifikasi')->count(),
            'ditolak' => (clone $q)->where('status', 'ditolak')->count(),
            'nonaktif' => (clone $q)->where('status', 'nonaktif')->count(),
        ];

        $recent = (clone $q)->with('wilayah', 'pengajuanTerbaru')->latest()->take(6)->get();

        $kategori = KategoriBantuan::withCount('jenis')->get();

        // Sebaran penerima per kategori bantuan (berdasarkan riwayat penyaluran).
        $sebaranKategori = KategoriBantuan::query()
            ->select('kategori_bantuan.nama', DB::raw('COUNT(DISTINCT riwayat_bantuan.id_penerima) as jumlah'))
            ->leftJoin('jenis_bantuan', 'jenis_bantuan.id_kategori', '=', 'kategori_bantuan.id_kategori')
            ->leftJoin('riwayat_bantuan', 'riwayat_bantuan.id_jenis', '=', 'jenis_bantuan.id_jenis')
            ->groupBy('kategori_bantuan.id_kategori', 'kategori_bantuan.nama')
            ->orderByDesc('jumlah')
            ->get();

        // Sebaran wilayah penugasan.
        $wilayahIds = $u->id_wilayah ? WilayahScope::ids($u->id_wilayah) : [];

        $sebaranWilayah = Penerima::query()
            ->select('id_wilayah', DB::raw('COUNT(*) as jumlah'))
            ->when($wilayahIds, fn ($query) => $query->whereIn('id_wilayah', $wilayahIds))
            ->groupBy('id_wilayah')
            ->orderByDesc('jumlah')
            ->take(4)
            ->get()
            ->map(function ($row) {
                $wilayah = Wilayah::find($row->id_wilayah);

                if (!$wilayah) {
                    $row->label = 'Tanpa wilayah';

                    return $row;
                }

                // Sertakan RW induk agar RT dengan nomor sama tetap berbeda.
                if ($wilayah->tipe === 'rt' && $wilayah->id_induk) {
                    $rw = Wilayah::find($wilayah->id_induk);

                    $row->label = $wilayah->label . ($rw ? ' / ' . $rw->label : '');
                } else {
                    $row->label = $wilayah->label;
                }

                return $row;
            });

        // Antrean verifikasi pengajuan (objek yang diproses pada menu Verifikasi).
        $antrean = PengajuanBantuan::query()
            ->when($wilayahIds, fn ($query) => $query->whereIn('data_penerima->id_wilayah', $wilayahIds));

        $jumlahMenunggu = (clone $antrean)->where('status', 'diajukan')->count();

        $tertuaMenunggu = (clone $antrean)
            ->where('status', 'diajukan')
            ->oldest()
            ->value('created_at');

        // Tabel "Data menunggu verifikasi" pada dashboard.
        $penerimaMenunggu = (clone $q)
            ->where('status', 'diajukan')
            ->with(['wilayah', 'jenisBantuan.kategori'])
            ->latest('created_at')
            ->take(6)
            ->get();

        $totalPenyaluran = RiwayatBantuan::when(
            $wilayahIds,
            fn ($query) => $query->whereHas(
                'penerima',
                fn ($p) => $p->whereIn('id_wilayah', $wilayahIds)
            )
        )->sum('jumlah');

        return view('admin.dashboard', compact(
            'stats',
            'recent',
            'kategori',
            'sebaranKategori',
            'sebaranWilayah',
            'jumlahMenunggu',
            'tertuaMenunggu',
            'totalPenyaluran',
            'penerimaMenunggu'
        ));
    }
}
