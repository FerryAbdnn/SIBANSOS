<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    protected $table = 'wilayah';

    protected $primaryKey = 'id_wilayah';

    public $timestamps = false;

    protected $fillable = [
        'tipe',
        'nama',
        'id_induk',
    ];

    /**
     * Wilayah memiliki beberapa wilayah anak.
     */
    public function anak()
    {
        return $this->hasMany(
            self::class,
            'id_induk',
            'id_wilayah'
        );
    }

    /**
     * Wilayah induk, misalnya RT memiliki induk RW.
     */
    public function induk()
    {
        return $this->belongsTo(
            self::class,
            'id_induk',
            'id_wilayah'
        );
    }
}
