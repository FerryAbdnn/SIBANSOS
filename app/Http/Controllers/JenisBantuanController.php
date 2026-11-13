<?php

namespace App\Http\Controllers;

use App\Models\JenisBantuan;
use App\Models\KategoriBantuan;
use Illuminate\Http\Request;

class JenisBantuanController extends Controller
{
    public function index()
    {
        $items = JenisBantuan::with('kategori')
            ->paginate(10);

        return view(
            'admin.jenis.index',
            compact('items')
        );
    }

    public function create()
    {
        return view('admin.jenis.form', [
            'item' => new JenisBantuan,
            'kategori' => KategoriBantuan::all(),
        ]);
    }

    public function store(Request $r)
    {
        JenisBantuan::create($r->validate([
            'id_kategori' => 'required|exists:kategori_bantuan,id_kategori',
            'nama_program' => 'required|string|max:100',
            'penyelenggara' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
        ]));

        return redirect()
            ->route('admin.jenis.index')
            ->with('success', 'Program bantuan ditambahkan.');
    }

    public function edit(JenisBantuan $jenis)
    {
        return view('admin.jenis.form', [
            'item' => $jenis,
            'kategori' => KategoriBantuan::all(),
        ]);
    }

    public function update(Request $r, JenisBantuan $jenis)
    {
        $jenis->update($r->validate([
            'id_kategori' => 'required|exists:kategori_bantuan,id_kategori',
            'nama_program' => 'required|string|max:100',
            'penyelenggara' => 'nullable|string|max:100',
            'deskripsi' => 'nullable|string',
        ]));

        return back()->with('success', 'Program diperbarui.');
    }

    public function destroy(JenisBantuan $jenis)
    {
        if ($jenis->riwayat()->exists()) {
            $jenis->update([
                'aktif' => false,
            ]);

            return back()->with(
                'success',
                'Program sudah digunakan, sehingga dinonaktifkan.'
            );
        }

        $jenis->delete();

        return back()->with(
            'success',
            'Program dihapus.'
        );
    }
}
