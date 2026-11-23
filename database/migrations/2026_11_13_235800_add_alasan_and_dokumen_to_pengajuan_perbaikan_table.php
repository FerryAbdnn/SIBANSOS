
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_perbaikan', function (Blueprint $table) {
            $table->text('alasan')->nullable();
            $table->string('dokumen_surat_rt')->nullable();
            $table->string('dokumen_berhenti_kerja')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_perbaikan', function (Blueprint $table) {
            $table->dropColumn([
                'alasan',
                'dokumen_surat_rt',
                'dokumen_berhenti_kerja',
            ]);
        });
    }
};
