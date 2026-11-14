<?php

namespace App\Http\Controllers;

use App\Models\Penerima;
use App\Models\Wilayah;
use App\Models\KategoriBantuan;
use App\Models\LogAktivitas;
use App\Support\WilayahScope;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\DokumenPendukung;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\JenisBantuan;
use Throwable;

class PenerimaController extends Controller
{
    private function scope()
    {
        $wilayahIds = WilayahScope::ids(
            (int) auth()->user()->id_wilayah
        );

        return Penerima::with('wilayah')
            ->whereIn('id_wilayah', $wilayahIds);
    }

    public function index(Request $r)
    {
        $wilayahIds = WilayahScope::ids(
            (int) auth()->user()->id_wilayah
        );

        $q = $this->scope()
            ->withSum('riwayat as total_diterima', 'jumlah')
            ->with([
                'riwayat.jenis.kategori',
                'wilayah',
                'pengajuanTerbaru',
            ]);

        // Pencarian nama, kode tampilan, atau NIK
        if ($r->filled('q')) {
            $keyword = trim($r->q);

            $q->where(function ($query) use ($keyword) {
                $query->where('nama', 'like', '%' . $keyword . '%');

                // Kode tampilan PNR-2026-00001
                if (preg_match('/^PNR-\d{4}-(\d+)$/i', $keyword, $match)) {
                    $query->orWhere(
                        'id_penerima',
                        (int) $match[1]
                    );
                }

                // Pencarian NIK menggunakan hash
                if (preg_match('/^\d{16}$/', $keyword)) {
                    $query->orWhere(
                        'nik_hash',
                        hash_hmac('sha256', $keyword, config('app.key'))
                    );
                }
            });
        }

        // Filter status
        if ($r->filled('status')) {
            $q->where('status', $r->status);
        }

        // Filter kategori bantuan
        if ($r->filled('kategori')) {
            $q->whereHas('riwayat.jenis', function ($query) use ($r) {
                $query->where('id_kategori', $r->kategori);
            });
        }

        // Filter wilayah RT/RW
        if ($r->filled('wilayah')) {
            $selectedWilayah = (int) $r->wilayah;

            abort_unless(
                in_array($selectedWilayah, $wilayahIds, true),
                403,
                'Wilayah tidak termasuk cakupan akses Anda.'
            );

            $filterWilayahIds = WilayahScope::ids($selectedWilayah);

            $q->whereIn('id_wilayah', $filterWilayahIds);
        }

        $penerima = $q
            ->latest('created_at')
            ->paginate(7)
            ->withQueryString();

        // Statistik sesuai cakupan wilayah admin
        $statistik = Penerima::whereIn('id_wilayah', $wilayahIds);

        $totalPenerima = (clone $statistik)->count();

        $menungguVerifikasi = (clone $statistik)
            ->where('status', 'diajukan')
            ->count();

        // Pilihan kategori
        $kategori = KategoriBantuan::orderBy('nama')->get();

        // Pilihan wilayah RT/RW dalam cakupan admin
        $wilayah = Wilayah::whereIn('id_wilayah', $wilayahIds)
            ->whereIn('tipe', ['rt', 'rw'])
            ->orderBy('tipe')
            ->orderBy('nama')
            ->get();

        // Peta wilayah untuk menampilkan pasangan RT/RW
        $wilayahMap = Wilayah::whereIn('id_wilayah', $wilayahIds)
            ->get()
            ->keyBy('id_wilayah');

        return view('admin.penerima.index', compact(
            'penerima',
            'kategori',
            'wilayah',
            'wilayahMap',
            'totalPenerima',
            'menungguVerifikasi'
        ));
    }

    public function create()
    {
        $wilayah = $this->getWilayahForm();

        $jenisBantuan = JenisBantuan::where('aktif', 1)
            ->orderBy('nama_program')
            ->get();

        return view('admin.penerima.form', [
            'p' => new Penerima,
            'wilayah' => $wilayah,
            'jenisBantuan' => $jenisBantuan,
        ]);
    }


