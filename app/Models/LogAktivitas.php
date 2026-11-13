<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    protected $table = 'log_aktivitas';

    protected $primaryKey = 'id_log';

    public $timestamps = false;

    protected $guarded = ['id_log'];

    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \RuntimeException(
                'Log aktivitas tidak boleh diubah.'
            );
        });

        static::deleting(function () {
            throw new \RuntimeException(
                'Log aktivitas tidak boleh dihapus.'
            );
        });
    }
}
