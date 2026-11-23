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
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\ExportPenerimaController;

Route::get('/', function () {

    // Kategori dan program bantuan aktif.
    $kategoriBantuan = \App\Models\KategoriBantuan::with([
        'jenis' => function ($query) {
            $query->where('aktif', true)
                ->orderBy('nama_program');
        }
    ])
        ->whereHas('jenis', function ($query) {
            $query->where('aktif', true);
        })
        ->orderBy('id_kategori')
        ->get();

    // Statistik penerima.
    $jumlahPenerima = \App\Models\Penerima::count();

    // Statistik wilayah.
    $jumlahRw = \App\Models\Wilayah::where('tipe', 'rw')->count();

    $jumlahRt = \App\Models\Wilayah::where('tipe', 'rt')->count();

    // Jumlah program bantuan aktif.
    $jumlahProgram = $kategoriBantuan->sum(
        fn($kategori) => $kategori->jenis->count()
    );

    return view('public.landing', compact(
        'kategoriBantuan',
        'jumlahPenerima',
        'jumlahRw',
        'jumlahRt',
        'jumlahProgram'
    ));

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

        // Profil masyarakat.
        if ($user->peran === 'masyarakat') {
            $p = $user->penerima()
                ->with('wilayah')
                ->first();

            return view('masyarakat.profile', compact('p'));
        }

        // Wilayah penugasan admin.
        $wilayah = $user->wilayah()
            ->with('induk.induk')
            ->first();

        // Cakupan wilayah admin.
        $cakupanWilayahIds = \App\Support\WilayahScope::ids(
            $user->id_wilayah
        );

        $wilayahDalamCakupan = \App\Models\Wilayah::whereIn(
            'id_wilayah',
            $cakupanWilayahIds
        )->get(['id_wilayah', 'tipe']);

        $jumlahRw = $wilayahDalamCakupan
            ->filter(fn($item) => strtolower($item->tipe) === 'rw')
            ->count();

        $jumlahRt = $wilayahDalamCakupan
            ->filter(fn($item) => strtolower($item->tipe) === 'rt')
            ->count();

        // Jumlah penerima.
        $jumlahPenerima = \App\Models\Penerima::whereIn(
            'id_wilayah',
            $cakupanWilayahIds
        )->count();

        // Statistik aktivitas admin.
        $logAdmin = \App\Models\LogAktivitas::where(
            'id_user',
            $user->id_user
        );

        $jumlahPengajuan = (clone $logAdmin)
            ->where('aksi', 'pengajuan')
            ->count();

        $jumlahVerifikasi = (clone $logAdmin)
            ->where('aksi', 'verifikasi')
            ->count();

        $jumlahPenolakan = (clone $logAdmin)
            ->where('aksi', 'tolak')
            ->count();

        // Lima aktivitas terakhir.
        $aktivitasTerbaru = (clone $logAdmin)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('profile', compact(
            'user',
            'wilayah',
            'jumlahRw',
            'jumlahRt',
            'jumlahPenerima',
            'jumlahPengajuan',
            'jumlahVerifikasi',
            'jumlahPenolakan',
            'aktivitasTerbaru'
        ));
    })->name('profile');

    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('penerima/export', [ExportPenerimaController::class, 'index'])
                ->name('penerima.export');

            Route::post('penerima/export/download', [ExportPenerimaController::class, 'download'])
                ->name('penerima.export.download');

            Route::resource('penerima', PenerimaController::class);

            Route::get(
                'penerima/{penerima}/dokumen/{dokumen}',
                [PenerimaController::class, 'showDocument']
            )->name('penerima.dokumen.show');

            Route::patch(
                'penerima/{penerima}/nonaktif',
                [PenerimaController::class, 'nonaktifkan']
            )->name('penerima.nonaktif');

            Route::get('pengajuan', [PengajuanController::class, 'index'])
                ->name('pengajuan.index');

            Route::get(
                'pengajuan/{pengajuan}/dokumen/{dokumen}',
                [PengajuanController::class, 'showDocument']
            )->name('pengajuan.dokumen.show');

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

            Route::get(
                'kategori/{kategori}',
                [KategoriController::class, 'show']
            )->name('kategori.show');

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

            Route::get(
                'perbaikan/{pengajuan}/dokumen/{jenis}',
                [PerbaikanController::class, 'showDocument']
            )->name('perbaikan.dokumen.show');

            Route::get('wilayah', [WilayahController::class, 'index'])
                ->name('wilayah.index');

            Route::post('wilayah', [WilayahController::class, 'store'])
                ->name('wilayah.store');

            Route::put('wilayah/{wilayah}', [WilayahController::class, 'update'])
                ->name('wilayah.update');

            Route::delete('wilayah/{wilayah}', [WilayahController::class, 'destroy'])
                ->name('wilayah.destroy');
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
