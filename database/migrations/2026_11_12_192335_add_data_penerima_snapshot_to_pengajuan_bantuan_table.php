<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_bantuan', function (Blueprint $table) {
            $table->json('data_penerima')
                ->nullable()
                ->after('id_jenis');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_bantuan', function (Blueprint $table) {
            $table->dropColumn('data_penerima');
        });
    }
};