<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Wilayah;
use App\Models\Penerima;
use App\Models\KategoriBantuan;
use App\Models\JenisBantuan;
use App\Models\PengajuanBantuan;
use App\Models\RiwayatBantuan;

/**
 * Data contoh untuk keperluan pratinjau desain.
 * Jalankan: php artisan db:seed --class=DemoSeeder
 * Hapus dengan: php artisan migrate:fresh --seed
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Penerima::count() > 0) {
            $this->command->warn('Data penerima sudah ada, DemoSeeder dilewati.');
            return;
        }

        // ---------------------------------------------------------------
        // Wilayah
        // ---------------------------------------------------------------

        $desa = Wilayah::firstOrCreate(
            ['nama' => 'Desa Sukamaju'],
            ['tipe' => 'desa', 'id_induk' => null]
        );

        $daftarRw = ['RW 01', 'RW 02', 'RW 03', 'RW 04', 'RW 05'];

        $rwModel = [];

        foreach ($daftarRw as $namaRw) {
            $rwModel[$namaRw] = Wilayah::firstOrCreate(
                ['nama' => $namaRw, 'id_induk' => $desa->id_wilayah],
                ['tipe' => 'rw']
            );
        }

        $rtModel = [];

        foreach ($rwModel as $namaRw => $rw) {
            $jumlahRt = $namaRw === 'RW 01' ? 4 : 3;

            for ($i = 1; $i <= $jumlahRt; $i++) {
                $namaRt = 'RT ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);

                $rtModel[$namaRw][$namaRt] = Wilayah::firstOrCreate(
                    ['nama' => $namaRt, 'id_induk' => $rw->id_wilayah],
                    ['tipe' => 'rt']
                );
            }
        }

        // ---------------------------------------------------------------
        // Akun
        // ---------------------------------------------------------------

        $admin = User::where('email', 'admin@sibansos.local')->first();

        if ($admin) {
            $admin->update(['id_wilayah' => $desa->id_wilayah, 'nama' => 'Rina Kusuma']);
        }

        $warga = User::where('email', 'warga@sibansos.local')->first();

        if ($warga) {
            $warga->update(['nama' => 'Siti Aminah']);
        }

        // ---------------------------------------------------------------
        // Kategori & jenis bantuan
        // ---------------------------------------------------------------

        $kategoriData = [
            'PKH' => [
                'deskripsi' => 'Program Keluarga Harapan untuk keluarga prasejahtera.',
                'program' => [['PKH Tahap IV', 'Kementerian Sosial']],
            ],
            'BPNT' => [
                'deskripsi' => 'Bantuan Pangan Non Tunai berupa paket sembako.',
                'program' => [['BPNT Sembako', 'Kementerian Sosial']],
            ],
            'BLT-DD' => [
                'deskripsi' => 'Bantuan Langsung Tunai Dana Desa.',
                'program' => [['BLT Dana Desa', 'Pemerintah Desa']],
            ],
            'Pendidikan' => [
                'deskripsi' => 'Bantuan biaya pendidikan bagi anak penerima manfaat.',
                'program' => [['Bantuan Pendidikan', 'Dinas Pendidikan']],
            ],
            'Kesehatan' => [
                'deskripsi' => 'Bantuan biaya pengobatan dan pendampingan lansia.',
                'program' => [['Bantuan Kesehatan', 'Puskesmas']],
            ],
        ];

        $jenisModel = [];

        foreach ($kategoriData as $nama => $data) {
            $kategori = KategoriBantuan::firstOrCreate(
                ['nama' => $nama],
                ['deskripsi' => $data['deskripsi']]
            );

            foreach ($data['program'] as $program) {
                $jenisModel[$nama] = JenisBantuan::firstOrCreate(
                    ['nama_program' => $program[0]],
                    [
                        'id_kategori' => $kategori->id_kategori,
                        'penyelenggara' => $program[1],
                        'deskripsi' => $data['deskripsi'],
                        'aktif' => true,
                    ]
                );
            }
        }

        // ---------------------------------------------------------------
        // Penerima contoh
        // ---------------------------------------------------------------

        $contoh = [
            ['Siti Aminah', 'P', '1978-01-17', '3573015201780001', 'RW 02', 'RT 04', 'PKH', 700000, 4, 'tidak_layak', true, false],
            ['Nur Hidayah', 'P', '1985-03-06', '3573016203850005', 'RW 03', 'RT 07', 'BPNT', 950000, 3, 'semi_permanen', false, false],
            ['Bambang Sutrisno', 'L', '1967-08-11', '3573011108670002', 'RW 01', 'RT 02', 'BLT-DD', 1200000, 2, 'semi_permanen', false, true],
            ['Sri Wahyuni', 'P', '1972-09-05', '3573014509720008', 'RW 04', 'RT 09', 'Pendidikan', 1450000, 3, 'semi_permanen', false, false],
            ['Joko Purnomo', 'L', '1979-05-28', '3573012805790003', 'RW 02', 'RT 05', 'Kesehatan', 800000, 1, 'tidak_layak', true, false],
            ['Sumiati', 'P', '1965-11-18', '3573015811650007', 'RW 05', 'RT 11', 'PKH', 650000, 2, 'tidak_layak', false, true],
            ['Agus Setiawan', 'L', '1990-04-22', '3573012204900004', 'RW 03', 'RT 03', 'BLT-DD', 1550000, 2, 'permanen', false, false],
            ['Ahmad Fauzi', 'L', '1988-02-14', '3573011402880011', 'RW 01', 'RT 03', 'BPNT', 1100000, 5, 'semi_permanen', false, false],
            ['Lestari Ningsih', 'P', '1983-07-23', '3573012307830023', 'RW 04', 'RT 07', 'BLT-DD', 900000, 4, 'semi_permanen', false, false],
            ['Endang Setiawan', 'L', '1975-12-02', '3573010212750052', 'RW 01', 'RT 01', 'BLT-DD', 1000000, 3, 'tidak_layak', false, false],
            ['Yuliana Sari', 'P', '1992-06-19', '3573011906920067', 'RW 02', 'RT 06', 'Pendidikan', 1250000, 2, 'permanen', false, false],
            ['Hartono', 'L', '1970-10-30', '3573013010700074', 'RW 03', 'RT 08', 'PKH', 850000, 3, 'tidak_layak', false, true],
        ];

        $statusUrut = [
            'terverifikasi', 'terverifikasi', 'terverifikasi', 'terverifikasi',
            'terverifikasi', 'terverifikasi', 'diajukan', 'diajukan',
            'diajukan', 'diajukan', 'diajukan', 'terverifikasi',
        ];

        $penerimaModel = [];

        foreach ($contoh as $i => $row) {
            [$nama, $jk, $lahir, $nik, $namaRw, $namaRt, $kategori, $penghasilan, $tanggungan, $rumah, $disabilitas, $lansia] = $row;

            $rt = $rtModel[$namaRw][$namaRt] ?? $rtModel['RW 01']['RT 01'];

            $hash = hash_hmac('sha256', $nik, config('app.key'));

            $penerimaModel[] = Penerima::create([
                'nik' => encrypt($nik),
                'nik_hash' => $hash,
                'no_kk' => encrypt(substr($nik, 0, 15) . '2'),
                'nama' => $nama,
                'tanggal_lahir' => $lahir,
                'jenis_kelamin' => $jk,
                'alamat' => 'Jl. Melati No. ' . (10 + $i) . ', ' . $namaRt . ' / ' . $namaRw . ', Desa Sukamaju',
                'id_wilayah' => $rt->id_wilayah,
                'penghasilan_bulanan' => $penghasilan,
                'jumlah_tanggungan' => $tanggungan,
                'kondisi_rumah' => $rumah,
                'disabilitas' => $disabilitas,
                'lansia' => $lansia,
                'status' => $statusUrut[$i] ?? 'diajukan',
                'nomor_whatsapp' => '0812345678' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'catatan_verifikasi' => ($statusUrut[$i] ?? '') === 'terverifikasi'
                    ? 'Data sesuai KTP dan KK. Kondisi rumah sudah dicek lewat foto.'
                    : null,
                'diverifikasi_oleh' => ($statusUrut[$i] ?? '') === 'terverifikasi'
                    ? ($admin->id_user ?? null)
                    : null,
                'tanggal_verifikasi' => ($statusUrut[$i] ?? '') === 'terverifikasi'
                    ? now()->subDays(30 - $i)
                    : null,
                'created_at' => now()->subDays(60 - $i * 3),
                'updated_at' => now()->subDays(10),
            ]);
        }

        // Kaitkan akun warga ke Siti Aminah.
        if ($warga) {
            $penerimaModel[0]->update(['id_user' => $warga->id_user]);
        }

        // ---------------------------------------------------------------
        // Pengajuan
        // ---------------------------------------------------------------

        foreach ($penerimaModel as $i => $p) {
            $namaKategori = $contoh[$i][6];

            $status = $statusUrut[$i] ?? 'diajukan';

            $snapshot = [
                'nama' => $p->nama,
                'alamat' => $p->alamat,
                'nomor_whatsapp' => $p->nomor_whatsapp,
                'id_wilayah' => $p->id_wilayah,
                'nama_rt' => optional($p->wilayah)->nama,
                'nama_rw' => $contoh[$i][4],
                'penghasilan_bulanan' => $p->penghasilan_bulanan,
                'jumlah_tanggungan' => $p->jumlah_tanggungan,
                'kondisi_rumah' => $p->kondisi_rumah,
                'disabilitas' => $p->disabilitas,
                'lansia' => $p->lansia,
            ];

            PengajuanBantuan::create([
                'id_penerima' => $p->id_penerima,
                'id_jenis' => $jenisModel[$namaKategori]->id_jenis,
                'status' => $status === 'terverifikasi' ? 'terverifikasi' : 'diajukan',
                'data_penerima' => $snapshot,
                'diverifikasi_oleh' => $status === 'terverifikasi' ? ($admin->id_user ?? null) : null,
                'tanggal_verifikasi' => $status === 'terverifikasi' ? now()->subDays(20 - $i) : null,
                'catatan_verifikasi' => $status === 'terverifikasi'
                    ? 'Data sesuai KTP dan KK. Memenuhi kriteria kategori.'
                    : null,
                'created_at' => now()->subDays(14 - $i),
                'updated_at' => now()->subDays(5),
            ]);
        }

        // ---------------------------------------------------------------
        // Riwayat penyaluran
        // ---------------------------------------------------------------

        $nominal = [
            'PKH' => 750000,
            'BPNT' => 200000,
            'BLT-DD' => 300000,
            'Pendidikan' => 900000,
            'Kesehatan' => 1500000,
        ];

        $periode = [
            'PKH' => ['Tahap I', 'Tahap II', 'Tahap III', 'Tahap IV'],
            'BPNT' => ['Januari', 'Februari', 'Maret', 'April'],
            'BLT-DD' => ['Triwulan I', 'Triwulan II', 'Triwulan III'],
            'Pendidikan' => ['Ganjil 2025', 'Genap 2025', 'Ganjil 2026'],
            'Kesehatan' => ['Klaim I', 'Klaim II'],
        ];

        foreach ($penerimaModel as $i => $p) {
            if (($statusUrut[$i] ?? '') !== 'terverifikasi') {
                continue;
            }

            $namaKategori = $contoh[$i][6];

            $jumlahRiwayat = ($i % 3) + 2;

            for ($n = 0; $n < $jumlahRiwayat; $n++) {
                RiwayatBantuan::create([
                    'id_penerima' => $p->id_penerima,
                    'id_jenis' => $jenisModel[$namaKategori]->id_jenis,
                    'tanggal_terima' => now()->subMonths(($jumlahRiwayat - $n) * 3)->startOfMonth()->addDays(4),
                    'jumlah' => $nominal[$namaKategori],
                    'satuan' => 'Rupiah',
                    'keterangan' => $periode[$namaKategori][$n % count($periode[$namaKategori])]
                        . ' · transfer bank',
                    'dicatat_oleh' => $admin->id_user ?? $penerimaModel[0]->id_user ?? 1,
                ]);
            }
        }

        $this->command->info('DemoSeeder selesai: '
            . Penerima::count() . ' penerima, '
            . PengajuanBantuan::count() . ' pengajuan, '
            . RiwayatBantuan::count() . ' riwayat.');
    }
}
