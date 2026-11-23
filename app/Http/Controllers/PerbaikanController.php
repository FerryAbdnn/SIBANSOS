<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Penerima;
use App\Models\PengajuanPerbaikan;
use App\Models\LogAktivitas;
use App\Support\WilayahScope;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Wilayah;

class PerbaikanController extends Controller
{


    public function index()
    {
        $wilayahId = auth()->user()->id_wilayah;

        // Admin wajib memiliki wilayah tugas.
        abort_unless(
            $wilayahId,
            403,
            'Admin tidak memiliki wilayah yang dapat diakses.'
        );

        $wilayahIds = array_map(
            'intval',
            WilayahScope::ids((int) $wilayahId)
        );

        $idPenerima = Penerima::whereIn(
            'id_wilayah',
            $wilayahIds
        )->pluck('id_penerima');

        // Query dasar agar seluruh statistik mengikuti wilayah admin.
        $query = PengajuanPerbaikan::whereIn(
            'id_penerima',
            $idPenerima
        );

        // Statistik berdasarkan status pengajuan.
        $statistik = [
            'menunggu' => (clone $query)
                ->where('status', 'menunggu')
                ->count(),

            'disetujui' => (clone $query)
                ->where('status', 'disetujui')
                ->count(),

            'ditolak' => (clone $query)
                ->where('status', 'ditolak')
                ->count(),
        ];

        // Daftar pengajuan untuk ditampilkan.

        $items = $query
            ->with([
                'penerima.wilayah.induk',
                'pengaju',
            ])
            ->latest()
            ->paginate(10);

        // Ambil RT tujuan dari seluruh pengajuan pada halaman aktif.
        $wilayahUsulanIds = $items->getCollection()
            ->map(fn($item) => (int) ($item->data_usulan['id_wilayah'] ?? 0))
            ->filter()
            ->unique()
            ->values();

        // Ambil nama RT beserta RW induknya sekaligus.
        $wilayahUsulan = Wilayah::with('induk')
            ->whereIn('id_wilayah', $wilayahUsulanIds)
            ->get()
            ->keyBy('id_wilayah');

        return view(
            'admin.perbaikan.index',
            compact('items', 'statistik', 'wilayahUsulan')
        );

    }


    public function create()
    {
        $penerima = Penerima::where(
            'id_user',
            auth()->id()
        )
            ->with(['wilayah', 'wilayah.induk'])
            ->first();

        $items = $penerima
            ? $penerima->pengajuanPerbaikan()->latest()->get()
            : collect();

        // Ambil seluruh pilihan RW.
        $rwList = \App\Models\Wilayah::where('tipe', 'rw')
            ->orderBy('nama')
            ->get();

        // Ambil seluruh pilihan RT beserta RW induknya.
        $rtList = \App\Models\Wilayah::where('tipe', 'rt')
            ->with('induk')
            ->orderBy('nama')
            ->get();

        return view(
            'masyarakat.perbaikan',
            compact('penerima', 'items', 'rwList', 'rtList')
        );
    }

