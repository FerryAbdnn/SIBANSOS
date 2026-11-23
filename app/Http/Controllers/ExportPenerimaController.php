<?php

namespace App\Http\Controllers;

use App\Exports\DataPenerimaExport;
use App\Models\Penerima;
use App\Models\Wilayah;
use App\Models\JenisBantuan;
use App\Support\WilayahScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
use Throwable;

class ExportPenerimaController extends Controller
{
    /**
     * Menampilkan halaman ekspor data penerima.
     */
    public function index()
    {
        // Bersihkan folder ekspor sementara yang berumur lebih dari 24 jam.
        // Folder yang sedang digunakan oleh proses download tidak boleh dihapus.
        $disk = Storage::disk('local');

        $exportDirectory = 'export-penerima';

        if ($disk->exists($exportDirectory)) {
            foreach ($disk->directories($exportDirectory) as $directory) {
                $lockPath = $disk->path($directory . '/.lock');

                // Folder lama dari versi sebelumnya mungkin belum memiliki lock.
                if (!is_file($lockPath)) {
                    $lastModified = $disk->lastModified($directory);

                    if ($lastModified < now()->subDay()->timestamp) {
                        $disk->deleteDirectory($directory);
                    }

                    continue;
                }

                $cleanupLock = fopen($lockPath, 'c');

                if ($cleanupLock === false) {
                    continue;
                }

                // Jika pengunci sedang dipakai proses download, lewati folder.
                if (!flock($cleanupLock, LOCK_EX | LOCK_NB)) {
                    fclose($cleanupLock);
                    continue;
                }

                $lastModified = filemtime($lockPath);

                flock($cleanupLock, LOCK_UN);
                fclose($cleanupLock);

                if (
                    $lastModified !== false &&
                    $lastModified < now()->subDay()->timestamp
                ) {
                    $disk->deleteDirectory($directory);
                }
            }
        }

        $wilayahIds = WilayahScope::ids(
            (int) auth()->user()->id_wilayah
        );

        $wilayah = Wilayah::whereIn('id_wilayah', $wilayahIds)
            ->whereIn('tipe', ['rw', 'rt'])
            ->orderBy('tipe')
            ->orderBy('nama')
            ->get();

        $jenisBantuan = JenisBantuan::orderBy('nama_program')
            ->get();

        return view('admin.penerima.export', compact(
            'wilayah',
            'jenisBantuan'
        ));
    }

