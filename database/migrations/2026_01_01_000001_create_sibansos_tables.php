<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tabel Password Reset Tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Tabel Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Tabel Wilayah
        Schema::create('wilayah', function (Blueprint $table) {
            $table->increments('id_wilayah');
            $table->enum('tipe', ['desa', 'kelurahan', 'rw', 'rt']);
            $table->string('nama', 100);

            $table->unsignedInteger('id_induk')->nullable();

            $table->foreign('id_induk')
                ->references('id_wilayah')
                ->on('wilayah')
                ->nullOnDelete();
        });

        // Tabel Users
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama', 100);
            $table->string('email', 100)->unique();
            $table->string('password');

            $table->enum('peran', ['admin', 'masyarakat'])
                ->default('masyarakat');

            $table->unsignedInteger('id_wilayah')->nullable();

            $table->foreign('id_wilayah')
                ->references('id_wilayah')
                ->on('wilayah')
                ->nullOnDelete();

            $table->boolean('aktif')->default(true);

            $table->rememberToken();
            $table->timestamps();
        });

        // Tabel Penerima Bantuan
        Schema::create('penerima', function (Blueprint $table) {
            $table->increments('id_penerima');

            $table->unsignedInteger('id_user')->nullable()->unique();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete();

            $table->text('nik');
            $table->char('nik_hash', 64)->unique();
            $table->string('no_kk', 255)->nullable();
            $table->string('nama', 100);
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->text('alamat');

            $table->unsignedInteger('id_wilayah')->nullable();

            $table->foreign('id_wilayah')
                ->references('id_wilayah')
                ->on('wilayah')
                ->nullOnDelete();

            $table->decimal('penghasilan_bulanan', 12, 2)->default(0);
            $table->unsignedTinyInteger('jumlah_tanggungan')->default(0);

            $table->enum('kondisi_rumah', [
                'permanen',
                'semi_permanen',
                'tidak_layak'
            ]);

            $table->boolean('disabilitas')->default(false);
            $table->boolean('lansia')->default(false);

            $table->enum('status', [
                'diajukan',
                'terverifikasi',
                'ditolak',
                'nonaktif'
            ])->default('diajukan');

            $table->text('catatan_verifikasi')->nullable();

            $table->unsignedInteger('diverifikasi_oleh')->nullable();

            $table->foreign('diverifikasi_oleh')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete();

            $table->dateTime('tanggal_verifikasi')->nullable();

            $table->timestamps();

            $table->index(['nama', 'id_wilayah', 'status']);
        });

        // Tabel Kategori Bantuan
        Schema::create('kategori_bantuan', function (Blueprint $table) {
            $table->increments('id_kategori');
            $table->string('nama', 50)->unique();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        // Tabel Jenis Bantuan
        Schema::create('jenis_bantuan', function (Blueprint $table) {
            $table->increments('id_jenis');

            $table->unsignedInteger('id_kategori');

            $table->foreign('id_kategori')
                ->references('id_kategori')
                ->on('kategori_bantuan')
                ->restrictOnDelete();

            $table->string('nama_program', 100);
            $table->string('penyelenggara', 100)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);

            $table->timestamps();
        });

        // Tabel Riwayat Bantuan
        Schema::create('riwayat_bantuan', function (Blueprint $table) {
            $table->increments('id_riwayat');

            $table->unsignedInteger('id_penerima');

            $table->foreign('id_penerima')
                ->references('id_penerima')
                ->on('penerima')
                ->restrictOnDelete();

            $table->unsignedInteger('id_jenis');

            $table->foreign('id_jenis')
                ->references('id_jenis')
                ->on('jenis_bantuan')
                ->restrictOnDelete();

            $table->date('tanggal_terima');
            $table->decimal('jumlah', 12, 2)->nullable();
            $table->string('satuan', 30)->nullable();
            $table->text('keterangan')->nullable();

            $table->unsignedInteger('dicatat_oleh');

            $table->foreign('dicatat_oleh')
                ->references('id_user')
                ->on('users')
                ->restrictOnDelete();

            $table->timestamps();
        });

        // Tabel Pengajuan Perbaikan Data
        Schema::create('pengajuan_perbaikan', function (Blueprint $table) {
            $table->increments('id_pengajuan');

            $table->unsignedInteger('id_penerima');

            $table->foreign('id_penerima')
                ->references('id_penerima')
                ->on('penerima')
                ->cascadeOnDelete();

            $table->unsignedInteger('diajukan_oleh');

            $table->foreign('diajukan_oleh')
                ->references('id_user')
                ->on('users')
                ->restrictOnDelete();

            $table->json('data_usulan');

            $table->enum('status', [
                'menunggu',
                'disetujui',
                'ditolak'
            ])->default('menunggu');

            $table->text('catatan_admin')->nullable();

            $table->unsignedInteger('diproses_oleh')->nullable();

            $table->foreign('diproses_oleh')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->dateTime('diproses_at')->nullable();
        });

        // Tabel Dokumen Pendukung
        Schema::create('dokumen_pendukung', function (Blueprint $table) {
            $table->increments('id_dokumen');

            $table->unsignedInteger('id_penerima');

            $table->foreign('id_penerima')
                ->references('id_penerima')
                ->on('penerima')
                ->cascadeOnDelete();

            $table->enum('jenis_dokumen', [
                'ktp',
                'kk',
                'surat_keterangan',
                'lainnya'
            ])->default('lainnya');

            $table->string('nama_file', 150);
            $table->string('path_file', 255);

            $table->unsignedInteger('diunggah_oleh');

            $table->foreign('diunggah_oleh')
                ->references('id_user')
                ->on('users')
                ->restrictOnDelete();

            $table->timestamps();
        });

        // Tabel Log Aktivitas
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->bigIncrements('id_log');

            $table->unsignedInteger('id_user')->nullable();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete();

            $table->string('aksi', 30);
            $table->string('tabel', 50);
            $table->unsignedInteger('id_record')->nullable();
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        $tables = [
            'log_aktivitas',
            'dokumen_pendukung',
            'pengajuan_perbaikan',
            'riwayat_bantuan',
            'jenis_bantuan',
            'kategori_bantuan',
            'penerima',
            'users',
            'wilayah',
            'sessions',
            'password_reset_tokens',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
