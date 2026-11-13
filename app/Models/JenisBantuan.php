<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class JenisBantuan extends Model { protected $table='jenis_bantuan'; protected $primaryKey='id_jenis'; protected $fillable=['id_kategori','nama_program','penyelenggara','deskripsi','aktif']; protected $casts=['aktif'=>'boolean']; public function riwayat(){return $this->hasMany(RiwayatBantuan::class,'id_jenis','id_jenis');} public function kategori(){return $this->belongsTo(KategoriBantuan::class,'id_kategori','id_kategori');} }
