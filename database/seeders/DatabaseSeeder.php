<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Wilayah;
use App\Models\KategoriBantuan;
use App\Models\JenisBantuan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        // Membuat wilayah desa
        $desa = Wilayah::create([
            'tipe' => 'desa',
            'nama' => 'Desa Sukamaju',
        ]);

        // Membuat wilayah RW
        $rw = Wilayah::create([
            'tipe' => 'rw',
            'nama' => 'RW 01',
            'id_induk' => $desa->id_wilayah,
        ]);

        // Membuat wilayah RT
        $rt = Wilayah::create([
            'tipe' => 'rt',
            'nama' => 'RT 01',
            'id_induk' => $rw->id_wilayah,
        ]);

        // Membuat akun Administrator
        User::create([
            'nama' => 'Administrator SIBANSOS',
            'email' => 'admin@sibansos.local',
            'password' => 'password123',
            'peran' => 'admin',
            'id_wilayah' => $desa->id_wilayah,
            'aktif' => true,
        ]);

        // Membuat akun Masyarakat
        User::create([
            'nama' => 'Warga Contoh',
            'email' => 'warga@sibansos.local',
            'password' => 'password123',
            'peran' => 'masyarakat',
            'aktif' => true,
        ]);

        $daftarKategori = [
            'Sembako',
            'Tunai',
            'Pendidikan',
            'Kesehatan',
        ];

        foreach ($daftarKategori as $namaKategori) {

            // Membuat kategori bantuan
            $kategori = KategoriBantuan::create([
                'nama' => $namaKategori,
                'deskripsi' => 'Kategori bantuan ' . $namaKategori,
            ]);

            // Membuat jenis bantuan berdasarkan kategori
            JenisBantuan::create([
                'id_kategori' => $kategori->id_kategori,
                'nama_program' => 'Program ' . $namaKategori,
                'penyelenggara' => 'Pemerintah',
                'aktif' => true,
            ]);
        }
    }
}
