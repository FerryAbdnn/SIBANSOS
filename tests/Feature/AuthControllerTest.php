<?php

namespace Tests\Feature;

use App\Models\Penerima;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_masyarakat_dapat_login_dengan_jenis_akun_yang_sesuai(): void
    {
        $user = User::create([
            'nama' => 'Masyarakat Login',
            'email' => 'masyarakat-login@example.test',
            'password' => 'password123',
            'peran' => 'masyarakat',
            'aktif' => true,
        ]);

        $response = $this->post(route('login.post'), [
            'jenis_login' => 'masyarakat',
            'email' => 'masyarakat-login@example.test',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_ditolak_jika_kredensial_salah(): void
    {
        $user = User::create([
            'nama' => 'Masyarakat Password Salah',
            'email' => 'password-salah@example.test',
            'password' => 'password123',
            'peran' => 'masyarakat',
            'aktif' => true,
        ]);

        $response = $this->post(route('login.post'), [
            'jenis_login' => 'masyarakat',
            'email' => $user->email,
            'password' => 'password-yang-salah',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_ditolak_jika_jenis_akun_tidak_sesuai(): void
    {
        $user = User::create([
            'nama' => 'Masyarakat Salah Jenis',
            'email' => 'jenis-akun-salah@example.test',
            'password' => 'password123',
            'peran' => 'masyarakat',
            'aktif' => true,
        ]);

        $response = $this->post(route('login.post'), [
            'jenis_login' => 'admin',
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_akun_nonaktif_tidak_dapat_login(): void
    {
        $user = User::create([
            'nama' => 'Masyarakat Nonaktif Login',
            'email' => 'akun-nonaktif-login@example.test',
            'password' => 'password123',
            'peran' => 'masyarakat',
            'aktif' => false,
        ]);

        $response = $this->post(route('login.post'), [
            'jenis_login' => 'masyarakat',
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_masyarakat_dapat_logout(): void
    {
        $user = User::create([
            'nama' => 'Masyarakat Logout',
            'email' => 'masyarakat-logout@example.test',
            'password' => 'password123',
            'peran' => 'masyarakat',
            'aktif' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_masyarakat_dapat_registrasi_dan_data_identitas_disimpan_terenkripsi(): void
    {
        [$desa, $rw, $rt] = $this->buatWilayah(
            'Desa Registrasi Berhasil'
        );

        $nik = '3174000000000101';
        $noKk = '3174000000000201';
        $email = 'registrasi-berhasil@example.test';

        $response = $this->post(
            route('register.post'),
            $this->payloadRegistrasi(
                $desa,
                $rw,
                $rt,
                $nik,
                $noKk,
                $email
            )
        );

        $response->assertRedirect(route('masyarakat.pengajuan.create'));
        $this->assertAuthenticated();

        $user = User::where('email', $email)->firstOrFail();

        $penerima = Penerima::where(
            'id_user',
            $user->id_user
        )->firstOrFail();

        $this->assertSame('masyarakat', $user->peran);
        $this->assertTrue((bool) $user->aktif);

        $this->assertSame(
            $rt->id_wilayah,
            $penerima->id_wilayah
        );

        $this->assertSame(
            $nik,
            decrypt($penerima->nik)
        );

        $this->assertSame(
            $noKk,
            decrypt($penerima->no_kk)
        );

        $this->assertNotSame($nik, $penerima->nik);
        $this->assertNotSame($noKk, $penerima->no_kk);

        $this->assertSame(
            hash_hmac('sha256', $nik, config('app.key')),
            $penerima->nik_hash
        );

        $this->assertDatabaseHas('penerima', [
            'id_penerima' => $penerima->id_penerima,
            'status' => 'diajukan',
        ]);

        $this->assertNotNull(
            $user->fresh()->persetujuan_data_at
        );
    }

    public function test_registrasi_ditolak_jika_email_sudah_digunakan(): void
    {
        [$desa, $rw, $rt] = $this->buatWilayah(
            'Desa Email Duplikat'
        );

        User::create([
            'nama' => 'Pengguna Lama',
            'email' => 'email-duplikat@example.test',
            'password' => 'password123',
            'peran' => 'masyarakat',
            'aktif' => true,
        ]);

        $response = $this->post(
            route('register.post'),
            $this->payloadRegistrasi(
                $desa,
                $rw,
                $rt,
                '3174000000000102',
                '3174000000000202',
                'email-duplikat@example.test'
            )
        );

        $response->assertSessionHasErrors('email');

        $this->assertSame(
            1,
            User::where(
                'email',
                'email-duplikat@example.test'
            )->count()
        );
    }

    public function test_registrasi_ditolak_jika_nik_sudah_terdaftar(): void
    {
        [$desa, $rw, $rt] = $this->buatWilayah(
            'Desa NIK Duplikat'
        );

        $nik = '3174000000000103';

        Penerima::create([
            'nik' => Crypt::encryptString($nik),
            'nik_hash' => hash_hmac(
                'sha256',
                $nik,
                config('app.key')
            ),
            'no_kk' => Crypt::encryptString('3174000000000203'),
            'nama' => 'Pemilik NIK Lama',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Alamat pemilik NIK lama',
            'id_wilayah' => $rt->id_wilayah,
            'kondisi_rumah' => null,
            'status' => 'diajukan',
        ]);

        $response = $this->post(
            route('register.post'),
            $this->payloadRegistrasi(
                $desa,
                $rw,
                $rt,
                $nik,
                '3174000000000204',
                'nik-duplikat@example.test'
            )
        );

        $response->assertSessionHasErrors('nik');

        $this->assertDatabaseMissing('users', [
            'email' => 'nik-duplikat@example.test',
        ]);
    }

    public function test_registrasi_ditolak_jika_hierarki_wilayah_tidak_sesuai(): void
    {
        [$desaA, $rwA, $rtA] = $this->buatWilayah(
            'Desa Hierarki A'
        );

        [, $rwB] = $this->buatWilayah(
            'Desa Hierarki B'
        );

        $response = $this->post(
            route('register.post'),
            $this->payloadRegistrasi(
                $desaA,
                $rwB,
                $rtA,
                '3174000000000104',
                '3174000000000205',
                'hierarki-salah@example.test'
            )
        );

        $response->assertSessionHasErrors('id_wilayah');

        $this->assertDatabaseMissing('users', [
            'email' => 'hierarki-salah@example.test',
        ]);
    }

    private function buatWilayah(string $nama): array
    {
        $desa = Wilayah::create([
            'tipe' => 'desa',
            'nama' => $nama,
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

        return [$desa, $rw, $rt];
    }

    private function payloadRegistrasi(
        Wilayah $desa,
        Wilayah $rw,
        Wilayah $rt,
        string $nik,
        string $noKk,
        string $email
    ): array {
        return [
            'nik' => $nik,
            'no_kk' => $noKk,
            'nama' => 'Masyarakat Registrasi Pengujian',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'alamat' => 'Alamat registrasi pengujian',
            'id_desa' => $desa->id_wilayah,
            'id_rw' => $rw->id_wilayah,
            'id_wilayah' => $rt->id_wilayah,
            'nomor_whatsapp' => '081234567890',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'persetujuan_data' => '1',
        ];
    }
}


