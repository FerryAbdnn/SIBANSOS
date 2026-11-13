<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanBantuan extends Model
{
    protected $table = 'pengajuan_bantuan';

    protected $primaryKey = 'id_pengajuan';

    protected $guarded = ['id_pengajuan'];

    protected $casts = [
        'tanggal_verifikasi' => 'datetime',
        'data_penerima' => 'array',
    ];

    public function penerima()
    {
        return $this->belongsTo(Penerima::class, 'id_penerima', 'id_penerima');
    }

    public function jenisBantuan()
    {
        return $this->belongsTo(JenisBantuan::class, 'id_jenis', 'id_jenis');
    }

    public function dokumen()
    {
        return $this->hasMany(DokumenPendukung::class, 'id_pengajuan', 'id_pengajuan');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh', 'id_user');
    }
}