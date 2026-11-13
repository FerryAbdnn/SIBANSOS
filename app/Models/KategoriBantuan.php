<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;
class KategoriBantuan extends Model { protected $table='kategori_bantuan'; protected $primaryKey='id_kategori'; protected $fillable=['nama','deskripsi']; public function jenis(){return $this->hasMany(JenisBantuan::class,'id_kategori','id_kategori');} }
