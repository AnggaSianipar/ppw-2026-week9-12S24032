<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## SIM-Del Perpustakaan

Fitur tugas yang ditambahkan:

- CRUD kategori dengan relasi `Kategori hasMany Buku`.
- CRUD buku dengan relasi `Buku belongsTo Kategori`.
- Resource route dan implicit route model binding untuk buku dan kategori.
- Validasi server-side untuk ISBN unik, judul minimal 5 karakter, stok minimal 0,
  serta foreign key kategori.
- Eager loading pada daftar buku melalui `Buku::with('kategori')`.
- Pagination, `@forelse`, flash message, CSRF, dan method spoofing pada form.
- Seeder dan factory untuk minimal 5 kategori dan 20 buku.
- Audit query SQL lokal pada `storage/logs/laravel.log`.

### Menjalankan proyek

```powershell
composer install
php artisan migrate
php artisan db:seed
php artisan serve
```

Untuk menyiapkan database latihan dari awal pada instalasi baru:

```powershell
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

`migrate:fresh --seed` menghapus database latihan, menjalankan migration, dan
mengisi data contoh. Jangan gunakan perintah tersebut pada database produksi.

### SIPUS-Del Tahap 2

Tahap 2 menambahkan transaksi sirkulasi pada tabel `peminjamans`.
Peminjaman dan pengembalian menggunakan `DB::transaction()` serta
`lockForUpdate()` agar perubahan stok aman saat ada akses bersamaan.

#### Akun uji pustakawan

```text
Username: pustakawan
Password: password123
```

Kredensial login REST API:

```text
Email: pustakawan@del.ac.id
Password: password123
```

Jalankan seeder untuk menyiapkan akun, buku stok 0/stok 1, dan transaksi contoh:

```powershell
php artisan migrate:fresh --seed
```

#### Alur pengujian wajib

1. Login melalui `/login` menggunakan akun uji.
2. Buka `/buku` untuk melihat kartu statistik, pencarian judul/penulis/ISBN,
   filter kategori, dan jumlah peminjaman setiap buku.
3. Buka `/peminjaman`, catat peminjaman pada buku yang memiliki stok.
   Periksa stok buku berkurang satu.
4. Coba pinjam buku stok 0. Sistem harus menolak transaksi dan stok tidak berubah.
5. Coba pinjam buku yang sama dengan NIM yang sama sebelum dikembalikan.
   Sistem harus menolak transaksi duplikat aktif.
6. Klik `Kembalikan` pada transaksi aktif. Status berubah, tanggal kembali terisi,
   dan stok bertambah satu.
7. Buka DevTools browser pada `Application/Storage > Cookies`.
   Cookie `sipus_last_search` harus memiliki atribut `HttpOnly` setelah melakukan
   pencarian katalog. Ambil screenshot bukti ini untuk laporan.

State `peminjam_terakhir` dan `riwayat_pencarian` disimpan dalam session.

Untuk mengulang database latihan dari awal:

```powershell
php artisan migrate:fresh --seed
```

Perintah tersebut menghapus seluruh data database sebelum membuat ulang migration
dan data contoh.

### Route utama

```text
/buku
/kategori
```

Semua operasi CRUD tersedia melalui `Route::resource` di `routes/web.php`.

### Bukti eager loading

Saat aplikasi berjalan pada environment `local`, listener di
`app/Providers/AppServiceProvider.php` mencatat query ke
`storage/logs/laravel.log`. Buka halaman `/buku` dan cari teks `SQL AUDIT`.

## SIPUS-Del Tahap 3: Web Services dan REST API

SIPUS-Del bertindak sebagai **REST API Provider** untuk aplikasi mobile dan
kiosk melalui endpoint `/api/v1`. SIPUS-Del juga menjadi **REST API Consumer**
ketika mengambil metadata buku dari OpenLibrary berdasarkan ISBN. REST dipilih
untuk mobile/kiosk karena stateless, ringan, memakai JSON, dan memanfaatkan
semantik HTTP; SOAP hanya dipertahankan untuk kompatibilitas Sistem Yudisium
legacy.

### Endpoint Tahap 3

| Method | Endpoint | Keterangan |
|---|---|---|
| POST | `/api/v1/auth/login` | Login email dan token Sanctum |
| GET/POST | `/api/v1/auth/me`, `/api/v1/auth/logout` | Profil dan pencabutan token |
| GET | `/api/v1/bukus` | Katalog, pencarian, filter, sorting, pagination |
| GET | `/api/v1/bukus/{id}` | Detail buku, kategori, dan riwayat |
| POST/PUT/PATCH/DELETE | `/api/v1/bukus/{id}` | CRUD ber-Bearer Token |
| GET/POST | `/api/v1/peminjamans` | Daftar dan transaksi peminjaman ACID |
| PATCH | `/api/v1/peminjamans/{id}/kembalikan` | Pengembalian ber-lock |
| GET | `/api/v1/bukus/{id}/referensi-eksternal` | Consumer OpenLibrary |
| GET/POST | `/soap/sipus.wsdl`, `/soap/sipus` | WSDL dan clearance SOAP |

Kontrak OpenAPI dan koleksi Postman tersedia di
`docs/openapi-sipus-v1.yaml` dan `docs/SIPUS-Del-APIv1.postman_collection.json`.
Error API 401, 404, dan 422 menggunakan `application/problem+json` sesuai RFC
7807. Link `_links` pada Resource menunjukkan aksi yang tersedia berdasarkan
stok buku atau status peminjaman.

### Cara Menguji REST API dengan Postman

Jalankan server dengan `php artisan serve`, lalu import koleksi
`docs/SIPUS-Del-APIv1.postman_collection.json` ke Postman. Atur variable:

```text
base_url = http://127.0.0.1:8000
token = kosong sebelum login
```

Urutan pengujian:

1. `POST /api/v1/auth/login`, lalu salin token ke variable `token`.
2. `GET /api/v1/auth/me` menggunakan `Authorization: Bearer {{token}}`.
3. `GET /api/v1/bukus` untuk katalog, pagination, kategori, dan `_links`.
4. `GET /api/v1/bukus?search=laravel` untuk pencarian.
5. `GET /api/v1/bukus?kategori_id=1` untuk filter kategori.
6. `GET /api/v1/bukus?tersedia=1` untuk filter stok tersedia.
7. `GET /api/v1/bukus?sort=-judul` untuk sorting menurun.
8. `GET /api/v1/bukus?page=2` untuk pagination.
9. Uji `POST`, `PUT`, `PATCH`, dan `DELETE /api/v1/bukus/{id}`.
10. Uji `GET` dan `POST /api/v1/peminjamans`, termasuk stok habis dan konflik.
11. Uji `PATCH /api/v1/peminjamans/{id}/kembalikan`.
12. Uji referensi OpenLibrary dan endpoint SOAP.

Endpoint katalog publik tidak memerlukan token. Endpoint CRUD buku dan
peminjaman memerlukan:

```text
Authorization: Bearer {{token}}
Accept: application/json
Content-Type: application/json
```

### Bukti Pengujian

Simpan screenshot hasil Postman di `docs/screenshots/`, misalnya:

```text
01-login-bearer-token.png
02-katalog-pencarian-filter-sorting.png
03-katalog-pagination-hateoas.png
04-post-buku-201-location.png
05-put-vs-patch.png
06-peminjaman-sukses-201.png
07-stok-habis-422.png
08-peminjaman-conflict-409.png
09-pengembalian.png
10-openlibrary.png
11-soap-success.png
12-soap-fault.png
```

Sensor password, Bearer Token, dan token eksternal sebelum screenshot
dimasukkan ke repository atau laporan.

### Validasi Sebelum Pengumpulan

```powershell
php artisan route:list
php artisan test
vendor\bin\pint --test
```

Pastikan `/login`, `/buku`, `/kategori`, dan `/peminjaman` tetap berjalan,
endpoint REST menghasilkan JSON, dan endpoint SOAP menghasilkan XML.

### Naskah Video Demonstrasi

**Pembukaan:**  
"Pada video ini saya mendemonstrasikan SIPUS-Del Tahap 3. Fitur yang diuji
adalah REST API Provider, Bearer Token Sanctum, transaksi peminjaman ACID,
SOAP untuk sistem legacy, consumer OpenLibrary, HATEOAS, RFC 7807, OpenAPI,
dan koleksi Postman."

**Persiapan:**  
"Saya menjalankan `php artisan migrate:fresh --seed` untuk menyiapkan database
latihan, kemudian menjalankan `php artisan serve`. Seeder membuat akun uji,
kategori, buku, dan transaksi contoh."

**Login:**  
"Saya mengirim `POST /api/v1/auth/login` menggunakan email pustakawan.
Response `200 OK` menghasilkan Bearer Token Sanctum. Token ini digunakan untuk
endpoint yang membutuhkan autentikasi."

**Katalog:**  
"Saya menguji `GET /api/v1/bukus`. Endpoint ini publik dan menghasilkan JSON
terpaginasi. Saya juga menguji search, filter kategori, filter stok tersedia,
sorting, dan pagination. `_links` menunjukkan aksi yang tersedia. Link
peminjaman hanya muncul jika stok buku lebih dari nol."

**CRUD Buku:**  
"Saya membuat buku dengan POST dan mendapatkan `201 Created` serta header
Location. PUT membutuhkan seluruh atribut, sedangkan PATCH dapat mengubah
sebagian atribut seperti stok. Data tidak valid menghasilkan `422` dalam
format RFC 7807."

**Transaksi:**  
"Saya menguji peminjaman buku. Sistem menggunakan `DB::transaction` dan
`lockForUpdate` untuk mengunci stok. Stok habis menghasilkan `422`, pinjaman
aktif yang sama menghasilkan `409`, dan pengembalian mengubah status serta
menambah stok."

**SOAP:**  
"Saya membuka `/soap/sipus.wsdl` untuk melihat kontrak XML. Kemudian saya
mengirim SOAP Envelope berisi NIM. Sistem mengembalikan jumlah pinjaman aktif
dan status bebas pustaka. XML rusak atau NIM kosong menghasilkan `soap:Fault`."

**Consumer eksternal:**  
"Endpoint referensi eksternal mengambil metadata buku berdasarkan ISBN dari
OpenLibrary. Jika layanan timeout atau data tidak ditemukan, sistem
mengembalikan fallback yang aman."

**Penutup:**  
"SIPUS-Del tetap mempertahankan web Blade lama, sekaligus menyediakan REST
stateless untuk mobile dan kiosk, SOAP untuk sistem legacy, dokumentasi
OpenAPI, serta koleksi Postman untuk pengujian berulang."

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
