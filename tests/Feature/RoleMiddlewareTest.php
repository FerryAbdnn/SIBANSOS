<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    public function test_masyarakat_tidak_dapat_mengakses_route_admin(): void
    {
        $user = User::create([
            'nama' => 'Masyarakat Pengujian',
            'email' => 'masyarakat-role-test@example.com',
            'password' => 'password123',
            'peran' => 'masyarakat',
            'id_wilayah' => null,
            'aktif' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('admin.penerima.index'));

        $response->assertForbidden();
    }

    public function test_akun_masyarakat_nonaktif_tidak_dapat_mengakses_route_masyarakat(): void
    {
        $user = User::create([
            'nama' => 'Masyarakat Nonaktif',
            'email' => 'masyarakat-nonaktif-test@example.com',
            'password' => 'password123',
            'peran' => 'masyarakat',
            'id_wilayah' => null,
            'aktif' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('masyarakat.status'));

        $response->assertForbidden();
    }
}