    /**
     * Membuat ZIP berisi Excel dan dokumen asli.
     */
    public function download(Request $request)
    {
        $request->validate([
            'rw' => ['nullable', 'integer'],
            'rt' => ['nullable', 'integer'],
            'id_jenis' => ['nullable', 'integer', 'exists:jenis_bantuan,id_jenis'],
            'status' => [
                'nullable',
                'in:diajukan,terverifikasi,ditolak,nonaktif',
            ],
        ]);

        $wilayahIds = WilayahScope::ids(
            (int) auth()->user()->id_wilayah
        );

        // Dokumen yang diekspor hanya dokumen milik penerima,
        // bukan dokumen yang terhubung dengan pengajuan bantuan.
        $query = Penerima::with([
            'wilayah.induk',
            'jenisBantuan',
            'dokumen' => function ($query) {
                $query->whereNull('id_pengajuan');
            },
        ])->whereIn('id_wilayah', $wilayahIds);

        // Filter RW.
        if ($request->filled('rw')) {
            $rwId = (int) $request->rw;

            $rwValid = Wilayah::whereIn(
                'id_wilayah',
                $wilayahIds
            )
                ->where('id_wilayah', $rwId)
                ->where('tipe', 'rw')
                ->exists();

            abort_unless(
                $rwValid,
                403,
                'RW tidak termasuk cakupan wilayah Anda.'
            );

            $rtIds = Wilayah::whereIn(
                'id_wilayah',
                $wilayahIds
            )
                ->where('id_induk', $rwId)
                ->where('tipe', 'rt')
                ->pluck('id_wilayah');

            $query->whereIn('id_wilayah', $rtIds);
        }

        // Filter RT.
        if ($request->filled('rt')) {
            $rtId = (int) $request->rt;

            $rtQuery = Wilayah::whereIn(
                'id_wilayah',
                $wilayahIds
            )
                ->where('id_wilayah', $rtId)
                ->where('tipe', 'rt');

            // Jika RW juga dipilih, pastikan RT merupakan anak RW tersebut.
            if ($request->filled('rw')) {
                $rtQuery->where('id_induk', (int) $request->rw);
            }

            $rtValid = $rtQuery->exists();

            abort_unless(
                $rtValid,
                403,
                'RT tidak termasuk cakupan wilayah Anda.'
            );

            $query->where('id_wilayah', $rtId);
        }

        // Filter program bantuan.
        if ($request->filled('id_jenis')) {
            $query->where('id_jenis', $request->id_jenis);
        }

        // Filter status penerima.
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $penerima = $query
            ->orderBy('id_penerima')
            ->get();

        if ($penerima->isEmpty()) {
            return back()->withErrors([
                'export' => 'Tidak ada data penerima yang sesuai dengan filter.',
            ]);
        }

        // Setiap proses ekspor menggunakan folder unik.
        $relativeDirectory = 'export-penerima/' . Str::uuid();

        $disk = Storage::disk('local');

        $disk->makeDirectory($relativeDirectory);

        $excelRelativePath = $relativeDirectory
            . '/Data_Penerima.xlsx';

        $zipRelativePath = $relativeDirectory
            . '/Data_Penerima.zip';

        $excelPath = $disk->path($excelRelativePath);
        $zipPath = $disk->path($zipRelativePath);

        // Siapkan pengunci agar folder tidak dibersihkan
        // ketika proses ekspor atau download masih berlangsung.
        $lockHandle = null;

        try {
            $lockPath = $disk->path(
                $relativeDirectory . '/.lock'
            );

            $lockHandle = fopen($lockPath, 'c');

            if ($lockHandle === false) {
                throw new \RuntimeException(
                    'Pengunci proses ekspor gagal dibuat.'
                );
            }

            if (!flock($lockHandle, LOCK_EX)) {
                throw new \RuntimeException(
                    'Pengunci proses ekspor gagal diaktifkan.'
                );
            }

            // Buat Excel di folder sementara yang unik.
            Excel::store(
                new DataPenerimaExport($penerima),
                $excelRelativePath,
                'local'
            );

            if (!$disk->exists($excelRelativePath)) {
                throw new \RuntimeException(
                    'File Excel gagal dibuat.'
                );
            }

            // Buat arsip ZIP.
            $zip = new ZipArchive();

            $opened = $zip->open(
                $zipPath,
                ZipArchive::CREATE | ZipArchive::OVERWRITE
            );

            if ($opened !== true) {
                throw new \RuntimeException(
                    'ZIP tidak dapat dibuat.'
                );
            }

            try {
                if (!$zip->addFile(
                    $excelPath,
                    'Data_Penerima.xlsx'
                )) {
                    throw new \RuntimeException(
                        'File Excel gagal dimasukkan ke ZIP.'
                    );
                }

                // Tambahkan dokumen asli setiap penerima dari penyimpanan privat.
                $privateDisk = Storage::disk('local');

                $privateRoot = realpath(
                    $privateDisk->path('')
                );

                foreach ($penerima as $p) {
                    $nik = $p->nik_decrypted ?: 'Tanpa_NIK';

                    $namaFolder = Str::slug(
                        $nik . '_' . $p->nama,
                        '_'
                    );

                    $namaTerpakai = [];

                    foreach ($p->dokumen as $dokumen) {
                        $pathFile = $dokumen->path_file;

                        if (!$pathFile || !$privateRoot) {
                            continue;
                        }

                        $source = realpath(
                            $privateDisk->path($pathFile)
                        );

                        // Pastikan file benar-benar berada
                        // di dalam direktori penyimpanan publik.
                        if (
                            !$source ||
                            !is_file($source) ||
                            !str_starts_with(
                                $source,
                                $privateRoot . DIRECTORY_SEPARATOR
                            )
                        ) {
                            continue;
                        }

                        $label = $this->documentLabel($dokumen);

                        $extension = strtolower(
                            pathinfo(
                                $pathFile,
                                PATHINFO_EXTENSION
                            )
                        );

                        if (!preg_match('/^[a-z0-9]{1,10}$/', $extension)) {
                            $extension = 'bin';
                        }

                        // Hindari nama file ZIP yang sama.
                        $namaTerpakai[$label] =
                            ($namaTerpakai[$label] ?? 0) + 1;

                        if ($namaTerpakai[$label] > 1) {
                            $label .= '_' . $namaTerpakai[$label];
                        }

                        $zipName = 'Dokumen_Pendukung/'
                            . $namaFolder . '/'
                            . $label . '.' . $extension;

                        if (!$zip->addFile($source, $zipName)) {
                            throw new \RuntimeException(
                                'Dokumen gagal dimasukkan ke ZIP.'
                            );
                        }
                    }
                }
            } finally {
                $zip->close();
            }

            if (!$disk->exists($zipRelativePath)) {
                throw new \RuntimeException(
                    'File ZIP gagal dibuat.'
                );
            }

            // Kirim ZIP dan bersihkan seluruh folder sementara
            // setelah proses pengiriman selesai.
            return response()->streamDownload(
                function () use (
                    $zipPath,
                    $disk,
                    $relativeDirectory,
                    $lockHandle
                ) {
                    $stream = null;

                    try {
                        $stream = fopen($zipPath, 'rb');

                        if ($stream === false) {
                            throw new \RuntimeException(
                                'File ZIP tidak dapat dibaca.'
                            );
                        }

                        fpassthru($stream);
                    } finally {
                        if (is_resource($stream)) {
                            fclose($stream);
                        }

                        if (is_resource($lockHandle)) {
                            flock($lockHandle, LOCK_UN);
                            fclose($lockHandle);
                        }

                        $disk->deleteDirectory($relativeDirectory);
                    }
                },
                'Data_Penerima.zip',
                [
                    'Content-Type' => 'application/zip',
                ]
            );

        } catch (Throwable $e) {
            // Lepaskan pengunci jika proses ekspor gagal.
            if (is_resource($lockHandle)) {
                flock($lockHandle, LOCK_UN);
                fclose($lockHandle);
            }

            // Hapus folder sementara.
            $disk->deleteDirectory($relativeDirectory);

            throw $e;
        }
    }

    /**
     * Menentukan nama dokumen berdasarkan data penyimpanan.
     */
    private function documentLabel($dokumen): string
    {
        if ($dokumen->jenis_dokumen === 'ktp') {
            return 'KTP';
        }

        if ($dokumen->jenis_dokumen === 'kk') {
            return 'KK';
        }

        if (str_starts_with(
            $dokumen->nama_file,
            'Bukti DTKS/P3KE - '
        )) {
            return 'DTKS_P3KE';
        }

        if (str_starts_with(
            $dokumen->nama_file,
            'Foto Rumah Depan - '
        )) {
            return 'Foto_Rumah_Depan';
        }

        if (str_starts_with(
            $dokumen->nama_file,
            'Foto Rumah Dalam - '
        )) {
            return 'Foto_Rumah_Dalam';
        }

        if (
            $dokumen->jenis_dokumen === 'surat_keterangan' &&
            str_starts_with(
                $dokumen->nama_file,
                'SKTM - '
            )
        ) {
            return 'SKTM';
        }

        return 'Dokumen_Lainnya_' . $dokumen->id_dokumen;
    }
}
