<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (
            !Auth::attempt($data) ||
            !Auth::user()->aktif
        ) {
            Auth::logout();

            return back()
                ->withErrors([
                    'email' => 'Email atau kata sandi salah, atau akun tidak aktif.',
                ])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = User::create([
            'nama' => $data['nama'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'peran' => 'masyarakat',
            'aktif' => true,
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('masyarakat.pengajuan.create')
            ->with(
                'success',
                'Akun berhasil dibuat. Lengkapi pengajuan bantuan Anda.'
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
