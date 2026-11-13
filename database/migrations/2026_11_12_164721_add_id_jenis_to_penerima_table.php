
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerima', function (Blueprint $table) {
            $table->unsignedInteger('id_jenis')
                ->nullable()
                ->after('id_user');

            $table->foreign('id_jenis')
                ->references('id_jenis')
                ->on('jenis_bantuan')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('penerima', function (Blueprint $table) {
            $table->dropForeign(['id_jenis']);
            $table->dropColumn('id_jenis');
        });
    }
};
