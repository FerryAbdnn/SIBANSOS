<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Penerima;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        $desa = Wilayah::whereIn('tipe', ['desa', 'kelurahan'])
            ->orderBy('nama')
            ->get();

        $wilayahData = Wilayah::select(
            'id_wilayah',
            'tipe',
            'nama',
            'id_induk'
        )->orderBy('nama')->get();

        return view('auth.register', compact('desa', 'wilayahData'));
    }

    
    public function login(Request $request)
    {
        $data = $request->validate([
            'jenis_login' => 'required|in:admin,masyarakat',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Periksa email dan kata sandi terlebih dahulu.
        $berhasil = Auth::attempt([
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        if (!$berhasil) {
            Auth::logout();

            return back()
                ->withErrors([
                    'email' => 'Email atau kata sandi salah.',
                ])
                ->onlyInput(['email', 'jenis_login']);
        }

        $user = Auth::user();

        // Pastikan akun aktif dan perannya sesuai pilihan login.
        if (
            !$user->aktif ||
            $user->peran !== $data['jenis_login']
        ) {
            Auth::logout();

            return back()
                ->withErrors([
                    'email' => 'Jenis akun tidak sesuai dengan pilihan masuk, atau akun tidak aktif.',
                ])
                ->onlyInput(['email', 'jenis_login']);
        }

        // Login berhasil.
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }


    public function register(Request $request)
    {
        $data = $request->validate([
            'nik' => [
                'required',
                'digits:16',
            ],
            'no_kk' => [
                'required',
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
                'before:today',
            ],
            'jenis_kelamin' => [
                'required',
                'in:L,P',
            ],
            'alamat' => [
                'required',
                'string',
                'max:2000',
            ],
            'id_desa' => [
                'required',
                'integer',
                'exists:wilayah,id_wilayah',
            ],
            'id_rw' => [
                'required',
                'integer',
                'exists:wilayah,id_wilayah',
            ],
            'id_wilayah' => [
                'required',
                'integer',
                'exists:wilayah,id_wilayah',
            ],
            'nomor_whatsapp' => [
                'required',
                'string',
                'max:20',
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'persetujuan_data' => [
                'accepted',
            ],
        ]);

        // Pastikan hierarki wilayah benar:
        // Desa/Kelurahan -> RW -> RT.

        $desaValid = Wilayah::where('id_wilayah', $data['id_desa'])
            ->whereIn('tipe', ['desa', 'kelurahan'])
            ->exists();

        $rwValid = Wilayah::where('id_wilayah', $data['id_rw'])
            ->where('tipe', 'rw')
            ->where('id_induk', $data['id_desa'])
            ->exists();

        $rtValid = Wilayah::where('id_wilayah', $data['id_wilayah'])
            ->where('tipe', 'rt')
            ->where('id_induk', $data['id_rw'])
            ->exists();

        if (!$desaValid || !$rwValid || !$rtValid) {
            return back()
                ->withErrors([
                    'id_wilayah' => 'Kombinasi wilayah, RW, dan RT tidak valid.',
                ])
                ->withInput();
        }

        // NIK disimpan terenkripsi, hash digunakan untuk pencarian
        // dan pemeriksaan NIK ganda.

        $nikHash = hash_hmac(
            'sha256',
            $data['nik'],
            config('app.key')
        );

        if (Penerima::where('nik_hash', $nikHash)->exists()) {
            return back()
                ->withErrors([
                    'nik' => 'NIK sudah terdaftar dalam sistem.',
                ])
                ->withInput();
        }

        // Buat akun dan profil penerima secara atomik.

        $user = DB::transaction(function () use ($data, $nikHash) {
            $user = User::create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'peran' => 'masyarakat',
                'aktif' => true,
            ]);

            $user->forceFill([
                'persetujuan_data_at' => now(),
            ])->save();

            Penerima::create([
                'id_user' => $user->id_user,
                'nik' => encrypt($data['nik']),
                'nik_hash' => $nikHash,
                'no_kk' => encrypt($data['no_kk']),
                'nama' => $data['nama'],
                'tanggal_lahir' => $data['tanggal_lahir'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'nomor_whatsapp' => $data['nomor_whatsapp'],
                'alamat' => $data['alamat'],
                'id_wilayah' => $data['id_wilayah'],

                // Data ekonomi dilengkapi saat pengajuan bantuan.
                'penghasilan_bulanan' => 0,
                'jumlah_tanggungan' => 0,
                'kondisi_rumah' => null,
                'disabilitas' => false,
                'lansia' => false,
                'status' => 'diajukan',
            ]);

            return $user;
        });

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('masyarakat.pengajuan.create')
            ->with(
                'success',
                'Akun berhasil dibuat. Silakan lengkapi data ekonomi dan dokumen untuk mengajukan bantuan.'
            );
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
