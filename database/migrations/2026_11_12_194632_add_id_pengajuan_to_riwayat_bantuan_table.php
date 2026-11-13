
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_bantuan', function (Blueprint $table) {
            $table->unsignedInteger('id_pengajuan')
                ->nullable()
                ->after('id_penerima');

            $table->foreign('id_pengajuan')
                ->references('id_pengajuan')
                ->on('pengajuan_bantuan')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_bantuan', function (Blueprint $table) {
            $table->dropForeign(['id_pengajuan']);
            $table->dropColumn('id_pengajuan');
        });
    }
};
