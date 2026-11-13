
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_bantuan', function (Blueprint $table) {
            $table->increments('id_pengajuan');

            $table->unsignedInteger('id_penerima');
            $table->unsignedInteger('id_jenis');

            $table->enum('status', [
                'diajukan',
                'terverifikasi',
                'ditolak',
                'nonaktif',
            ])->default('diajukan');

            $table->text('catatan_verifikasi')->nullable();

            $table->unsignedInteger('diverifikasi_oleh')->nullable();
            $table->dateTime('tanggal_verifikasi')->nullable();

            $table->timestamps();

            $table->foreign('id_penerima')
                ->references('id_penerima')
                ->on('penerima')
                ->restrictOnDelete();

            $table->foreign('id_jenis')
                ->references('id_jenis')
                ->on('jenis_bantuan')
                ->restrictOnDelete();

            $table->foreign('diverifikasi_oleh')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete();

            $table->index(
                ['id_penerima', 'status'],
                'pengajuan_penerima_status_index'
            );

            $table->index(
                ['id_jenis', 'status'],
                'pengajuan_jenis_status_index'
            );
        });

        DB::table('penerima')
            ->whereNotNull('id_jenis')
            ->whereIn('status', [
                'diajukan',
                'terverifikasi',
                'ditolak',
                'nonaktif',
            ])
            ->orderBy('id_penerima')
            ->get()
            ->each(function ($penerima) {
                DB::table('pengajuan_bantuan')->insert([
                    'id_penerima' => $penerima->id_penerima,
                    'id_jenis' => $penerima->id_jenis,
                    'status' => $penerima->status,
                    'catatan_verifikasi' => $penerima->catatan_verifikasi,
                    'diverifikasi_oleh' => $penerima->diverifikasi_oleh,
                    'tanggal_verifikasi' => $penerima->tanggal_verifikasi,
                    'created_at' => $penerima->created_at,
                    'updated_at' => $penerima->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_bantuan');
    }
};
