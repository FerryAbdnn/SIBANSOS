<?php

namespace Tests\Feature;

use App\Models\Penerima;
use App\Models\PengajuanPerbaikan;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PerbaikanControllerTest extends TestCase
{
    use DatabaseTransactions;


    public function test_masyarakat_hanya_dapat_mengajukan_perbaikan_untuk_profilnya_sendiri(): void
    {
        $desa = Wilayah::create([
            'tipe' => 'desa',
            'nama' => 'Desa Pengujian',
            'id_induk' => null,
        ]);

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

        $masyarakat = User::create([
            'nama' => 'Masyarakat Pengujian',
            'email' => 'masyarakat-pengujian@example.test',
            'password' => bcrypt('password'),
            'peran' => 'masyarakat',
            'aktif' => true,
        ]);

        $pemilikLain = User::create([
            'nama' => 'Pemilik Lain',
            'email' => 'pemilik-lain@example.test',
            'password' => bcrypt('password'),
            'peran' => 'masyarakat',
            'aktif' => true,
        ]);

        $penerimaMilikMasyarakat = $this->buatPenerima(
            $masyarakat,
            $rt
        );

        $penerimaMilikOrangLain = $this->buatPenerima(
            $pemilikLain,
            $rt,
            '3174000000000002'
        );

        // Percobaan mengajukan perbaikan menggunakan ID penerima orang lain.
        $this->actingAs($masyarakat)
            ->post(route('masyarakat.perbaikan.store'), [
                'id_penerima' => $penerimaMilikOrangLain->id_penerima,
                'alamat' => 'Alamat hasil perbaikan',
                'alasan' => 'Perubahan alamat tempat tinggal.',
                'dokumen_surat_rt' => UploadedFile::fake()->create(
                    'surat-rt.pdf',
                    100,
                    'application/pdf'
                ),
            ])
            ->assertRedirect();

        // Pastikan tidak ada pengajuan untuk profil orang lain.
        $this->assertDatabaseMissing('pengajuan_perbaikan', [
            'id_penerima' => $penerimaMilikOrangLain->id_penerima,
        ]);

        // Pengajuan yang benar untuk profil milik sendiri.
        $this->post(route('masyarakat.perbaikan.store'), [
            'alamat' => 'Alamat hasil perbaikan',
            'alasan' => 'Perubahan alamat tempat tinggal.',
            'dokumen_surat_rt' => UploadedFile::fake()->create(
                'surat-rt.pdf',
                100,
                'application/pdf'
            ),
        ])->assertRedirect();

        $this->assertDatabaseHas('pengajuan_perbaikan', [
            'id_penerima' => $penerimaMilikMasyarakat->id_penerima,
            'diajukan_oleh' => $masyarakat->id_user,
            'status' => 'menunggu',
        ]);
    }

    public function test_admin_hanya_melihat_pengajuan_perbaikan_di_wilayahnya(): void
    {
        // Buat wilayah Desa A.
        $desaA = Wilayah::create([
            'tipe' => 'desa',
            'nama' => 'Desa Admin A',
            'id_induk' => null,
        ]);

        $rwA = Wilayah::create([
            'tipe' => 'rw',
            'nama' => 'RW 01',
            'id_induk' => $desaA->id_wilayah,
        ]);

        $rtA = Wilayah::create([
            'tipe' => 'rt',
            'nama' => 'RT 01',
            'id_induk' => $rwA->id_wilayah,
        ]);

        // Buat wilayah Desa B.
        $desaB = Wilayah::create([
            'tipe' => 'desa',
            'nama' => 'Desa Admin B',
            'id_induk' => null,
        ]);

        $rwB = Wilayah::create([
            'tipe' => 'rw',
            'nama' => 'RW 02',
            'id_induk' => $desaB->id_wilayah,
        ]);

        $rtB = Wilayah::create([
            'tipe' => 'rt',
            'nama' => 'RT 02',
            'id_induk' => $rwB->id_wilayah,
        ]);

        // Admin hanya memiliki kewenangan di Desa A.
        $admin = User::create([
            'nama' => 'Admin Pengujian',
            'email' => 'admin-pengujian@example.test',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'id_wilayah' => $desaA->id_wilayah,
            'aktif' => true,
        ]);

        // Buat dua masyarakat.
        $wargaA = User::create([
            'nama' => 'Warga Desa A',
            'email' => 'warga-a@example.test',
            'password' => bcrypt('password'),
            'peran' => 'masyarakat',
            'aktif' => true,
        ]);

        $wargaB = User::create([
            'nama' => 'Warga Desa B',
            'email' => 'warga-b@example.test',
            'password' => bcrypt('password'),
            'peran' => 'masyarakat',
            'aktif' => true,
        ]);

        // Buat penerima di masing-masing wilayah.
        $penerimaA = $this->buatPenerima(
            $wargaA,
            $rtA,
            '3174000000000003'
        );

        $penerimaB = $this->buatPenerima(
            $wargaB,
            $rtB,
            '3174000000000004'
        );

        // Buat pengajuan perbaikan di Desa A.
        $pengajuanA = PengajuanPerbaikan::create([
            'id_penerima' => $penerimaA->id_penerima,
            'diajukan_oleh' => $wargaA->id_user,
            'data_usulan' => [
                'alamat' => 'Alamat baru Desa A',
            ],
            'status' => 'menunggu',
        ]);

        // Buat pengajuan perbaikan di Desa B.
        $pengajuanB = PengajuanPerbaikan::create([
            'id_penerima' => $penerimaB->id_penerima,
            'diajukan_oleh' => $wargaB->id_user,
            'data_usulan' => [
                'alamat' => 'Alamat baru Desa B',
            ],
            'status' => 'menunggu',
        ]);

        // Login sebagai admin Desa A.
        $response = $this->actingAs($admin)
            ->get(route('admin.perbaikan.index'));

        $response->assertOk();

        // Pastikan hanya pengajuan Desa A yang tampil.
        $response->assertViewHas('items', function ($items) use ($pengajuanA, $pengajuanB) {
            $idPengajuan = $items->getCollection()
                ->pluck('id_pengajuan')
                ->all();

            return in_array($pengajuanA->id_pengajuan, $idPengajuan)
                && !in_array($pengajuanB->id_pengajuan, $idPengajuan);
        });
    }


    public function test_admin_tidak_dapat_memproses_pengajuan_dari_wilayah_lain(): void
    {
        // Wilayah admin.
        $desaA = Wilayah::create([
            'tipe' => 'desa',
            'nama' => 'Desa Admin Proses A',
            'id_induk' => null,
        ]);

        $rwA = Wilayah::create([
            'tipe' => 'rw',
            'nama' => 'RW 01',
            'id_induk' => $desaA->id_wilayah,
        ]);

        // Wilayah lain.
        $desaB = Wilayah::create([
            'tipe' => 'desa',
            'nama' => 'Desa Admin Proses B',
            'id_induk' => null,
        ]);

        $rwB = Wilayah::create([
            'tipe' => 'rw',
            'nama' => 'RW 02',
            'id_induk' => $desaB->id_wilayah,
        ]);

        $rtB = Wilayah::create([
            'tipe' => 'rt',
            'nama' => 'RT 02',
            'id_induk' => $rwB->id_wilayah,
        ]);

        $admin = User::create([
            'nama' => 'Admin Proses A',
            'email' => 'admin-proses-a@example.test',
            'password' => bcrypt('password'),
            'peran' => 'admin',
            'id_wilayah' => $desaA->id_wilayah,
            'aktif' => true,
        ]);

        $wargaB = User::create([
            'nama' => 'Warga Proses B',
            'email' => 'warga-proses-b@example.test',
            'password' => bcrypt('password'),
            'peran' => 'masyarakat',
            'aktif' => true,
        ]);

        $penerimaB = $this->buatPenerima(
            $wargaB,
            $rtB,
            '3174000000000005'
        );

        $pengajuanB = PengajuanPerbaikan::create([
            'id_penerima' => $penerimaB->id_penerima,
            'diajukan_oleh' => $wargaB->id_user,
            'data_usulan' => [
                'alamat' => 'Alamat baru yang tidak boleh diterapkan',
            ],
            'status' => 'menunggu',
        ]);

        // Admin Desa A mencoba menyetujui pengajuan Desa B.
        $this->actingAs($admin)
            ->patch(
                route('admin.perbaikan.proses', $pengajuanB->id_pengajuan),
                [
                    'status' => 'disetujui',
                    'catatan_admin' => 'Pengujian akses wilayah',
                ]
            )
            ->assertForbidden();

        // Status pengajuan harus tetap menunggu.
        $this->assertDatabaseHas('pengajuan_perbaikan', [
            'id_pengajuan' => $pengajuanB->id_pengajuan,
            'status' => 'menunggu',
            'diproses_oleh' => null,
        ]);

        // Data penerima tidak boleh berubah.
        $this->assertDatabaseHas('penerima', [
            'id_penerima' => $penerimaB->id_penerima,
            'alamat' => 'Alamat awal',
        ]);
    }


    private function buatPenerima(
        User $user,
        Wilayah $wilayah,
        string $nik = '3174000000000001'
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
            'alamat' => 'Alamat awal',
            'id_wilayah' => $wilayah->id_wilayah,
            'kondisi_rumah' => 'permanen',
            'status' => 'terverifikasi',
        ]);
    }
}
