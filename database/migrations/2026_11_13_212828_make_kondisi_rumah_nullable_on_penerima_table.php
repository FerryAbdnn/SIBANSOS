
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerima', function (Blueprint $table) {
            $table->enum('kondisi_rumah', [
                'permanen',
                'semi_permanen',
                'tidak_layak',
            ])->nullable()->change();
        });
    }

    public function down(): void
    {
        // Jangan rollback jika masih ada profil tanpa kondisi rumah.
        if (DB::table('penerima')
            ->whereNull('kondisi_rumah')
            ->exists()) {
            throw new RuntimeException(
                'Rollback dibatalkan: masih ada profil penerima tanpa kondisi rumah.'
            );
        }

        Schema::table('penerima', function (Blueprint $table) {
            $table->enum('kondisi_rumah', [
                'permanen',
                'semi_permanen',
                'tidak_layak',
            ])->nullable(false)->change();
        });
    }
};
