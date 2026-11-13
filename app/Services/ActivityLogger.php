<?php

namespace App\Services;

use App\Models\LogAktivitas;

class ActivityLogger
{
    public static function record(
        string $aksi,
        string $tabel,
        ?int $idRecord = null,
        ?array $dataLama = null,
        ?array $dataBaru = null
    ): void {
        LogAktivitas::create([
            'id_user' => auth()->id(),
            'aksi' => $aksi,
            'tabel' => $tabel,
            'id_record' => $idRecord,
            'data_lama' => $dataLama,
            'data_baru' => $dataBaru,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
