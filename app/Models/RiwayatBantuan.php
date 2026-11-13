<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use App\Models\PengajuanBantuan;
class RiwayatBantuan extends Model
{
    protected $table = 'riwayat_bantuan';
    protected $primaryKey = 'id_riwayat';
    protected $guarded = ['id_riwayat'];
    protected $casts = ['tanggal_terima' => 'date', 'jumlah' => 'decimal:2'];
    public function penerima()
    {
        return $this->belongsTo(Penerima::class, 'id_penerima', 'id_penerima');
    }
    public function jenis()
    {
        return $this->belongsTo(JenisBantuan::class, 'id_jenis', 'id_jenis');
    }
    public function pengajuan()
    {
        return $this->belongsTo(
            PengajuanBantuan::class,
            'id_pengajuan',
            'id_pengajuan'
        );
    }
}
