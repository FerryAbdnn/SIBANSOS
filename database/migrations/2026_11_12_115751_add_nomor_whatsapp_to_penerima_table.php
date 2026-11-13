<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('penerima', function (Blueprint $table) {
            $table->string('nomor_whatsapp', 20)
                ->nullable()
                ->after('jenis_kelamin');
        });
    }

    public function down(): void
    {
        Schema::table('penerima', function (Blueprint $table) {
            $table->dropColumn('nomor_whatsapp');
        });
    }
};