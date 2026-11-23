<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
use App\Models\Penerima;
use App\Support\WilayahScope;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class WilayahController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $wilayahIds = WilayahScope::ids(
            (int) $user->id_wilayah
        );

        $rwList = Wilayah::whereIn('id_wilayah', $wilayahIds)
            ->where('tipe', 'rw')
            ->with(['anak' => function ($query) use ($wilayahIds) {
                $query->where('tipe', 'rt')
                    ->whereIn('id_wilayah', $wilayahIds)
                    ->orderBy('nama');
            }])
            ->orderBy('nama')
            ->get();

        return view('admin.wilayah.index', compact('rwList'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $wilayahIds = WilayahScope::ids(
            (int) $user->id_wilayah
        );

        $data = $request->validate([
            'tipe' => [
                'required',
                Rule::in(['rw', 'rt']),
            ],
            'nama' => [
                'required',
                'string',
                'max:10',
                'regex:/^[0-9]+$/',
            ],
            'id_induk' => [
                'required',
                'integer',
                Rule::in($wilayahIds),
            ],
        ]);

        $induk = Wilayah::findOrFail($data['id_induk']);

        if (
            ($data['tipe'] === 'rw' && $induk->tipe !== 'desa' && $induk->tipe !== 'kelurahan') ||
            ($data['tipe'] === 'rt' && $induk->tipe !== 'rw')
        ) {
            return back()
                ->withErrors(['id_induk' => 'Wilayah induk tidak sesuai.'])
                ->withInput();
        }

        $sudahAda = Wilayah::where('tipe', $data['tipe'])
            ->where('id_induk', $induk->id_wilayah)
            ->whereRaw('LOWER(nama) = ?', [mb_strtolower(trim($data['nama']))])
            ->exists();

        if ($sudahAda) {
            return back()
                ->withErrors(['nama' => 'Nama wilayah tersebut sudah terdaftar.'])
                ->withInput();
        }

        Wilayah::create([
            'tipe' => $data['tipe'],
            'nama' => trim($data['nama']),
            'id_induk' => $induk->id_wilayah,
        ]);

        return redirect()
            ->route('admin.wilayah.index')
            ->with('success', 'Wilayah berhasil ditambahkan.');
    }

    public function update(Request $request, Wilayah $wilayah)
    {
        $user = auth()->user();

        $wilayahIds = WilayahScope::ids(
            (int) $user->id_wilayah
        );

        abort_unless(
            in_array($wilayah->id_wilayah, $wilayahIds),
            403
        );

        if (!in_array($wilayah->tipe, ['rw', 'rt'])) {
            abort(403);
        }

        $data = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:10',
                'regex:/^[0-9]+$/',
            ],
        ]);

        $sudahAda = Wilayah::where('tipe', $wilayah->tipe)
            ->where('id_induk', $wilayah->id_induk)
            ->where('id_wilayah', '!=', $wilayah->id_wilayah)
            ->whereRaw('LOWER(nama) = ?', [mb_strtolower(trim($data['nama']))])
            ->exists();

        if ($sudahAda) {
            return back()
                ->withErrors(['nama' => 'Nama wilayah tersebut sudah terdaftar.'])
                ->withInput();
        }

        $wilayah->update([
            'nama' => trim($data['nama']),
        ]);

        return redirect()
            ->route('admin.wilayah.index')
            ->with('success', 'Nama wilayah berhasil diperbarui.');
    }

    public function destroy(Wilayah $wilayah)
    {
        $user = auth()->user();

        $wilayahIds = WilayahScope::ids(
            (int) $user->id_wilayah
        );

        abort_unless(
            in_array($wilayah->id_wilayah, $wilayahIds),
            403
        );

        if (!in_array($wilayah->tipe, ['rw', 'rt'])) {
            abort(403);
        }

        $punyaAnak = $wilayah->anak()->exists();

        if ($punyaAnak) {
            return back()->with(
                'error',
                'Wilayah tidak dapat dihapus karena masih memiliki wilayah turunan.'
            );
        }

        $digunakan = Penerima::where(
            'id_wilayah',
            $wilayah->id_wilayah
        )->exists();

        if ($digunakan) {
            return back()->with(
                'error',
                'Wilayah tidak dapat dihapus karena masih digunakan oleh data penerima.'
            );
        }

        $wilayah->delete();

        return redirect()
            ->route('admin.wilayah.index')
            ->with('success', 'Wilayah berhasil dihapus.');
    }
}
