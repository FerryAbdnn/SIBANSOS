<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PenerimaController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\JenisBantuanController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\PerbaikanController;

Route::get('/', function () {
    return view('public.landing');
})->name('landing');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.post');

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profil', function () {
        $user = auth()->user();

        if ($user->peran === 'masyarakat') {
            $p = $user->penerima()
                ->with('wilayah')
                ->first();

            return view('masyarakat.profile', compact('p'));
        }

        return view('profile');
    })->name('profile');

    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::resource('penerima', PenerimaController::class);

            Route::patch(
                'penerima/{penerima}/nonaktif',
                [PenerimaController::class, 'nonaktifkan']
            )->name('penerima.nonaktif');

            Route::get('pengajuan', [PengajuanController::class, 'index'])
                ->name('pengajuan.index');

            Route::patch(
                'pengajuan/{pengajuan}/verifikasi',
                [PengajuanController::class, 'verifikasi']
            )->name('pengajuan.verifikasi');

            Route::patch(
                'pengajuan/{pengajuan}/tolak',
                [PengajuanController::class, 'tolak']
            )->name('pengajuan.tolak');

            Route::resource('kategori', KategoriController::class)
                ->except(['show']);

            Route::resource('jenis', JenisBantuanController::class)
                ->except(['show'])
                ->parameters(['jenis' => 'jenis']);

            Route::get('riwayat', [RiwayatController::class, 'index'])
                ->name('riwayat.index');

            Route::post('riwayat', [RiwayatController::class, 'store'])
                ->name('riwayat.store');

            Route::get(
                'riwayat/{riwayat}/edit',
                [RiwayatController::class, 'edit']
            )->name('riwayat.edit');

            Route::put(
                'riwayat/{riwayat}',
                [RiwayatController::class, 'update']
            )->name('riwayat.update');

            Route::delete(
                'riwayat/{riwayat}',
                [RiwayatController::class, 'destroy']
            )->name('riwayat.destroy');

            Route::get('perbaikan', [PerbaikanController::class, 'index'])
                ->name('perbaikan.index');

            Route::patch(
                'perbaikan/{pengajuan}/proses',
                [PerbaikanController::class, 'proses']
            )->name('perbaikan.proses');
        });

    Route::middleware('role:masyarakat')
        ->prefix('masyarakat')
        ->name('masyarakat.')
        ->group(function () {

            Route::get('pengajuan', [PengajuanController::class, 'create'])
                ->name('pengajuan.create');

            Route::post('pengajuan', [PengajuanController::class, 'store'])
                ->name('pengajuan.store');

            Route::get('status', [PengajuanController::class, 'status'])
                ->name('status');

            Route::get('perbaikan', [PerbaikanController::class, 'create'])
                ->name('perbaikan.create');

            Route::post('perbaikan', [PerbaikanController::class, 'store'])
                ->name('perbaikan.store');

            Route::get('riwayat', [RiwayatController::class, 'masyarakatIndex'])
                ->name('riwayat');
        });
});
