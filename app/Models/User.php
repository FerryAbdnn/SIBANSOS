<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'id_user';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'peran',
        'id_wilayah',
        'aktif',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'aktif' => 'boolean',
        ];
    }

    public function penerima()
    {
        return $this->hasOne(
            Penerima::class,
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
}
