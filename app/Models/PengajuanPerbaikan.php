<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class PengajuanPerbaikan extends Model { protected $table='pengajuan_perbaikan'; protected $primaryKey='id_pengajuan'; protected $guarded=['id_pengajuan']; protected $casts=['data_usulan'=>'array','diproses_at'=>'datetime']; public function penerima(){return $this->belongsTo(Penerima::class,'id_penerima','id_penerima');} public function pengaju(){return $this->belongsTo(User::class,'diajukan_oleh','id_user');} }