    public function store(Request $r)
    {
        $d = $this->validateData($r);

        $nik = $d['nik'];
        $noKk = $d['no_kk'] ?? null;

        // Hapus data yang bukan kolom tabel penerima.
        unset(
            $d['nik'],
            $d['no_kk'],
            $d['id_rw'],
            $d['dokumen_ktp'],
            $d['dokumen_kk'],
            $d['dokumen_dtks_p3ke'],
            $d['foto_rumah_depan'],
            $d['foto_rumah_dalam'],
            $d['dokumen_sktm']
        );

        // Enkripsi NIK dan buat hash untuk pencarian.
        $d['nik_hash'] = hash_hmac(
            'sha256',
            $nik,
            config('app.key')
        );

        $d['nik'] = encrypt($nik);
        $d['no_kk'] = $noKk ? encrypt($noKk) : null;

        $d['status'] = 'terverifikasi';

        $d['disabilitas'] = $r->boolean('disabilitas');
        $d['lansia'] = $r->boolean('lansia');

        abort_if(
            Penerima::where('nik_hash', $d['nik_hash'])->exists(),
            422,
            'NIK sudah terdaftar.'
        );

        $storedPaths = [];

        try {
            $p = DB::transaction(function () use ($d, $r, &$storedPaths) {

                $p = Penerima::create($d);

                $this->saveDocument(
                    $p,
                    $r->file('dokumen_ktp'),
                    'ktp',
                    $storedPaths
                );

                $this->saveDocument(
                    $p,
                    $r->file('dokumen_kk'),
                    'kk',
                    $storedPaths
                );

                $this->saveDocument(
                    $p,
                    $r->file('dokumen_dtks_p3ke'),
                    'lainnya',
                    $storedPaths,
                    'Bukti DTKS/P3KE'
                );

                $this->saveDocument(
                    $p,
                    $r->file('foto_rumah_depan'),
                    'lainnya',
                    $storedPaths,
                    'Foto Rumah Depan'
                );

                $this->saveDocument(
                    $p,
                    $r->file('foto_rumah_dalam'),
                    'lainnya',
                    $storedPaths,
                    'Foto Rumah Dalam'
                );

                $this->saveDocument(
                    $p,
                    $r->file('dokumen_sktm'),
                    'surat_keterangan',
                    $storedPaths,
                    'SKTM'
                );

                $this->log(
                    'tambah',
                    'penerima',
                    $p->id_penerima,
                    null,
                    $p->toArray()
                );

                return $p;
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($storedPaths);
            throw $e;
        }

        return redirect()
            ->route('admin.penerima.index')
            ->with('success', 'Data penerima berhasil ditambahkan.');
    }


    public function show(Penerima $penerima)
    {
        $this->authorizeScope($penerima);

        $penerima->load([
            'wilayah',
            'riwayat.jenis.kategori',
            'dokumen',
        ]);

        $wilayahMap = Wilayah::all()->keyBy('id_wilayah');

        $totalDiterima = $penerima->riwayat->sum(function ($item) {
            return (float) ($item->jumlah ?? 0);
        });

        $kategoriAktif = $penerima->riwayat
            ->map(function ($item) {
                return optional(
                    optional($item->jenis)->kategori
                )->nama;
            })
            ->filter()
            ->unique()
            ->values();

        return view('admin.penerima.show', compact(
            'penerima',
            'wilayahMap',
            'totalDiterima',
            'kategoriAktif'
        ));
    }
    
    public function edit(Penerima $penerima)
    {
        $this->authorizeScope($penerima);

        $wilayah = $this->getWilayahForm();

        $jenisBantuan = JenisBantuan::where('aktif', 1)
            ->orWhere('id_jenis', $penerima->id_jenis)
            ->orderBy('nama_program')
            ->get();

        return view('admin.penerima.form', [
            'p' => $penerima,
            'wilayah' => $wilayah,
            'jenisBantuan' => $jenisBantuan,
        ]);
    }

    private function getWilayahForm()
    {
        $wilayahIds = WilayahScope::ids(
            (int) auth()->user()->id_wilayah
        );

        // Sertakan RW induk dari RT yang masuk cakupan admin.
        $parentIds = Wilayah::whereIn('id_wilayah', $wilayahIds)
            ->where('tipe', 'rt')
            ->pluck('id_induk')
            ->filter()
            ->all();

        $allIds = array_unique(array_merge($wilayahIds, $parentIds));

        return Wilayah::whereIn('id_wilayah', $allIds)
            ->orderBy('nama')
            ->get();
    }
    
    public function update(Request $r, Penerima $penerima)
    {
        $this->authorizeScope($penerima);

        $old = $penerima->toArray();

        $d = $this->validateData($r, true, $penerima);

        $nik = $d['nik'] ?? null;
        $noKk = $d['no_kk'] ?? null;

        // Hapus input yang bukan kolom tabel penerima.
        unset(
            $d['nik'],
            $d['no_kk'],
            $d['id_rw'],
            $d['dokumen_ktp'],
            $d['dokumen_kk'],
            $d['dokumen_dtks_p3ke'],
            $d['foto_rumah_depan'],
            $d['foto_rumah_dalam'],
            $d['dokumen_sktm']
        );

        // Enkripsi NIK dan buat hash pencarian.
        if ($nik !== null) {
            $nikHash = hash_hmac(
                'sha256',
                $nik,
                config('app.key')
            );

            abort_if(
                Penerima::where('nik_hash', $nikHash)
                    ->whereKeyNot($penerima->getKey())
                    ->exists(),
                422,
                'NIK sudah digunakan.'
            );

            $d['nik_hash'] = $nikHash;
            $d['nik'] = encrypt($nik);
        }

        if (filled($noKk)) {
            $d['no_kk'] = encrypt($noKk);
        } else {
            unset($d['no_kk']);
        }

        $d['disabilitas'] = $r->boolean('disabilitas');
        $d['lansia'] = $r->boolean('lansia');

        $storedPaths = [];
        $oldPaths = [];

        // Daftar seluruh dokumen yang dapat diganti.
        $documentInputs = [
            'dokumen_ktp' => [
                'jenis' => 'ktp',
                'label' => null,
            ],
            'dokumen_kk' => [
                'jenis' => 'kk',
                'label' => null,
            ],
            'dokumen_dtks_p3ke' => [
                'jenis' => 'lainnya',
                'label' => 'Bukti DTKS/P3KE',
            ],
            'foto_rumah_depan' => [
                'jenis' => 'lainnya',
                'label' => 'Foto Rumah Depan',
            ],
            'foto_rumah_dalam' => [
                'jenis' => 'lainnya',
                'label' => 'Foto Rumah Dalam',
            ],
            'dokumen_sktm' => [
                'jenis' => 'surat_keterangan',
                'label' => 'SKTM',
            ],
        ];

        try {
            DB::transaction(function () use (
                $penerima,
                $d,
                $r,
                &$storedPaths,
                &$oldPaths,
                $old,
                $documentInputs
            ) {
                $penerima->update($d);

                // Simpan dokumen yang dipilih admin.
                foreach ($documentInputs as $input => $config) {
                    if (!$r->hasFile($input)) {
                        continue;
                    }

                    $this->saveDocument(
                        $penerima,
                        $r->file($input),
                        $config['jenis'],
                        $storedPaths,
                        $config['label'],
                        $oldPaths
                    );
                }

                $this->log(
                    'ubah',
                    'penerima',
                    $penerima->id_penerima,
                    $old,
                    $penerima->fresh()->toArray()
                );
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($storedPaths);
            throw $e;
        }

        // Hapus file lama setelah transaksi berhasil.
        Storage::disk('public')->delete($oldPaths);

        return redirect()
            ->route('admin.penerima.show', $penerima)
            ->with('success', 'Data penerima berhasil diperbarui.');
    }


    private function saveDocument(
        Penerima $p,
        $file,
        string $jenis,
        array &$storedPaths,
        ?string $slotLabel = null,
        array &$oldPaths = []
    ): void {
        if (!$file) {
            return;
        }

        $path = $file->store('dokumen-penerima', 'public');

        if (!$path) {
            throw new \RuntimeException('Dokumen gagal disimpan.');
        }

        $storedPaths[] = $path;

        $query = $p->dokumen()->where('jenis_dokumen', $jenis);

        // Dokumen tambahan dicari berdasarkan label slot,
        // bukan hanya jenis_dokumen.
        if ($slotLabel !== null) {
            $query->where('nama_file', 'like', $slotLabel . ' - %');
        }

        $dokumen = $query->first();

        $originalName = $file->getClientOriginalName();

        $namaFile = $slotLabel !== null
            ? $slotLabel . ' - ' . $originalName
            : $originalName;

        $data = [
            'nama_file' => Str::limit($namaFile, 150, ''),
            'path_file' => $path,
            'diunggah_oleh' => auth()->user()->id_user,
        ];

        if ($dokumen) {
            $oldPaths[] = $dokumen->path_file;
            $dokumen->update($data);
        } else {
            $p->dokumen()->create(array_merge($data, [
                'jenis_dokumen' => $jenis,
            ]));
        }
    }

    public function destroy(Penerima $penerima)
    {
        $this->authorizeScope($penerima);

        $memilikiRiwayat = $penerima->riwayat()->exists();
        $memilikiDokumen = $penerima->dokumen()->exists();
        $memilikiPengajuan = $penerima
            ->pengajuanPerbaikan()
            ->exists();

        if (
            $memilikiRiwayat ||
            $memilikiDokumen ||
            $memilikiPengajuan
        ) {
            return back()->withErrors([
                'delete' => 'Data masih memiliki riwayat administrasi. Gunakan fitur nonaktifkan.',
            ]);
        }

        $old = $penerima->toArray();
        $id = $penerima->id_penerima;

        $penerima->delete();

        $this->log(
            'hapus',
            'penerima',
            $id,
            $old,
            null
        );

        return redirect()
            ->route('admin.penerima.index')
            ->with('success', 'Data berhasil dihapus.');
    }

    public function nonaktifkan(Penerima $penerima)
    {
        $this->authorizeScope($penerima);

        $penerima->update([
            'status' => 'nonaktif',
        ]);

        $this->log(
            'ubah',
            'penerima',
            $penerima->id_penerima,
            null,
            ['status' => 'nonaktif']
        );

        return back()->with('success', 'Penerima dinonaktifkan.');
    }

    private function validateData(
        Request $r,
        bool $update = false,
        ?Penerima $p = null
    ) {
        /*
        |--------------------------------------------------------------------------
        | CAKUPAN WILAYAH ADMIN
        |--------------------------------------------------------------------------
        */

        $wilayahIds = WilayahScope::ids(
            (int) auth()->user()->id_wilayah
        );

        $rtIds = Wilayah::whereIn('id_wilayah', $wilayahIds)
            ->where('tipe', 'rt')
            ->pluck('id_wilayah')
            ->all();

        $rwIds = Wilayah::whereIn('id_wilayah', $wilayahIds)
            ->where('tipe', 'rw')
            ->pluck('id_wilayah')
            ->all();

        // Sertakan RW induk dari RT yang masuk cakupan admin.
        $parentRwIds = Wilayah::whereIn('id_wilayah', $rtIds)
            ->pluck('id_induk')
            ->filter()
            ->all();

        $rwIds = array_unique(
            array_merge($rwIds, $parentRwIds)
        );

        /*
        |--------------------------------------------------------------------------
        | CEK DOKUMEN YANG SUDAH ADA
        |--------------------------------------------------------------------------
        */

        $hasKtp = $update && $p
            ? $p->dokumen()
                ->where('jenis_dokumen', 'ktp')
                ->exists()
            : false;

        $hasKk = $update && $p
            ? $p->dokumen()
                ->where('jenis_dokumen', 'kk')
                ->exists()
            : false;

        $hasDtks = $update && $p
            ? $p->dokumen()
                ->where('jenis_dokumen', 'lainnya')
                ->where('nama_file', 'like', 'Bukti DTKS/P3KE - %')
                ->exists()
            : false;

        $hasFotoDepan = $update && $p
            ? $p->dokumen()
                ->where('jenis_dokumen', 'lainnya')
                ->where('nama_file', 'like', 'Foto Rumah Depan - %')
                ->exists()
            : false;

        $hasFotoDalam = $update && $p
            ? $p->dokumen()
                ->where('jenis_dokumen', 'lainnya')
                ->where('nama_file', 'like', 'Foto Rumah Dalam - %')
                ->exists()
            : false;

        $hasSktm = $update && $p
            ? $p->dokumen()
                ->where('jenis_dokumen', 'surat_keterangan')
                ->where('nama_file', 'like', 'SKTM - %')
                ->exists()
            : false;

        /*
        |--------------------------------------------------------------------------
        | VALIDASI PROGRAM BANTUAN
        |--------------------------------------------------------------------------
        |
        | Program aktif boleh dipilih.
        | Saat edit, program lama yang sudah nonaktif tetap boleh dipertahankan.
        |
        */

        $jenisBantuanRule = Rule::exists(
            'jenis_bantuan',
            'id_jenis'
        )->where(function ($query) use ($update, $p) {
            $query->where('aktif', 1);

            if ($update && $p) {
                $query->orWhere('id_jenis', $p->id_jenis);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | VALIDASI DATA
        |--------------------------------------------------------------------------
        */

        return $r->validate([

            // Identitas
            // Saat edit, NIK boleh dikosongkan bila tidak ingin diubah
            // (mis. nilai lama tidak dapat didekripsi karena kunci berbeda).
            'nik' => $update
                ? 'nullable|digits:16'
                : 'required|digits:16',

            'no_kk' => [
                'nullable',
                'digits:16',
            ],

            'nama' => [
                'required',
                'string',
                'max:100',
            ],

            'tanggal_lahir' => [
                'required',
                'date',
            ],

            'jenis_kelamin' => [
                'required',
                'in:L,P',
            ],

            'nomor_whatsapp' => [
                'required',
                'string',
                'max:20',
            ],

            'id_jenis' => [
                'required',
                'integer',
                $jenisBantuanRule,
            ],

            'alamat' => [
                'required',
                'string',
            ],

            // Wilayah
            'id_rw' => [
                'required',
                'integer',
                Rule::in($rwIds),
            ],

            'id_wilayah' => [
                'required',
                'integer',
                Rule::in($rtIds),
                Rule::exists('wilayah', 'id_wilayah')
                    ->where(function ($query) use ($r) {
                        $query->where(
                            'id_induk',
                            $r->input('id_rw')
                        )->where('tipe', 'rt');
                    }),
            ],

            // Kondisi ekonomi
            'penghasilan_bulanan' => [
                'required',
                'numeric',
                'min:0',
            ],

            'jumlah_tanggungan' => [
                'required',
                'integer',
                'min:0',
                'max:99',
            ],

            'kondisi_rumah' => [
                'required',
                'in:permanen,semi_permanen,tidak_layak',
            ],

            // Kondisi khusus
            'disabilitas' => [
                'nullable',
                'boolean',
            ],

            'lansia' => [
                'nullable',
                'boolean',
            ],

            // Dokumen KTP
            'dokumen_ktp' => [
                $hasKtp ? 'nullable' : 'required',
                'file',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],

            // Dokumen KK
            'dokumen_kk' => [
                $hasKk ? 'nullable' : 'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:2048',
            ],

            // Bukti DTKS / P3KE
            'dokumen_dtks_p3ke' => [
                $hasDtks ? 'nullable' : 'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:2048',
            ],

            // Foto rumah depan
            'foto_rumah_depan' => [
                $hasFotoDepan ? 'nullable' : 'required',
                'file',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],

            // Foto rumah dalam
            'foto_rumah_dalam' => [
                $hasFotoDalam ? 'nullable' : 'required',
                'file',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],

            // SKTM
            'dokumen_sktm' => [
                $hasSktm ? 'nullable' : 'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:2048',
            ],

        ]);
    }

    private function authorizeScope($penerima): void
    {
        $wilayahIds = WilayahScope::ids(
            (int) auth()->user()->id_wilayah
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
    }

    private function log($a, $t, $id, $old, $new)
    {
        LogAktivitas::create([
            'id_user' => auth()->id(),
            'aksi' => $a,
            'tabel' => $t,
            'id_record' => $id,
            'data_lama' => $old,
            'data_baru' => $new,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
