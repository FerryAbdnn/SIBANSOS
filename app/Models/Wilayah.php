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
}
