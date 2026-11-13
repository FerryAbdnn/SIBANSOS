<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokumenPendukung extends Model
{
    protected $table = 'dokumen_pendukung';

    protected $primaryKey = 'id_dokumen';

    protected $guarded = ['id_dokumen'];

    /**
     * Dokumen dimiliki oleh satu penerima.
     */
    public function penerima()
    {
        return $this->belongsTo(
            Penerima::class,
            'id_penerima',
            'id_penerima'
        );
    }

    /**
     * Dokumen dapat terhubung ke satu pengajuan bantuan.
     */
    public function pengajuanBantuan()
    {
        return $this->belongsTo(
            PengajuanBantuan::class,
            'id_pengajuan',
            'id_pengajuan'
        );
    }
}
