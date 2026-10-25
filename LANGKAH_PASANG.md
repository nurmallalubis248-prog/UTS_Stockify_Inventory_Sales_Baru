# Cara memasang kode ini ke project Laravel baru

Folder ini berisi **lapisan aplikasi** (model, controller, view, migration, seeder, test, README). Kerangka Laravel (`vendor`, `bootstrap`, `config`, dll.) tidak disertakan karena dibuat oleh Composer.

1. Buat project Laravel 12 (butuh PHP 8.2+ dan Composer):

   ```bash
   composer create-project laravel/laravel inventory-sales
   cd inventory-sales
   ```

2. Ekstrak zip ini, lalu **salin seluruh isinya ke dalam folder `inventory-sales`** dan pilih *replace/timpa* bila ditanya (`app/Models/User.php`, `app/Providers/AppServiceProvider.php`, `routes/web.php`, dan `database/seeders/DatabaseSeeder.php` menggantikan berkas bawaan).

3. Hapus contoh test bawaan yang mengharapkan halaman `/` bernilai 200 (sekarang `/` mengarah ke dashboard):

   ```bash
   rm tests/Feature/ExampleTest.php
   ```

4. Ubah `.env` (lihat bagian Environment setup di README.md), terutama:

   ```env
   APP_NAME=Stockify
   APP_LOCALE=id
   APP_FALLBACK_LOCALE=id
   DB_CONNECTION=mysql
   DB_DATABASE=inventory_sales
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Salin juga perubahan yang sama ke `.env.example` agar repository dapat dijalankan ulang.

5. Agar jam transaksi sesuai WIB, di `config/app.php` ubah:

   ```php
   'timezone' => 'Asia/Jakarta',
   ```

6. Jalankan:

   ```bash
   php artisan migrate --seed
   php artisan test
   php artisan serve
   ```

7. Login dengan `admin@example.com` / `password` `staff@example.com` / `password`, atau `user@example.com` / `password`.

Catatan: kode ini ditulis tanpa PHP di lingkungan pembuatnya, jadi belum pernah dijalankan. Jalankan `php artisan test` terlebih dahulu; jika ada galat, kirimkan pesannya untuk diperbaiki.
