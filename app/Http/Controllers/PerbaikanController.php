<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Penerima;
use App\Models\PengajuanPerbaikan;
use App\Models\LogAktivitas;
use App\Support\WilayahScope;

class PerbaikanController extends Controller
{

    public function index()
    {
        $query = PengajuanPerbaikan::with([
            'penerima',
            'pengaju'
        ])->latest();

        $wilayahId = auth()->user()->id_wilayah;

        if ($wilayahId) {
            $idPenerima = Penerima::whereIn(
                'id_wilayah',
                WilayahScope::ids($wilayahId)
            )->pluck('id_penerima');

            $query->whereIn('id_penerima', $idPenerima);
        }

        $items = $query->paginate(10);

        return view('admin.perbaikan.index', compact('items'));
    }

    public function create()
    {
        $penerima = Penerima::where(
            'id_user',
            auth()->id()
        )
            ->with('wilayah')
            ->first();

        $items = $penerima
            ? $penerima->pengajuanPerbaikan()->latest()->get()
            : collect();

        return view(
            'masyarakat.perbaikan',
            compact('penerima', 'items')
        );
    }

    public function store(Request $request)
    {
        $penerima = Penerima::where('id_user', auth()->id())
            ->firstOrFail();

        $data = $request->validate([
            'alamat' => 'nullable|string',
            'nomor_whatsapp' => 'nullable|string|max:20',
            'penghasilan_bulanan' => 'nullable|numeric|min:0',
            'jumlah_tanggungan' => 'nullable|integer|min:0',
            'kondisi_rumah' => 'nullable|in:permanen,semi_permanen,tidak_layak',
            'alasan' => 'nullable|string|max:1000',
        ]);

        $data = array_filter(
            $data,
            fn($value) => $value !== null && $value !== ''
        );

        // Alasan bukan kolom penerima, hanya catatan pendukung usulan.
        $perubahan = array_diff_key($data, ['alasan' => null]);

        abort_if(
            !$perubahan,
            422,
            'Isi minimal satu perubahan.'
        );

        PengajuanPerbaikan::create([
            'id_penerima' => $penerima->id_penerima,
            'diajukan_oleh' => auth()->id(),
            'data_usulan' => $data,
            'status' => 'menunggu',
        ]);

        return back()->with(
            'success',
            'Permintaan perbaikan dikirim ke admin.'
        );
    }


    public function proses(
        Request $request,
        PengajuanPerbaikan $pengajuan
    ) {
        if ($pengajuan->status !== 'menunggu') {
            return redirect()
                ->route('admin.perbaikan.index')
                ->with('warning', 'Pengajuan perbaikan ini sudah diproses sebelumnya.');
        }

        $wilayahId = auth()->user()->id_wilayah;

        abort_unless(
            $wilayahId,
            403,
            'Admin tidak memiliki wilayah yang dapat diakses.'
        );

        $penerima = Penerima::findOrFail(
            $pengajuan->id_penerima
        );

        $wilayahIds = array_map(
            'intval',
            WilayahScope::ids($wilayahId)
        );

        abort_unless(
            in_array(
                (int) $penerima->id_wilayah,
                $wilayahIds,
                true
            ),
            403,
            'Anda tidak dapat mengakses data wilayah lain.'
        );

        $data = $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'catatan_admin' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($pengajuan, $data, $penerima) {
            $dataLama = null;
            $dataBaru = null;

            if ($data['status'] === 'disetujui') {
                $dataLama = $penerima->only([
                    'alamat',
                    'penghasilan_bulanan',
                    'jumlah_tanggungan',
                    'kondisi_rumah',
                ]);

                $usulan = $pengajuan->data_usulan;

                // Kolom yang bukan atribut penerima tidak boleh ikut diperbarui.
                unset($usulan['alasan']);

                $penerima->update($usulan);

                $dataBaru = $penerima->fresh()->only([
                    'alamat',
                    'penghasilan_bulanan',
                    'jumlah_tanggungan',
                    'kondisi_rumah',
                ]);
            }

            $pengajuan->update([
                'status' => $data['status'],
                'catatan_admin' => $data['catatan_admin'] ?? null,
                'diproses_oleh' => auth()->id(),
                'diproses_at' => now(),
            ]);

            LogAktivitas::create([
                'id_user' => auth()->id(),
                'aksi' => 'proses_perbaikan',
                'tabel' => 'pengajuan_perbaikan',
                'id_record' => $pengajuan->id_pengajuan,
                'data_lama' => $dataLama,
                'data_baru' => $dataBaru,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);
        });

        return back()->with(
            'success',
            'Pengajuan perbaikan diproses.'
        );
    }

}