    public function store(Request $request)
    {
        $penerima = Penerima::where('id_user', auth()->id())
            ->firstOrFail();



        $data = $request->validate([
            'nama' => 'nullable|string|max:100',
            'nik' => 'nullable|digits:16',
            'no_kk' => 'nullable|digits:16',
            'tanggal_lahir' => 'nullable|date|before_or_equal:today',
            'jenis_kelamin' => 'nullable|in:L,P',

            'id_rw' => [
                'nullable',
                'required_with:id_wilayah',
                'integer',
                Rule::exists('wilayah', 'id_wilayah')
                    ->where(fn($query) => $query->where('tipe', 'rw')),
            ],

            'id_wilayah' => [
                'nullable',
                'required_with:id_rw',
                'integer',
                Rule::exists('wilayah', 'id_wilayah')
                    ->where(fn($query) => $query
                        ->where('tipe', 'rt')
                        ->where('id_induk', $request->input('id_rw'))),
            ],

            'alamat' => 'nullable|string|max:1000',
            'penghasilan_bulanan' => 'nullable|numeric|min:0',
            'jumlah_tanggungan' => 'nullable|integer|min:0|max:99',

            'alasan' => 'required|string|max:2000',

            'dokumen_surat_rt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'dokumen_berhenti_kerja' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);


        $dataUsulan = [];

        // Nama lengkap.
        if (
            filled($data['nama'] ?? null) &&
            $data['nama'] !== $penerima->nama
        ) {
            $dataUsulan['nama'] = $data['nama'];
        }

        // NIK.
        $nikLama = $penerima->nik_decrypted;

        if (
            filled($data['nik'] ?? null) &&
            $data['nik'] !== $nikLama
        ) {
            $nikHash = hash_hmac(
                'sha256',
                $data['nik'],
                config('app.key')
            );

            $nikDuplikat = Penerima::where('nik_hash', $nikHash)
                ->where('id_penerima', '!=', $penerima->id_penerima)
                ->exists();

            if ($nikDuplikat) {
                throw ValidationException::withMessages([
                    'nik' => 'NIK tersebut sudah digunakan oleh penerima lain.',
                ]);
            }

            $dataUsulan['nik'] = encrypt($data['nik']);
        }

        // Nomor KK.
        $kkLama = $penerima->no_kk_decrypted;

        if (
            filled($data['no_kk'] ?? null) &&
            $data['no_kk'] !== $kkLama
        ) {
            $dataUsulan['no_kk'] = encrypt($data['no_kk']);
        }

        // Tanggal lahir.
        $tanggalLahirLama = $penerima->tanggal_lahir?->format('Y-m-d');

        if (
            filled($data['tanggal_lahir'] ?? null) &&
            $data['tanggal_lahir'] !== $tanggalLahirLama
        ) {
            $dataUsulan['tanggal_lahir'] = $data['tanggal_lahir'];
        }

        // Jenis kelamin.
        if (
            filled($data['jenis_kelamin'] ?? null) &&
            $data['jenis_kelamin'] !== $penerima->jenis_kelamin
        ) {
            $dataUsulan['jenis_kelamin'] = $data['jenis_kelamin'];
        }

        // RW dan RT.
        if (
            filled($data['id_rw'] ?? null) &&
            filled($data['id_wilayah'] ?? null) &&
            (int) $data['id_wilayah'] !== (int) $penerima->id_wilayah
        ) {
            $dataUsulan['id_rw'] = (int) $data['id_rw'];
            $dataUsulan['id_wilayah'] = (int) $data['id_wilayah'];
        }

        // Alamat.
        if (
            filled($data['alamat'] ?? null) &&
            $data['alamat'] !== $penerima->alamat
        ) {
            $dataUsulan['alamat'] = $data['alamat'];
        }

        // Penghasilan bulanan.
        if (filled($data['penghasilan_bulanan'] ?? null)) {
            $penghasilanBaru = (float) $data['penghasilan_bulanan'];
            $penghasilanLama = (float) $penerima->penghasilan_bulanan;

            if ($penghasilanBaru != $penghasilanLama) {
                $dataUsulan['penghasilan_bulanan'] = $penghasilanBaru;
            }
        }

        // Jumlah tanggungan.
        if (filled($data['jumlah_tanggungan'] ?? null)) {
            $tanggunganBaru = (int) $data['jumlah_tanggungan'];
            $tanggunganLama = (int) $penerima->jumlah_tanggungan;

            if ($tanggunganBaru !== $tanggunganLama) {
                $dataUsulan['jumlah_tanggungan'] = $tanggunganBaru;
            }
        }

        abort_if(
            empty($dataUsulan),
            422,
            'Tidak ditemukan perubahan data untuk diajukan.'
        );

        $storedPaths = [];

        try {
            $pathSuratRt = null;
            $pathBerhentiKerja = null;

            // Surat keterangan RT bersifat opsional.
            if ($request->hasFile('dokumen_surat_rt')) {
                $pathSuratRt = $request->file('dokumen_surat_rt')
                    ->store('dokumen-perbaikan', 'local');

                $storedPaths[] = $pathSuratRt;
            }

            // Surat keterangan berhenti kerja bersifat opsional.
            if ($request->hasFile('dokumen_berhenti_kerja')) {
                $pathBerhentiKerja = $request->file('dokumen_berhenti_kerja')
                    ->store('dokumen-perbaikan', 'local');

                $storedPaths[] = $pathBerhentiKerja;
            }

            DB::transaction(function () use ($penerima, $dataUsulan, $data, $pathSuratRt, $pathBerhentiKerja) {
                PengajuanPerbaikan::create([
                    'id_penerima' => $penerima->id_penerima,
                    'diajukan_oleh' => auth()->id(),
                    'data_usulan' => $dataUsulan,
                    'alasan' => $data['alasan'],
                    'dokumen_surat_rt' => $pathSuratRt,
                    'dokumen_berhenti_kerja' => $pathBerhentiKerja,
                    'status' => 'menunggu',
                ]);
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Storage::disk('local')
                ->delete($storedPaths);

            throw $e;
        }

        return back()->with(
            'success',
            'Permintaan perbaikan dikirim ke admin.'
        );
    }


    public function showDocument($pengajuanId, $jenis)
    {
        // Hanya dua jenis dokumen yang boleh diakses.
        $kolomDokumen = [
            'surat-rt' => 'dokumen_surat_rt',
            'berhenti-kerja' => 'dokumen_berhenti_kerja',
        ];

        abort_unless(
            isset($kolomDokumen[$jenis]),
            404
        );

        // Ambil pengajuan beserta penerimanya.
        $pengajuan = PengajuanPerbaikan::with('penerima')
            ->findOrFail($pengajuanId);

        // Pastikan admin memiliki wilayah tugas.
        $wilayahId = auth()->user()->id_wilayah;

        abort_unless(
            $wilayahId,
            403,
            'Admin tidak memiliki wilayah yang dapat diakses.'
        );

        $wilayahIds = array_map(
            'intval',
            WilayahScope::ids((int) $wilayahId)
        );

        // Pastikan penerima berada dalam wilayah admin.
        abort_unless(
            $pengajuan->penerima &&
            in_array(
                (int) $pengajuan->penerima->id_wilayah,
                $wilayahIds,
                true
            ),
            403,
            'Anda tidak dapat mengakses dokumen wilayah lain.'
        );

        // Ambil lokasi dokumen dari kolom yang diizinkan.
        $path = $pengajuan->{$kolomDokumen[$jenis]};

        abort_unless(
            $path &&
            str_starts_with($path, 'dokumen-perbaikan/') &&
            Storage::disk('local')->exists($path),
            404,
            'Dokumen tidak ditemukan.'
        );

        $mimeType = Storage::disk('local')->mimeType($path)
            ?: 'application/octet-stream';

        return Storage::disk('local')->response(
            $path,
            basename($path),
            [
                'Content-Type' => $mimeType,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store, max-age=0',
            ],
            'inline'
        );
    }

    public function proses(
        Request $request,
        PengajuanPerbaikan $pengajuan
    ) {
        $wilayahId = auth()->user()->id_wilayah;

        abort_unless(
            $wilayahId,
            403,
            'Admin tidak memiliki wilayah yang dapat diakses.'
        );

        $data = $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'catatan_admin' => 'nullable|string|max:1000',
        ]);

        $wilayahIds = array_map(
            'intval',
            WilayahScope::ids((int) $wilayahId)
        );

        $hasil = DB::transaction(function () use ($pengajuan, $data, $wilayahIds) {
            // Kunci pengajuan agar tidak diproses bersamaan.
            $pengajuanTerkunci = PengajuanPerbaikan::where(
                'id_pengajuan',
                $pengajuan->id_pengajuan
            )
                ->lockForUpdate()
                ->firstOrFail();

            // Periksa ulang status setelah penguncian.
            if ($pengajuanTerkunci->status !== 'menunggu') {
                return false;
            }

            // Kunci profil penerima.
            $penerima = Penerima::where(
                'id_penerima',
                $pengajuanTerkunci->id_penerima
            )
                ->lockForUpdate()
                ->firstOrFail();

            // Pastikan penerima masih berada dalam wilayah admin.
            abort_unless(
                in_array(
                    (int) $penerima->id_wilayah,
                    $wilayahIds,
                    true
                ),
                403,
                'Anda tidak dapat mengakses data wilayah lain.'
            );


            $dataLama = null;
            $dataBaru = null;

            if ($data['status'] === 'disetujui') {
                // Kolom yang boleh diperbarui.
                $kolomDiizinkan = [
                    'nama',
                    'tanggal_lahir',
                    'jenis_kelamin',
                    'id_wilayah',
                    'alamat',
                    'penghasilan_bulanan',
                    'jumlah_tanggungan',
                    'nik',
                    'no_kk',
                ];

                $usulan = array_intersect_key(
                    (array) $pengajuanTerkunci->data_usulan,
                    array_flip($kolomDiizinkan)
                );

                abort_if(
                    empty($usulan),
                    422,
                    'Data usulan perbaikan tidak valid.'
                );

                // Data lama untuk log. NIK dan KK tidak dimasukkan.
                $kolomLog = [
                    'alamat',
                    'penghasilan_bulanan',
                    'jumlah_tanggungan',
                    'tanggal_lahir',
                    'jenis_kelamin',
                    'id_wilayah',
                ];

                $dataLama = $penerima->only($kolomLog);

                // Validasi ulang wilayah tujuan.
                $idRwUsulan = $pengajuanTerkunci->data_usulan['id_rw'] ?? null;

                if (isset($usulan['id_wilayah'])) {
                    abort_unless(
                        $idRwUsulan &&
                        Wilayah::where('id_wilayah', $usulan['id_wilayah'])
                            ->where('tipe', 'rt')
                            ->where('id_induk', $idRwUsulan)
                            ->whereIn('id_wilayah', $wilayahIds)
                            ->exists(),
                        422,
                        'Wilayah RT/RW tujuan tidak valid atau berada di luar wilayah tugas Anda.'
                    );
                }

                // id_rw hanya metadata validasi, bukan kolom tabel penerima.
                unset($usulan['id_rw']);

                // Proses perubahan NIK.
                if (isset($usulan['nik'])) {
                    try {
                        $nikBaru = decrypt($usulan['nik']);
                    } catch (\Throwable $e) {
                        abort(422, 'Data NIK usulan tidak valid.');
                    }

                    abort_unless(
                        preg_match('/^[0-9]{16}$/', $nikBaru),
                        422,
                        'Format NIK usulan tidak valid.'
                    );

                    $nikHash = hash_hmac(
                        'sha256',
                        $nikBaru,
                        config('app.key')
                    );

                    $nikDuplikat = Penerima::where('nik_hash', $nikHash)
                        ->where('id_penerima', '!=', $penerima->id_penerima)
                        ->exists();

                    abort_if(
                        $nikDuplikat,
                        422,
                        'NIK tersebut sudah digunakan oleh penerima lain.'
                    );

                    $usulan['nik'] = encrypt($nikBaru);
                    $usulan['nik_hash'] = $nikHash;
                }

                // Pastikan Nomor KK tetap terenkripsi.
                if (isset($usulan['no_kk'])) {
                    try {
                        $noKkBaru = decrypt($usulan['no_kk']);
                    } catch (\Throwable $e) {
                        abort(422, 'Data Nomor KK usulan tidak valid.');
                    }

                    abort_unless(
                        preg_match('/^[0-9]{16}$/', $noKkBaru),
                        422,
                        'Format Nomor KK usulan tidak valid.'
                    );

                    $usulan['no_kk'] = encrypt($noKkBaru);
                }

                // Terapkan hanya data yang benar-benar diajukan.
                $penerima->update($usulan);

                // Data baru untuk log, tanpa NIK dan Nomor KK.
                $dataBaru = $penerima->fresh()->only($kolomLog);
            }

            $pengajuanTerkunci->update([
                'status' => $data['status'],
                'catatan_admin' => $data['catatan_admin'] ?? null,
                'diproses_oleh' => auth()->id(),
                'diproses_at' => now(),
            ]);

            LogAktivitas::create([
                'id_user' => auth()->id(),
                'aksi' => 'proses_perbaikan',
                'tabel' => 'pengajuan_perbaikan',
                'id_record' => $pengajuanTerkunci->id_pengajuan,
                'data_lama' => $dataLama,
                'data_baru' => $dataBaru,
                'ip_address' => request()->ip(),
                'created_at' => now(),
            ]);

            return true;
        });

        if (!$hasil) {
            return redirect()
                ->route('admin.perbaikan.index')
                ->with(
                    'warning',
                    'Pengajuan perbaikan ini sudah diproses sebelumnya.'
                );
        }

        return back()->with(
            'success',
            'Pengajuan perbaikan diproses.'
        );
    }
}