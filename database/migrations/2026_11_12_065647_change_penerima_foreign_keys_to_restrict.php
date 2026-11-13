<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengajuan_perbaikan', function (Blueprint $table) {
            $table->dropForeign(['id_penerima']);

            $table->foreign('id_penerima')
                ->references('id_penerima')
                ->on('penerima')
                ->restrictOnDelete();
        });

        Schema::table('dokumen_pendukung', function (Blueprint $table) {
            $table->dropForeign(['id_penerima']);

            $table->foreign('id_penerima')
                ->references('id_penerima')
                ->on('penerima')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_perbaikan', function (Blueprint $table) {
            $table->dropForeign(['id_penerima']);

            $table->foreign('id_penerima')
                ->references('id_penerima')
                ->on('penerima')
                ->cascadeOnDelete();
        });

        Schema::table('dokumen_pendukung', function (Blueprint $table) {
            $table->dropForeign(['id_penerima']);

            $table->foreign('id_penerima')
                ->references('id_penerima')
                ->on('penerima')
                ->cascadeOnDelete();
        });
    }
};