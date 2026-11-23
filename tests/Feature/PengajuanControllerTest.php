<?php

namespace Tests\Feature;

use App\Models\Penerima;
use App\Models\PengajuanBantuan;
use App\Models\RiwayatBantuan;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PengajuanControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_tidak_dapat_memverifikasi_pengajuan_dari_wilayah_lain(): void
    {
        [$desaA] = $this->buatWilayah('Wilayah Admin');
        [, , $rtB] = $this->buatWilayah('Wilayah Warga');

        $admin = $this->buatAdmin(
            $desaA,
            'admin-pengajuan-a@example.test'
        );

        $warga = $this->buatWarga(
            'warga-pengajuan-b@example.test'
        );

        $penerima = $this->buatPenerima(
            $warga,
            $rtB,
            '3174000000000011'
        );

        $jenis = $this->buatJenisBantuan();

        $pengajuan = $this->buatPengajuan(
            $penerima,
            $jenis,
            $rtB
        );

        $this->actingAs($admin)
            ->patch(route(
                'admin.pengajuan.verifikasi',
                $pengajuan->id_pengajuan
            ))
            ->assertForbidden();

        $this->assertDatabaseHas('pengajuan_bantuan', [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'status' => 'diajukan',
            'diverifikasi_oleh' => null,
        ]);

        $this->assertDatabaseMissing('log_aktivitas', [
            'id_user' => $admin->id_user,
            'aksi' => 'verifikasi',
            'tabel' => 'pengajuan_bantuan',
            'id_record' => $pengajuan->id_pengajuan,
        ]);
    }

    public function test_admin_dapat_memverifikasi_pengajuan_di_wilayahnya(): void
    {
        [$desa, , $rt] = $this->buatWilayah('Wilayah Verifikasi');

        $admin = $this->buatAdmin(
            $desa,
            'admin-verifikasi@example.test'
        );

        $warga = $this->buatWarga(
            'warga-verifikasi@example.test'
        );

        $penerima = $this->buatPenerima(
            $warga,
            $rt,
            '3174000000000012'
        );

        $jenis = $this->buatJenisBantuan();

        $pengajuan = $this->buatPengajuan(
            $penerima,
            $jenis,
            $rt
        );

        $this->actingAs($admin)
            ->patch(route(
                'admin.pengajuan.verifikasi',
                $pengajuan->id_pengajuan
            ))
            ->assertRedirect();

        $this->assertDatabaseHas('pengajuan_bantuan', [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'status' => 'terverifikasi',
            'diverifikasi_oleh' => $admin->id_user,
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'id_user' => $admin->id_user,
            'aksi' => 'verifikasi',
            'tabel' => 'pengajuan_bantuan',
            'id_record' => $pengajuan->id_pengajuan,
        ]);
    }

    public function test_admin_dapat_menolak_pengajuan_di_wilayahnya(): void
    {
        [$desa, , $rt] = $this->buatWilayah('Wilayah Penolakan');

        $admin = $this->buatAdmin(
            $desa,
            'admin-penolakan@example.test'
        );

        $warga = $this->buatWarga(
            'warga-penolakan@example.test'
        );

        $penerima = $this->buatPenerima(
            $warga,
            $rt,
            '3174000000000013'
        );

        $jenis = $this->buatJenisBantuan();

        $pengajuan = $this->buatPengajuan(
            $penerima,
            $jenis,
            $rt
        );

        $alasan = 'Dokumen pendukung belum memenuhi persyaratan.';

        $this->actingAs($admin)
            ->patch(route(
                'admin.pengajuan.tolak',
                $pengajuan->id_pengajuan
            ), [
                'catatan' => $alasan,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('pengajuan_bantuan', [
            'id_pengajuan' => $pengajuan->id_pengajuan,
            'status' => 'ditolak',
            'catatan_verifikasi' => $alasan,
        ]);

        $this->assertDatabaseHas('log_aktivitas', [
            'id_user' => $admin->id_user,
            'aksi' => 'tolak',
            'tabel' => 'pengajuan_bantuan',
            'id_record' => $pengajuan->id_pengajuan,
        ]);
    }

    public function test_masyarakat_tidak_dapat_mengajukan_program_kedua_saat_pengajuan_pertama_aktif(): void
    {
        Storage::fake('local');

        [$desa, $rw, $rt] = $this->buatWilayah('Wilayah Pengajuan Ganda');

        $warga = $this->buatWarga(
            'warga-pengajuan-ganda@example.test'
        );

        $penerima = $this->buatPenerima(
            $warga,
            $rt,
            '3174000000000014'
        );

        $jenisLama = $this->buatJenisBantuan('Program Lama');
        $jenisBaru = $this->buatJenisBantuan('Program Baru');

        $pengajuanPertama = $this->buatPengajuan(
            $penerima,
            $jenisLama,
            $rt
        );

        $alamatAwal = $penerima->alamat;

        $response = $this->actingAs($warga)
            ->post(route('masyarakat.pengajuan.store'), [
                'nomor_whatsapp' => '081234567890',
                'alamat' => 'Alamat baru pengujian',
                'id_rw' => $rw->id_wilayah,
                'id_wilayah' => $rt->id_wilayah,
                'id_jenis' => $jenisBaru,
                'penghasilan_bulanan' => 2000000,
                'jumlah_tanggungan' => 3,
                'kondisi_rumah' => 'permanen',

                'dokumen_ktp' => UploadedFile::fake()->image('ktp.jpg'),
                'dokumen_kk' => UploadedFile::fake()->image('kk.jpg'),
                'dokumen_dtks_p3ke' => UploadedFile::fake()->image('dtks.jpg'),
                'foto_rumah_depan' => UploadedFile::fake()->image('depan.jpg'),
                'foto_rumah_dalam' => UploadedFile::fake()->image('dalam.jpg'),
                'dokumen_sktm' => UploadedFile::fake()->image('sktm.jpg'),
            ]);

        $response->assertSessionHasErrors('id_jenis');

        $this->assertSame(
            1,
            PengajuanBantuan::where(
                'id_penerima',
                $penerima->id_penerima
            )->count()
        );

        $this->assertDatabaseHas('pengajuan_bantuan', [
            'id_pengajuan' => $pengajuanPertama->id_pengajuan,
            'status' => 'diajukan',
            'id_jenis' => $jenisLama,
        ]);

        $this->assertSame(
            $alamatAwal,
            $penerima->fresh()->alamat
        );

        $this->assertDatabaseMissing('pengajuan_bantuan', [
            'id_penerima' => $penerima->id_penerima,
            'id_jenis' => $jenisBaru,
        ]);
    }

    public function test_masyarakat_dapat_mengajukan_ulang_blt_dd_yang_ditolak(): void
    {
        Storage::fake('local');

        [, $rw, $rt] = $this->buatWilayah(
            'Wilayah BLT-DD Ditolak'
        );

        $warga = $this->buatWarga(
            'warga-blt-ditolak@example.test'
        );

        $penerima = $this->buatPenerima(
            $warga,
            $rt,
            '3174000000000015'
        );

        $jenisBlt = $this->buatJenisBltDd();

        $pengajuanLama = $this->buatPengajuan(
            $penerima,
            $jenisBlt,
            $rt
        );

        $pengajuanLama->update([
            'status' => 'ditolak',
            'catatan_verifikasi' => 'Dokumen belum lengkap.',
        ]);

        $response = $this->actingAs($warga)
            ->post(
                route('masyarakat.pengajuan.store'),
                $this->payloadPengajuan(
                    $rw,
                    $rt,
                    $jenisBlt
                )
            );

        $response->assertRedirect();

        $this->assertDatabaseHas('pengajuan_bantuan', [
            'id_pengajuan' => $pengajuanLama->id_pengajuan,
            'status' => 'ditolak',
        ]);

        $this->assertSame(
            2,
            PengajuanBantuan::where(
                'id_penerima',
                $penerima->id_penerima
            )->count()
        );

        $this->assertDatabaseHas('pengajuan_bantuan', [
            'id_penerima' => $penerima->id_penerima,
            'id_jenis' => $jenisBlt,
            'status' => 'diajukan',
        ]);
    }

    public function test_masyarakat_dapat_mengajukan_ulang_blt_dd_setelah_pengajuan_sebelumnya_ditolak(): void
    {
        Storage::fake('local');

        [$desa, $rw, $rt] = $this->buatWilayah(
            'Wilayah BLT Ditolak'
        );

        $warga = $this->buatWarga(
            'warga-blt-ditolak@example.test'
        );

        $penerima = $this->buatPenerima(
            $warga,
            $rt,
            '3174000000000015'
        );

        $jenisBlt = $this->buatJenisBantuan(
            'Program BLT-DD'
        );

        $pengajuanLama = $this->buatPengajuan(
            $penerima,
            $jenisBlt,
            $rt
        );

        $pengajuanLama->update([
            'status' => 'ditolak',
            'catatan_verifikasi' => 'Dokumen belum lengkap.',
        ]);

        $response = $this->actingAs($warga)
            ->post(route('masyarakat.pengajuan.store'), [
                'nomor_whatsapp' => '081234567891',
                'alamat' => 'Alamat pengujian BLT baru',
                'id_rw' => $rw->id_wilayah,
                'id_wilayah' => $rt->id_wilayah,
                'id_jenis' => $jenisBlt,
                'penghasilan_bulanan' => 2000000,
                'jumlah_tanggungan' => 3,
                'kondisi_rumah' => 'permanen',

                'dokumen_ktp' => UploadedFile::fake()->image('ktp.jpg'),
                'dokumen_kk' => UploadedFile::fake()->image('kk.jpg'),
                'dokumen_dtks_p3ke' => UploadedFile::fake()->image('dtks.jpg'),
                'foto_rumah_depan' => UploadedFile::fake()->image('depan.jpg'),
                'foto_rumah_dalam' => UploadedFile::fake()->image('dalam.jpg'),
                'dokumen_sktm' => UploadedFile::fake()->image('sktm.jpg'),
            ]);

        $response->assertRedirect(route('masyarakat.status'));

        $this->assertDatabaseHas('pengajuan_bantuan', [
            'id_pengajuan' => $pengajuanLama->id_pengajuan,
            'status' => 'ditolak',
        ]);

        $this->assertDatabaseHas('pengajuan_bantuan', [
            'id_penerima' => $penerima->id_penerima,
            'id_jenis' => $jenisBlt,
            'status' => 'diajukan',
        ]);

        $this->assertSame(
            2,
            PengajuanBantuan::where(
                'id_penerima',
                $penerima->id_penerima
            )->count()
        );
    }

    private function buatWilayah(
        string $nama,
        bool $buatDesaBaru = true
    ): array {
        if ($buatDesaBaru) {
            $desa = Wilayah::create([
                'tipe' => 'desa',
                'nama' => $nama,
                'id_induk' => null,
            ]);
        } else {
            $desa = Wilayah::where('tipe', 'desa')
                ->where('nama', $nama)
                ->latest('id_wilayah')
                ->firstOrFail();
        }

        $rw = Wilayah::create([
            'tipe' => 'rw',
            'nama' => 'RW 01',
            'id_induk' => $desa->id_wilayah,
        ]);

        $rt = Wilayah::create([
            'tipe' => 'rt',
            'nama' => 'RT 01',
            'id_induk' => $rw->id_wilayah,
        ]);

        return [$desa, $rw, $rt];
    }

    private function buatAdmin(
        Wilayah $desa,
        string $email
    ): User {
        return User::create([
            'nama' => 'Admin Pengujian',
            'email' => $email,
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'id_wilayah' => $desa->id_wilayah,
            'aktif' => true,
        ]);
    }

    private function buatWarga(string $email): User
    {
        return User::create([
            'nama' => 'Warga Pengujian',
            'email' => $email,
            'password' => bcrypt('password'),
            'peran' => 'masyarakat',
            'aktif' => true,
        ]);
    }

    private function buatPenerima(
        User $user,
        Wilayah $rt,
        string $nik
    ): Penerima {
        return Penerima::create([
            'id_user' => $user->id_user,
            'nik' => Crypt::encryptString($nik),
            'nik_hash' => hash_hmac(
                'sha256',
                $nik,
                config('app.key')
            ),
            'nama' => $user->nama,
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Alamat pengujian',
            'id_wilayah' => $rt->id_wilayah,
            'kondisi_rumah' => 'permanen',
            'status' => 'terverifikasi',
        ]);
    }

    private function buatJenisBantuan(
        string $namaProgram = 'Program Pengujian'
    ): int {
        $idKategori = DB::table('kategori_bantuan')->insertGetId([
            'nama' => 'Kategori ' . $namaProgram,
            'deskripsi' => 'Kategori untuk pengujian otomatis',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('jenis_bantuan')->insertGetId([
            'id_kategori' => $idKategori,
            'nama_program' => $namaProgram,
            'penyelenggara' => 'Pemerintah',
            'deskripsi' => 'Program untuk pengujian otomatis',
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function buatJenisBltDd(): int
    {
        $idKategori = DB::table('kategori_bantuan')->insertGetId([
            'nama' => 'Kategori BLT-DD Pengujian',
            'deskripsi' => 'Kategori untuk pengujian BLT-DD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('jenis_bantuan')->insert([
            'id_jenis' => 7,
            'id_kategori' => $idKategori,
            'nama_program' => 'Program BLT-DD',
            'penyelenggara' => 'Pemerintah',
            'deskripsi' => 'Program BLT-DD untuk pengujian',
            'aktif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return 7;
    }

    private function payloadPengajuan(
        Wilayah $rw,
        Wilayah $rt,
        int $idJenis
    ): array {
        return [
            'nomor_whatsapp' => '081234567890',
            'alamat' => 'Alamat baru pengujian',
            'id_rw' => $rw->id_wilayah,
            'id_wilayah' => $rt->id_wilayah,
            'id_jenis' => $idJenis,
            'penghasilan_bulanan' => 2000000,
            'jumlah_tanggungan' => 3,
            'kondisi_rumah' => 'permanen',

            'dokumen_ktp' => UploadedFile::fake()->image('ktp.jpg'),
            'dokumen_kk' => UploadedFile::fake()->image('kk.jpg'),
            'dokumen_dtks_p3ke' => UploadedFile::fake()->image('dtks.jpg'),
            'foto_rumah_depan' => UploadedFile::fake()->image('depan.jpg'),
            'foto_rumah_dalam' => UploadedFile::fake()->image('dalam.jpg'),
            'dokumen_sktm' => UploadedFile::fake()->image('sktm.jpg'),
        ];
    }

    private function buatRiwayatBantuan(
        Penerima $penerima,
        User $admin,
        int $idJenis,
        string $tanggalTerima
    ): RiwayatBantuan {
        return RiwayatBantuan::create([
            'id_penerima' => $penerima->id_penerima,
            'id_jenis' => $idJenis,
            'tanggal_terima' => $tanggalTerima,
            'jumlah' => 300000,
            'satuan' => 'rupiah',
            'keterangan' => 'Riwayat bantuan untuk pengujian otomatis',
            'dicatat_oleh' => $admin->id_user,
        ]);
    }

    private function buatPengajuan(
        Penerima $penerima,
        int $idJenis,
        Wilayah $rt
    ): PengajuanBantuan {
        return PengajuanBantuan::create([
            'id_penerima' => $penerima->id_penerima,
            'id_jenis' => $idJenis,
            'status' => 'diajukan',
            'data_penerima' => [
                'id_wilayah' => $rt->id_wilayah,
                'alamat' => 'Alamat snapshot pengujian',
            ],
        ]);
    }

    public function test_masyarakat_nonaktif_dapat_mengajukan_kembali_dan_riwayat_lama_tetap_tersimpan(): void
    {
        Storage::fake('local');

        [, $rw, $rt] = $this->buatWilayah(
            'Wilayah Pengajuan Ulang Nonaktif'
        );

        $warga = $this->buatWarga(
            'warga-nonaktif-ajukan-ulang@example.test'
        );

        $penerima = $this->buatPenerima(
            $warga,
            $rt,
            '3174000000000025'
        );

        $jenisLama = $this->buatJenisBantuan(
            'Program Sebelumnya'
        );

        $jenisBaru = $this->buatJenisBantuan(
            'Program Pengajuan Ulang'
        );

        $pengajuanLama = $this->buatPengajuan(
            $penerima,
            $jenisLama,
            $rt
        );

        $pengajuanLama->update([
            'status' => 'terverifikasi',
        ]);

        $penerima->update([
            'status' => 'nonaktif',
        ]);

        $response = $this->actingAs($warga)->post(
            route('masyarakat.pengajuan.store'),
            $this->payloadPengajuan(
                $rw,
                $rt,
                $jenisBaru
            )
        );

        $response->assertRedirect(route('masyarakat.status'));

        $this->assertDatabaseHas('penerima', [
            'id_penerima' => $penerima->id_penerima,
            'status' => 'nonaktif',
        ]);

        $this->assertDatabaseHas('pengajuan_bantuan', [
            'id_pengajuan' => $pengajuanLama->id_pengajuan,
            'status' => 'terverifikasi',
            'id_jenis' => $jenisLama,
        ]);

        $this->assertDatabaseHas('pengajuan_bantuan', [
            'id_penerima' => $penerima->id_penerima,
            'id_jenis' => $jenisBaru,
            'status' => 'diajukan',
        ]);

        $this->assertSame(
            2,
            PengajuanBantuan::where(
                'id_penerima',
                $penerima->id_penerima
            )->count()
        );

        $pengajuanBaru = PengajuanBantuan::where(
            'id_penerima',
            $penerima->id_penerima
        )
            ->where('id_jenis', $jenisBaru)
            ->firstOrFail();

        $this->assertSame(
            6,
            $penerima->dokumen()
                ->where('id_pengajuan', $pengajuanBaru->id_pengajuan)
                ->count()
        );
    }

    public function test_masyarakat_tidak_dapat_mengajukan_blt_dd_sebelum_genap_12_bulan(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-14 12:00:00'));

        try {
            Storage::fake('local');

            // Membuat wilayah.
            [$desa, $rw, $rt] = $this->buatWilayah(
                'Desa Pengujian BLT-DD'
            );

            // Membuat akun masyarakat dan admin.
            $user = $this->buatWarga('warga-blt-wait@test.com');
            $admin = $this->buatAdmin(
                $desa,
                'admin-blt-wait@test.com'
            );

            // Membuat data penerima.
            $penerima = $this->buatPenerima(
                $user,
                $rt,
                '3201010101010001'
            );

            // Membuat program BLT-DD dengan ID 7.
            $idJenis = $this->buatJenisBltDd();

            // Bantuan terakhir diterima 11 bulan sebelumnya.
            $this->buatRiwayatBantuan(
                $penerima,
                $admin,
                $idJenis,
                '2025-12-14'
            );

            // Pengajuan sebelumnya sudah nonaktif.
            $pengajuanLama = $this->buatPengajuan(
                $penerima,
                $idJenis,
                $rt
            );

            $pengajuanLama->update([
                'status' => 'nonaktif',
            ]);

            // Masyarakat mencoba mengajukan BLT-DD kembali.
            $response = $this->actingAs($user)->post(
                route('masyarakat.pengajuan.store'),
                $this->payloadPengajuan($rw, $rt, $idJenis)
            );

            // Pengajuan harus ditolak karena belum genap 12 bulan.
            $response->assertSessionHasErrors('id_jenis');

            // Tidak boleh terbentuk pengajuan baru.
            $this->assertSame(
                1,
                PengajuanBantuan::where(
                    'id_penerima',
                    $penerima->id_penerima
                )->count()
            );
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_masyarakat_dapat_mengajukan_blt_dd_tepat_genap_12_bulan(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-14 12:00:00'));

        try {
            Storage::fake('local');

            [$desa, $rw, $rt] = $this->buatWilayah(
                'Desa BLT-DD Genap 12 Bulan'
            );

            $warga = $this->buatWarga(
                'warga-blt-genap@example.test'
            );

            $admin = $this->buatAdmin(
                $desa,
                'admin-blt-genap@example.test'
            );

            $penerima = $this->buatPenerima(
                $warga,
                $rt,
                '3201010101010021'
            );

            $idJenis = $this->buatJenisBltDd();

            // Bantuan terakhir diterima tepat 12 bulan sebelumnya.
            $this->buatRiwayatBantuan(
                $penerima,
                $admin,
                $idJenis,
                '2025-11-14'
            );

            // Pengajuan sebelumnya sudah nonaktif.
            $pengajuanLama = $this->buatPengajuan(
                $penerima,
                $idJenis,
                $rt
            );

            $pengajuanLama->update([
                'status' => 'nonaktif',
            ]);

            // Ajukan kembali tepat pada tanggal diperbolehkan.
            $response = $this->actingAs($warga)->post(
                route('masyarakat.pengajuan.store'),
                $this->payloadPengajuan($rw, $rt, $idJenis)
            );

            $response->assertRedirect(route('masyarakat.status'));

            // Pengajuan lama tetap tersimpan.
            $this->assertDatabaseHas('pengajuan_bantuan', [
                'id_pengajuan' => $pengajuanLama->id_pengajuan,
                'status' => 'nonaktif',
            ]);

            // Pengajuan baru berhasil dibuat.
            $this->assertDatabaseHas('pengajuan_bantuan', [
                'id_penerima' => $penerima->id_penerima,
                'id_jenis' => $idJenis,
                'status' => 'diajukan',
            ]);

            $this->assertSame(
                2,
                PengajuanBantuan::where(
                    'id_penerima',
                    $penerima->id_penerima
                )->count()
            );
        } finally {
            Carbon::setTestNow();
        }
    }

}
