<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penerima;
use App\Models\Wilayah;
use App\Models\LogAktivitas;
use App\Support\WilayahScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;
use App\Models\JenisBantuan;
use App\Models\PengajuanBantuan;
use App\Models\DokumenPendukung;
use App\Models\RiwayatBantuan;
use Throwable;

class PengajuanController extends Controller
{
    public function create()
    {
        $penerima = Penerima::where(
            'id_user',
            auth()->user()->id_user
        )->first();

        if (!$penerima) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Profil penerima belum tersedia. Lengkapi pendaftaran terlebih dahulu.');
        }

        if (!in_array($penerima->status, ['terverifikasi', 'nonaktif'], true)) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Profil Anda harus diverifikasi admin sebelum dapat mengajukan bantuan.');
        }

        // Dekripsi NIK untuk ditampilkan pada formulir.
        $nik = null;

        if (!empty($penerima->nik)) {
            try {
                $nik = decrypt($penerima->nik);
            } catch (Throwable $e) {
                $nik = null;
            }
        }

        return view('masyarakat.pengajuan', [
            'penerima' => $penerima,
            'nik' => $nik,
            'wilayah' => Wilayah::orderBy('nama')->get(),
            'jenisBantuan' => JenisBantuan::where('aktif', 1)
                ->orderBy('nama_program')
                ->get(),
        ]);
    }

    public function store(Request $r)
    {
        $idUser = auth()->user()->id_user;

        // Profil penerima wajib sudah tersedia.
        $penerimaAwal = Penerima::where('id_user', $idUser)->first();

        if (!$penerimaAwal) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Profil penerima belum tersedia. Lengkapi pendaftaran terlebih dahulu.');
        }

        // Profil harus diverifikasi sebelum pengajuan bantuan.
        if (!in_array($penerimaAwal->status, ['terverifikasi', 'nonaktif'], true)) {
            return back()
                ->with('error', 'Profil Anda harus diverifikasi admin sebelum dapat mengajukan bantuan.');
        }

        // Validasi data identitas hanya untuk masyarakat baru.
        $aturan = [
            'nomor_whatsapp' => 'required|string|max:20',
            'alamat' => 'required|string',

            'id_rw' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('wilayah', 'id_wilayah')
                    ->where('tipe', 'rw')
                    ->whereIn(
                        'id_induk',
                        Wilayah::whereIn('tipe', ['desa', 'kelurahan'])
                            ->select('id_wilayah')
                    ),
            ],

            'id_wilayah' => [
                'required',
                \Illuminate\Validation\Rule::exists('wilayah', 'id_wilayah')
                    ->where('tipe', 'rt')
                    ->where('id_induk', $r->input('id_rw')),
            ],

            'id_jenis' => [
                'required',
                'integer',
                \Illuminate\Validation\Rule::exists('jenis_bantuan', 'id_jenis')
                    ->where('aktif', 1),
            ],

            'penghasilan_bulanan' => 'required|numeric|min:0',
            'jumlah_tanggungan' => 'required|integer|min:0',
            'kondisi_rumah' => 'required|in:permanen,semi_permanen,tidak_layak',
            'disabilitas' => 'nullable|boolean',
            'lansia' => 'nullable|boolean',

            'dokumen_ktp' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'dokumen_kk' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'dokumen_dtks_p3ke' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'foto_rumah_depan' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'foto_rumah_dalam' => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'dokumen_sktm' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];

        $d = $r->validate($aturan);

        $files = [
            'dokumen_ktp' => [
                'jenis' => 'ktp',
                'prefix' => null,
            ],
            'dokumen_kk' => [
                'jenis' => 'kk',
                'prefix' => null,
            ],
            'dokumen_dtks_p3ke' => [
                'jenis' => 'lainnya',
                'prefix' => 'Bukti DTKS/P3KE',
            ],
            'foto_rumah_depan' => [
                'jenis' => 'lainnya',
                'prefix' => 'Foto Rumah Depan',
            ],
            'foto_rumah_dalam' => [
                'jenis' => 'lainnya',
                'prefix' => 'Foto Rumah Dalam',
            ],
            'dokumen_sktm' => [
                'jenis' => 'surat_keterangan',
                'prefix' => 'SKTM',
            ],
        ];

        $storedPaths = [];

        try {
            $pengajuan = DB::transaction(function () use ($r, $d, $idUser, $penerimaAwal, $files, &$storedPaths) {
                // Kunci akun agar dua pengajuan bersamaan tidak lolos.
                \App\Models\User::where('id_user', $idUser)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Ambil ulang profil setelah akun dikunci.
                $penerima = Penerima::where('id_user', $idUser)
                    ->lockForUpdate()
                    ->first();

                // BLT-DD: masa tunggu 12 bulan penuh sejak bantuan benar-benar diterima.
                if ($penerima && (int) $d['id_jenis'] === 7) {
                    $tanggalTerakhir = RiwayatBantuan::where(
                        'id_penerima',
                        $penerima->id_penerima
                    )
                        ->where('id_jenis', 7)
                        ->max('tanggal_terima');

                    if ($tanggalTerakhir) {
                        $tanggalBolehMengajukan = \Illuminate\Support\Carbon::parse(
                            $tanggalTerakhir
                        )->addMonthsNoOverflow(12);

                        if (now()->lt($tanggalBolehMengajukan)) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'id_jenis' => 'Anda belum dapat mengajukan BLT-DD kembali. Anda dapat mengajukan mulai tanggal ' . $tanggalBolehMengajukan->format('d-m-Y') . '.',
                            ]);
                        }
                    }
                }

                // Cegah pengajuan ganda yang masih menunggu verifikasi.
                if ($penerima) {
                    $statusPenghalang = $penerima->status === 'nonaktif'
                        ? ['diajukan']
                        : ['diajukan', 'terverifikasi'];

                    $masihAktif = PengajuanBantuan::where(
                        'id_penerima',
                        $penerima->id_penerima
                    )
                        ->whereIn('status', $statusPenghalang)
                        ->exists();

                    if ($masihAktif) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'id_jenis' => 'Anda masih memiliki pengajuan atau bantuan aktif. Selesaikan status pengajuan sebelumnya terlebih dahulu.',
                        ]);
                    }
                }

                // Pastikan profil penerima masih tersedia setelah akun dikunci.
                if (!$penerima) {
                    throw new \RuntimeException(
                        'Profil penerima tidak ditemukan. Silakan hubungi admin.'
                    );
                }

                // Pastikan status profil belum berubah selama proses berlangsung.
                if (!in_array($penerima->status, ['terverifikasi', 'nonaktif'], true)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'id_jenis' => 'Profil Anda harus diverifikasi admin sebelum dapat mengajukan bantuan.',
                    ]);
                }

                // Ambil nama RT dan RW untuk snapshot pengajuan.
                $rt = Wilayah::findOrFail($d['id_wilayah']);
                $rw = Wilayah::findOrFail($d['id_rw']);

                $snapshot = [
                    'alamat' => $d['alamat'],
                    'nomor_whatsapp' => $d['nomor_whatsapp'],
                    'id_wilayah' => $rt->id_wilayah,
                    'nama_rt' => $rt->nama,
                    'id_rw' => $rw->id_wilayah,
                    'nama_rw' => $rw->nama,
                    'penghasilan_bulanan' => $d['penghasilan_bulanan'],
                    'jumlah_tanggungan' => $d['jumlah_tanggungan'],
                    'kondisi_rumah' => $d['kondisi_rumah'],
                    'disabilitas' => $r->boolean('disabilitas'),
                    'lansia' => $r->boolean('lansia'),
                ];

                // Buat riwayat pengajuan baru.
                $pengajuan = PengajuanBantuan::create([
                    'id_penerima' => $penerima->id_penerima,
                    'id_jenis' => $d['id_jenis'],
                    'status' => 'diajukan',
                    'data_penerima' => $snapshot,
                ]);

                // Simpan dokumen dan hubungkan ke pengajuan baru.
                foreach ($files as $field => $config) {
                    $file = $r->file($field);

                    $path = $file->store('dokumen-penerima', 'local');

                    if (!$path) {
                        throw new \RuntimeException(
                            'Dokumen gagal disimpan: ' . $field
                        );
                    }

                    $storedPaths[] = $path;

                    $originalName = $file->getClientOriginalName();

                    $namaFile = $config['prefix']
                        ? $config['prefix'] . ' - ' . $originalName
                        : $originalName;

                    $penerima->dokumen()->create([
                        'id_pengajuan' => $pengajuan->id_pengajuan,
                        'jenis_dokumen' => $config['jenis'],
                        'nama_file' => \Illuminate\Support\Str::limit(
                            $namaFile,
                            150,
                            ''
                        ),
                        'path_file' => $path,
                        'diunggah_oleh' => $idUser,
                    ]);
                }

                return $pengajuan;
            });
        } catch (Throwable $e) {
            Storage::disk('local')->delete($storedPaths);
            throw $e;
        }

        ActivityLogger::record(
            'pengajuan',
            'pengajuan_bantuan',
            $pengajuan->id_pengajuan,
            null,
            [
                'id_penerima' => $pengajuan->id_penerima,
                'id_jenis' => $pengajuan->id_jenis,
                'status' => $pengajuan->status,
            ]
        );

        return redirect()
            ->route('masyarakat.status')
            ->with(
                'success',
                'Pengajuan berhasil dikirim dan menunggu verifikasi admin.'
            );
    }

    public function index(Request $request)
    {
        // Tentukan cakupan wilayah admin.
        $idWilayahAdmin = auth()->user()->id_wilayah;
        $wilayahIds = $idWilayahAdmin
            ? WilayahScope::ids($idWilayahAdmin)
            : null;

        // Query dasar untuk semua status pengajuan.
        $queryDasar = PengajuanBantuan::query();

        if ($wilayahIds !== null) {
            $queryDasar->where(function ($query) use ($wilayahIds) {
                $query->whereIn(
                    'data_penerima->id_wilayah',
                    $wilayahIds
                );

                $query->orWhere(function ($legacy) use ($wilayahIds) {
                    $legacy->whereNull('data_penerima')
                        ->whereHas('penerima', function ($penerima) use ($wilayahIds) {
                            $penerima->whereIn(
                                'id_wilayah',
                                $wilayahIds
                            );
                        });
                });
            });
        }

        // Ringkasan jumlah pengajuan sesuai cakupan admin.
        $totalMenunggu = (clone $queryDasar)
            ->where('status', 'diajukan')
            ->count();

        $totalTerverifikasi = (clone $queryDasar)
            ->where('status', 'terverifikasi')
            ->count();

        $totalDitolak = (clone $queryDasar)
            ->where('status', 'ditolak')
            ->count();

        // Daftar pengajuan yang masih menunggu verifikasi.
        $pengajuan = (clone $queryDasar)
            ->with([
                'penerima',
                'jenisBantuan',
                'dokumen',
            ])
            ->where('status', 'diajukan')
            ->orderBy('created_at')
            ->orderBy('id_pengajuan')
            ->paginate(10)
            ->withQueryString();

        $pilihan = null;

        if ($request->filled('pilih')) {
            $pilihan = (clone $queryDasar)
                ->with([
                    'penerima',
                    'jenisBantuan',
                    'dokumen',
                ])
                ->where('status', 'diajukan')
                ->where('id_pengajuan', $request->input('pilih'))
                ->first();
        }

        $pilihan = $pilihan ?? $pengajuan->first();

        return view('admin.pengajuan.index', compact(
            'pengajuan',
            'totalMenunggu',
            'totalTerverifikasi',
            'totalDitolak',
            'pilihan'
        ));
    }


    public function verifikasi(PengajuanBantuan $pengajuan)
    {
        // Pastikan pengajuan masih menunggu verifikasi.
        abort_unless(
            $pengajuan->status === 'diajukan',
            422,
            'Pengajuan ini sudah pernah diproses.'
        );

        // Pastikan pengajuan berada dalam wilayah admin.
        $this->scope($pengajuan);

        DB::transaction(function () use ($pengajuan) {
            $data = PengajuanBantuan::where(
                'id_pengajuan',
                $pengajuan->id_pengajuan
            )
                ->lockForUpdate()
                ->firstOrFail();

            // Periksa ulang untuk mencegah pemrosesan ganda.
            abort_unless(
                $data->status === 'diajukan',
                422,
                'Pengajuan ini sudah pernah diproses.'
            );

            $data->update([
                'status' => 'terverifikasi',
                'diverifikasi_oleh' => auth()->user()->id_user,
                'tanggal_verifikasi' => now(),
                'catatan_verifikasi' => null,
            ]);
        });

        $pengajuan->refresh();

        ActivityLogger::record(
            'verifikasi',
            'pengajuan_bantuan',
            $pengajuan->id_pengajuan,
            ['status' => 'diajukan'],
            ['status' => 'terverifikasi']
        );

        return back()->with(
            'success',
            'Pengajuan berhasil disetujui.'
        );
    }

    public function tolak(Request $r, PengajuanBantuan $pengajuan)
    {
        // Pastikan pengajuan masih menunggu verifikasi.
        abort_unless(
            $pengajuan->status === 'diajukan',
            422,
            'Pengajuan ini sudah pernah diproses.'
        );

        // Pastikan pengajuan berada dalam wilayah admin.
        $this->scope($pengajuan);

        $d = $r->validate([
            'catatan' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($pengajuan, $d) {
            $data = PengajuanBantuan::where(
                'id_pengajuan',
                $pengajuan->id_pengajuan
            )
                ->lockForUpdate()
                ->firstOrFail();

            // Periksa ulang untuk mencegah pemrosesan ganda.
            abort_unless(
                $data->status === 'diajukan',
                422,
                'Pengajuan ini sudah pernah diproses.'
            );

            $data->update([
                'status' => 'ditolak',
                'diverifikasi_oleh' => auth()->user()->id_user,
                'tanggal_verifikasi' => now(),
                'catatan_verifikasi' => $d['catatan'],
            ]);
        });

        $pengajuan->refresh();

        ActivityLogger::record(
            'tolak',
            'pengajuan_bantuan',
            $pengajuan->id_pengajuan,
            ['status' => 'diajukan'],
            [
                'status' => 'ditolak',
                'catatan_verifikasi' => $pengajuan->catatan_verifikasi,
            ]
        );

        return back()->with(
            'success',
            'Pengajuan berhasil ditolak.'
        );
    }

    public function status()
    {
        $p = Penerima::with([
            'riwayat.jenis.kategori',
            'pengajuanBantuan.jenisBantuan',
            'pengajuanBantuan.dokumen',
        ])
            ->where('id_user', auth()->user()->id_user)
            ->first();

        return view('masyarakat.status', compact('p'));
    }

    public function showDocument($pengajuanId, $dokumenId)
    {
        // Ambil pengajuan beserta data penerimanya.
        $pengajuan = PengajuanBantuan::with('penerima')
            ->findOrFail($pengajuanId);

        // Pastikan pengajuan berada dalam wilayah tugas admin.
        $this->scope($pengajuan);

        // Ambil dokumen yang benar-benar terhubung
        // dengan pengajuan yang diminta.
        $dokumen = DokumenPendukung::where(
            'id_dokumen',
            $dokumenId
        )
            ->where('id_pengajuan', $pengajuan->id_pengajuan)
            ->firstOrFail();

        // Pastikan file tersedia di penyimpanan privat.
        $disk = Storage::disk('local');

        abort_unless(
            $dokumen->path_file &&
            $disk->exists($dokumen->path_file),
            404,
            'Dokumen tidak ditemukan.'
        );

        // Tentukan tipe file.
        $mimeType = $disk->mimeType($dokumen->path_file)
            ?: 'application/octet-stream';

        // Tampilkan dokumen secara inline.
        return $disk->response(
            $dokumen->path_file,
            basename($dokumen->nama_file),
            [
                'Content-Type' => $mimeType,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store, max-age=0',
            ],
            'inline'
        );
    }

    private function scope(PengajuanBantuan $pengajuan)
    {
        $idWilayahAdmin = auth()->user()->id_wilayah;

        // Admin tanpa batasan wilayah dapat mengakses semua wilayah.
        if (!$idWilayahAdmin) {
            return;
        }

        $wilayahIds = collect(
            WilayahScope::ids($idWilayahAdmin)
        )->map(fn($id) => (int) $id);

        // Utamakan wilayah yang tersimpan dalam snapshot pengajuan.
        $idWilayahPengajuan = data_get(
            $pengajuan->data_penerima,
            'id_wilayah'
        );

        // Pengajuan lama yang belum memiliki snapshot.
        if (!$idWilayahPengajuan) {
            $idWilayahPengajuan = $pengajuan->penerima?->id_wilayah;
        }

        abort_if(
            !$idWilayahPengajuan ||
            !$wilayahIds->contains((int) $idWilayahPengajuan),
            403
        );
    }

    private function log($p, $a)
    {
        LogAktivitas::create([
            'id_user' => auth()->id(),
            'aksi' => $a,
            'tabel' => 'penerima',
            'id_record' => $p->id_penerima,
            'data_baru' => [
                'status' => $p->status,
            ],
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}

