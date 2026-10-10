# SIPUS-Del: Sistem Informasi Perpustakaan Kampus Del

Tugas Mandiri Praktikum Minggu 05, Pemrograman & Pengujian Web (12S3101), Institut Teknologi Del.

- Nama: Kezia Vania Pasaribu
- NIM: 12S24009

Aplikasi CRUD perpustakaan kampus berbasis **Laravel 11** dengan arsitektur **MVC**, relasi Eloquent, validasi server-side, Blade Component, dan pembuktian eager loading lewat audit SQL.

## Fitur

- CRUD **Buku** dan **Kategori** (7 aksi RESTful lewat `Route::resource` + Implicit Route Model Binding)
- Relasi One-to-Many: `Kategori hasMany Buku`, `Buku belongsTo Kategori`
- Eager Loading (`Buku::with('kategori')`) di seluruh halaman index
- Validasi server-side (ISBN unik, judul minimal 5 karakter, stok minimal 0) dan proteksi CSRF di semua form
- Master layout `<x-layout>` bertema Del Purple (`#4C1D95`), pagination, flash message, dan `@forelse`
- Seeder + Factory (Faker): 5 kategori dan 20 buku
- Audit query SQL lewat `DB::listen`

## Teknologi

PHP 8.2+, Laravel 11, SQLite, Bootstrap 5 (CDN), Composer.

## Instalasi

```bash
git clone https://github.com/[username]/ppw-2026-week5-[NIM].git
cd ppw-2026-week5-[NIM]
composer install
copy .env.example .env        # Linux/macOS: cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

Buka `http://localhost:8000`. Jika diminta membuat file `database/database.sqlite`, jawab `yes`.

Catatan: pengaturan `policy.advisories.block=false` sudah ada di `composer.json` karena Laravel 11.x terkena blokir security advisory pada Composer versi terbaru. Ini hanya untuk keperluan tugas lokal.

## Struktur MVC

| Lapisan | Lokasi |
|---|---|
| Model | `app/Models/Buku.php`, `app/Models/Kategori.php` |
| View | `resources/views/buku/`, `resources/views/kategori/`, `resources/views/components/layout.blade.php` |
| Controller | `app/Http/Controllers/BukuController.php`, `KategoriController.php` |
| Routes | `routes/web.php` |
| Migrations | `database/migrations/` |
| Seeders & Factories | `database/seeders/`, `database/factories/` |

## Bukti Audit SQL (Eager Loading)

Log query ada di folder `docs/`:

- `docs/audit-sql-index-buku.txt`: halaman `/buku` hanya menjalankan 3 query (hitung total pagination, ambil 10 buku, ambil semua kategori terkait dalam satu query `WHERE id IN (...)`).
- `docs/audit-sql-lazy-vs-eager.txt`: perbandingan lazy loading (1 + N query, terjadi N+1 problem) dengan eager loading (2 query).

Query hitung total pada halaman index berasal dari `paginate()`, sehingga jumlahnya 3, bukan 2.

## Konvensi Commit

Mengikuti Conventional Commits (`feat`, `perf`, `docs`, `chore`).