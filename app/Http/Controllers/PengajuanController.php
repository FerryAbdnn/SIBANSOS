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

        // Dekripsi NIK untuk ditampilkan pada formulir.
        $nik = null;

        if ($penerima && !empty($penerima->nik)) {
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

        // Ambil profil masyarakat jika sudah terdaftar.
        $penerimaAwal = Penerima::where('id_user', $idUser)->first();

        // Validasi data identitas hanya untuk masyarakat baru.
        $aturan = [
            'nomor_whatsapp' => 'required|string|max:20',
            'alamat' => 'required|string',

            'id_rw' => [
                'required',
                \Illuminate\Validation\Rule::exists('wilayah', 'id_wilayah')
                    ->where('tipe', 'rw'),
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

        if (!$penerimaAwal) {
            $aturan = array_merge($aturan, [
                'nik' => 'required|digits:16',
                'no_kk' => 'required|digits:16',
                'nama' => 'required|string|max:100',
                'tanggal_lahir' => 'required|date',
                'jenis_kelamin' => 'required|in:L,P',
            ]);
        }

        $d = $r->validate($aturan);

        // Pastikan NIK masyarakat baru belum digunakan.
        if (!$penerimaAwal) {
            $hashNik = hash_hmac('sha256', $d['nik'], config('app.key'));

            if (Penerima::where('nik_hash', $hashNik)->exists()) {
                return back()
                    ->withErrors(['nik' => 'NIK sudah terdaftar.'])
                    ->withInput();
            }
        }

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
            $pengajuan = DB::transaction(function () use (
                $r,
                $d,
                $idUser,
                $penerimaAwal,
                $files,
                &$storedPaths
            ) {
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

                // Cegah pengajuan bersamaan pada program lain.
                if ($penerima) {
                    $masihAktif = PengajuanBantuan::where(
                        'id_penerima',
                        $penerima->id_penerima
                    )
                        ->whereIn('status', ['diajukan', 'terverifikasi'])
                        ->exists();

                    if ($masihAktif) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'id_jenis' => 'Anda masih memiliki pengajuan atau bantuan aktif. Selesaikan status pengajuan sebelumnya terlebih dahulu.',
                        ]);
                    }
                }

                // Data identitas hanya dibuat untuk masyarakat baru.
                if (!$penerima) {
                    $hashNik = hash_hmac('sha256', $d['nik'], config('app.key'));

                    if (Penerima::where('nik_hash', $hashNik)->exists()) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'nik' => 'NIK sudah terdaftar.',
                        ]);
                    }

                    $penerima = Penerima::create([
                        'nik' => encrypt($d['nik']),
                        'nik_hash' => $hashNik,
                        'no_kk' => encrypt($d['no_kk']),
                        'nama' => $d['nama'],
                        'tanggal_lahir' => $d['tanggal_lahir'],
                        'jenis_kelamin' => $d['jenis_kelamin'],
                        'id_user' => $idUser,
                        'status' => 'diajukan',
                        'nomor_whatsapp' => $d['nomor_whatsapp'],
                        'alamat' => $d['alamat'],
                        'id_wilayah' => $d['id_wilayah'],
                        'penghasilan_bulanan' => $d['penghasilan_bulanan'],
                        'jumlah_tanggungan' => $d['jumlah_tanggungan'],
                        'kondisi_rumah' => $d['kondisi_rumah'],
                        'disabilitas' => $r->boolean('disabilitas'),
                        'lansia' => $r->boolean('lansia'),
                    ]);
                } else {
                    // Perbarui data profil yang boleh berubah.
                    $penerima->update([
                        'nomor_whatsapp' => $d['nomor_whatsapp'],
                        'alamat' => $d['alamat'],
                        'id_wilayah' => $d['id_wilayah'],
                        'penghasilan_bulanan' => $d['penghasilan_bulanan'],
                        'jumlah_tanggungan' => $d['jumlah_tanggungan'],
                        'kondisi_rumah' => $d['kondisi_rumah'],
                        'disabilitas' => $r->boolean('disabilitas'),
                        'lansia' => $r->boolean('lansia'),
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

                    $path = $file->store('dokumen-penerima', 'public');

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
            Storage::disk('public')->delete($storedPaths);
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

    public function index(Request $r)
    {
        $idWilayahAdmin = auth()->user()->id_wilayah;

        $wilayahIds = $idWilayahAdmin
            ? WilayahScope::ids($idWilayahAdmin)
            : [];

        $scope = function ($query) use ($wilayahIds) {
            if (!$wilayahIds) {
                return;
            }

            $query->where(function ($sub) use ($wilayahIds) {
                $sub->whereIn('data_penerima->id_wilayah', $wilayahIds);

                $sub->orWhere(function ($legacy) use ($wilayahIds) {
                    $legacy->whereNull('data_penerima')
                        ->whereHas('penerima', function ($penerima) use ($wilayahIds) {
                            $penerima->whereIn('id_wilayah', $wilayahIds);
                        });
                });
            });
        };

        // Hitungan tiap tab status.
        $hitung = [
            'diajukan' => PengajuanBantuan::where('status', 'diajukan')->tap($scope)->count(),
            'terverifikasi' => PengajuanBantuan::where('status', 'terverifikasi')->tap($scope)->count(),
            'ditolak' => PengajuanBantuan::where('status', 'ditolak')->tap($scope)->count(),
        ];

        $tab = $r->get('tab', 'diajukan');

        if (!array_key_exists($tab, $hitung)) {
            $tab = 'diajukan';
        }

        $pengajuan = PengajuanBantuan::with(['penerima', 'jenisBantuan', 'dokumen', 'verifikator'])
            ->where('status', $tab)
            ->tap($scope)
            ->latest('created_at')
            ->paginate(8)
            ->withQueryString();

        // Detail pengajuan terpilih.
        $terpilih = null;

        if ($r->filled('pilih')) {
            $terpilih = PengajuanBantuan::with(['penerima.dokumen', 'jenisBantuan.kategori', 'dokumen', 'verifikator'])
                ->where('id_pengajuan', (int) $r->pilih)
                ->tap($scope)
                ->first();
        }

        if (!$terpilih) {
            $terpilih = $pengajuan->first();
        }

        $tertua = PengajuanBantuan::where('status', 'diajukan')->tap($scope)->oldest()->value('created_at');

        return view('admin.pengajuan.index', compact('pengajuan', 'hitung', 'tab', 'terpilih', 'tertua'));
    }

    public function verifikasi(Request $r, PengajuanBantuan $pengajuan)
    {
        // Pastikan pengajuan masih menunggu verifikasi.
        abort_unless(
            $pengajuan->status === 'diajukan',
            422,
            'Pengajuan ini sudah pernah diproses.'
        );

        // Pastikan pengajuan berada dalam wilayah admin.
        $this->scope($pengajuan);

        DB::transaction(function () use ($r, $pengajuan) {
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

            $perubahan = [
                'status' => 'terverifikasi',
                'diverifikasi_oleh' => auth()->user()->id_user,
                'tanggal_verifikasi' => $r->filled('tanggal_verifikasi')
                    ? \Illuminate\Support\Carbon::parse($r->tanggal_verifikasi)
                    : now(),
                'catatan_verifikasi' => $r->filled('catatan_verifikasi')
                    ? $r->catatan_verifikasi
                    : null,
            ];

            // Kategori yang ditetapkan petugas boleh berbeda dari yang diminta warga.
            if ($r->filled('id_jenis')) {
                $valid = \App\Models\JenisBantuan::where('id_jenis', (int) $r->id_jenis)->exists();

                if ($valid) {
                    $perubahan['id_jenis'] = (int) $r->id_jenis;
                }
            }

            $data->update($perubahan);
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

        $catatan = $r->input('catatan') ?: $r->input('catatan_verifikasi');

        $d = \Illuminate\Support\Facades\Validator::make(
            ['catatan' => $catatan],
            ['catatan' => 'required|string|max:1000'],
            ['catatan.required' => 'Alasan penolakan wajib diisi.']
        )->validate();

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
        ])
            ->where('id_user', auth()->user()->id_user)
            ->first();

        return view('masyarakat.status', compact('p'));
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
        )->map(fn ($id) => (int) $id);

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
