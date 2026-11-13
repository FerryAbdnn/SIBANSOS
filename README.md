# SIBANSOS — Sistem Pendataan Bantuan Sosial

Aplikasi web lokal berbasis Laravel 12, PHP 8.2+, dan MySQL. Modul dan aturan bisnis mengikuti laporan proyek SIBANSOS (SDG 1 — Tanpa Kemiskinan).

## Cara menjalankan di komputer lokal

### Prasyarat
- PHP >= 8.2 dengan ekstensi PDO MySQL, OpenSSL, Mbstring, Tokenizer, XML, Ctype, JSON, Fileinfo.
- Composer 2.x.
- MySQL 8 atau MariaDB.

### Instalasi
1. Buat project Laravel 12 baru: `composer create-project laravel/laravel sibansos "^12.0"`.
2. Salin seluruh isi folder paket ini ke folder `sibansos` yang baru dibuat dan izinkan overwrite file yang sama. Jangan menghapus folder `vendor` dari instalasi Laravel. Hapus migration bawaan Laravel `database/migrations/0001_01_01_000000_create_users_table.php` agar tidak terjadi bentrok karena paket ini menyediakan tabel users dengan kolom dan primary key sesuai laporan.
3. Masuk ke folder project: `cd sibansos`.
4. Buat database MySQL bernama `sibansos`.
5. Salin `.env.example` menjadi `.env`, lalu sesuaikan `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`.
6. Jalankan: `php artisan key:generate`.
7. Perbarui dependensi bila Composer meminta penyesuaian lock file: `composer update`.
8. Jalankan migrasi dan data awal: `php artisan migrate --seed`.
9. Jalankan server lokal: `php artisan serve`.
10. Buka `http://127.0.0.1:8000`.

## Akun demo
- Admin: `admin@sibansos.local` / `password123`
- Masyarakat: `warga@sibansos.local` / `password123`

Ganti password demo sebelum memakai data nyata.

## Modul yang tersedia
- Autentikasi dan role admin/masyarakat.
- Dashboard ringkasan admin dan portal warga.
- CRUD data penerima, pencarian, filter status, dan detail.
- Pengajuan mandiri, status pengajuan, verifikasi/penolakan admin.
- CRUD kategori dan program bantuan.
- Pencatatan dan tampilan riwayat bantuan.
- Pengajuan perbaikan data oleh masyarakat dan persetujuan admin.
- Upload dokumen pendukung ke local private storage.
- Log aktivitas untuk sejumlah perubahan utama.
- Aturan larangan menghapus penerima yang sudah memiliki riwayat.

## Catatan implementasi dan keamanan
- NIK disimpan dalam bentuk terenkripsi dan HMAC untuk pemeriksaan duplikasi. NIK ditampilkan tersamarkan pada daftar.
- Akun admin demo ditugaskan ke wilayah desa contoh. Penugasan wilayah dapat diubah melalui database.
- `APP_KEY` harus stabil karena dipakai untuk enkripsi NIK. Jangan mengganti APP_KEY setelah terdapat data terenkripsi.
- Untuk deployment nyata, aktifkan HTTPS, batasi ukuran/jenis dokumen, gunakan backup berkala, konfigurasi session timeout, dan audit seluruh endpoint serta akses lintas wilayah.
- Fitur impor Excel/CSV, ekspor PDF/Excel, grafik periode, pengingat data usang, halaman unduh dokumen privat, dan tampilan log audit khusus belum disertakan pada paket awal ini.
- Tampilan dibangun berdasarkan spesifikasi laporan. Link Figma tidak dapat diambil secara otomatis pada sesi pembuatan ini, sehingga detail visual mungkin perlu disesuaikan setelah dibandingkan dengan file Figma.

## Struktur MVC
- `app/Models`: model Eloquent.
- `app/Http/Controllers`: controller dan aturan bisnis.
- `app/Http/Middleware/RoleMiddleware.php`: pembatasan peran.
- `resources/views`: tampilan Blade.
- `routes/web.php`: routing.
- `database/migrations`: skema relasional.
- `database/seeders`: data demo awal.
