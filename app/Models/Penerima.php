<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penerima extends Model
{
    protected $table = 'penerima';

    protected $primaryKey = 'id_penerima';

    protected $guarded = ['id_penerima'];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'disabilitas' => 'boolean',
        'lansia' => 'boolean',
        'penghasilan_bulanan' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'id_user',
            'id_user'
        );
    }

    public function wilayah()
    {
        return $this->belongsTo(
            Wilayah::class,
            'id_wilayah',
            'id_wilayah'
        );
    }

    public function riwayat()
    {
        return $this->hasMany(
            RiwayatBantuan::class,
            'id_penerima',
            'id_penerima'
        );
    }

    public function dokumen()
    {
        return $this->hasMany(
            DokumenPendukung::class,
            'id_penerima',
            'id_penerima'
        );
    }

    public function pengajuanPerbaikan()
    {
        return $this->hasMany(
            PengajuanPerbaikan::class,
            'id_penerima',
            'id_penerima'
        );
    }

    public function jenisBantuan()
    {
        return $this->belongsTo(
            JenisBantuan::class,
            'id_jenis',
            'id_jenis'
        );
    }

    public function pengajuanBantuan()
    {
        return $this->hasMany(
            PengajuanBantuan::class,
            'id_penerima',
            'id_penerima'
        );
    }

    public function pengajuanTerbaru()
    {
        return $this->hasOne(
            PengajuanBantuan::class,
            'id_penerima',
            'id_penerima'
        )->latestOfMany('created_at');
    }

    public function getNoKkDecryptedAttribute(): ?string
    {
        if (empty($this->no_kk)) {
            return null;
        }

        try {
            return decrypt($this->no_kk);
        } catch (\Throwable $e) {
            // Nilai tidak dapat didekripsi (mis. APP_KEY berbeda).
            // Jangan pernah tampilkan ciphertext ke pengguna.
            return null;
        }
    }

    /**
     * NIK hasil dekripsi. Mengembalikan null bila gagal didekripsi
     * agar ciphertext tidak pernah tampil pada antarmuka.
     */
    public function getNikDecryptedAttribute(): ?string
    {
        if (empty($this->nik)) {
            return null;
        }

        try {
            return decrypt($this->nik);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * NIK tersamarkan untuk ditampilkan pada daftar.
     */
    public function getNikMaskedAttribute(): string
    {
        $nik = $this->nik_decrypted;

        if (!$nik) {
            return 'Tidak tersedia';
        }

        return substr($nik, 0, 4) . '••••••' . substr($nik, -4);
    }
}
