<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    protected $table = 'wilayah';

    protected $primaryKey = 'id_wilayah';

    public $timestamps = false;

    protected $fillable = ['tipe', 'nama', 'id_induk'];

    public function anak()
    {
        return $this->hasMany(self::class, 'id_induk', 'id_wilayah');
    }

    public function induk()
    {
        return $this->belongsTo(self::class, 'id_induk', 'id_wilayah');
    }

    /**
     * Label wilayah siap tampil.
     * Data lama menyimpan nama tanpa awalan tipe ("01"), sehingga
     * awalan RT/RW ditambahkan agar tidak ambigu pada antarmuka.
     */
    public function getLabelAttribute(): string
    {
        $nama = trim((string) $this->nama);

        $awalan = [
            'rt' => 'RT',
            'rw' => 'RW',
            'desa' => 'Desa',
            'kelurahan' => 'Kel.',
        ][$this->tipe] ?? null;

        if (!$awalan) {
            return $nama;
        }

        // Hindari pengulangan bila nama sudah memuat awalan.
        if (preg_match('/^(rt|rw|desa|kel\.?)\s/i', $nama)) {
            return $nama;
        }

        return $awalan . ' ' . $nama;
    }
}
