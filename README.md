# NasLabs Journal

NasLabs Journal adalah personal engineering blog berbasis Laravel monolith. Artikel ditulis dalam Markdown, halaman publik dirender dengan Blade, dan pengelolaan konten berada di dashboard Livewire dengan Flux UI.

## Fitur

- Halaman publik berbahasa Indonesia dan Inggris dengan URL lokal, contoh `/id/articles` dan `/en/articles`.
- Artikel Markdown dengan kategori, tag, gambar sampul, metadata SEO, Open Graph, sitemap, dan RSS.
- Dashboard untuk artikel, media, kategori, tag, analytics editorial, activity log, users, service accounts, dan settings.
- Workflow draft, review, scheduled publishing, archive, trash, restore, serta signed preview untuk draft.
- Upload gambar dengan validasi tipe dan ukuran, termasuk avatar pengguna.
- OpenClaw memakai service account dan scoped API token. Akses API dibatasi berdasarkan scope, ownership artikel, dan kebijakan publishing.
- Health check, scheduler heartbeat, backup terjadwal, audit log, rate limit, dan security headers.

## Stack

- PHP 8.3 atau lebih baru
- Laravel 13
- Livewire 4 dan Flux UI 2
- Blade, Alpine.js, Tailwind CSS, dan Vite
- PostgreSQL untuk runtime
- SQLite in-memory untuk test suite
- `league/commonmark` untuk rendering Markdown
- `spatie/laravel-backup` untuk backup database dan media

## Menjalankan secara lokal

1. Buat database PostgreSQL dan sesuaikan koneksi di `.env`.
2. Salin konfigurasi contoh dan pasang dependency.

```bash
cp .env.example .env
composer install
npm ci
php artisan key:generate
```

3. Atur minimal `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env`. Sebelum seeding, atur `ADMIN_EMAIL` dan `ADMIN_PASSWORD` dengan nilai lokal yang aman.

4. Buat schema, data contoh, symbolic link media, lalu build asset.

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Dashboard tersedia di `/login`. Kredensial administrator mengikuti `ADMIN_EMAIL` dan `ADMIN_PASSWORD` yang digunakan saat seeding. Ganti password setelah login dan jangan gunakan nilai default pada deployment.

## Rute utama

Halaman publik menggunakan prefix locale:

- `/{locale}`
- `/{locale}/articles`
- `/{locale}/articles/{slug}`
- `/{locale}/categories/{slug}`
- `/{locale}/tags/{slug}`
- `/{locale}/projects`
- `/{locale}/about`

URL lama seperti `/articles` dialihkan ke locale default. Feed dan sitemap berada di `/rss.xml` dan `/sitemap.xml`.

Dashboard membutuhkan autentikasi dan menggunakan prefix `/dashboard`:

- `/dashboard/articles`
- `/dashboard/categories`
- `/dashboard/tags`
- `/dashboard/media`
- `/dashboard/analytics`
- `/dashboard/activity`
- `/dashboard/automation/openclaw`
- `/dashboard/users`
- `/dashboard/settings`

Health check tersedia di `/health` dan hanya mengembalikan status `healthy` atau `degraded`.

## OpenClaw API

OpenClaw tidak menggunakan akun dashboard. Buat service account dan token dari **Dashboard → Users & Access**, lalu berikan scope minimum yang diperlukan. Token hanya ditampilkan saat dibuat atau diregenerasi.

API tersedia di `/api/v1` dan memakai Bearer token. Dokumentasi endpoint, scope, workflow review, upload media, serta contoh payload tersedia di [docs/OPENCLAW_API.md](docs/OPENCLAW_API.md).

## Scheduler dan backup

Scheduler menangani publikasi artikel yang sudah jatuh tempo, backup harian, dan cleanup backup. Untuk server, pasang satu cron entry:

```cron
* * * * * cd /path/to/Blog && php artisan schedule:run >> /dev/null 2>&1
```

Jalankan backup manual bila diperlukan:

```bash
php artisan operations:backup
php artisan backup:list
```

Backup mencakup dump PostgreSQL dan media pada `storage/app/public`. Gunakan disk private atau remote untuk production melalui `BACKUP_DISK`, serta isi `BACKUP_ARCHIVE_PASSWORD`.

## Testing dan quality checks

Test suite memakai SQLite in-memory dan tidak memakai database PostgreSQL lokal.

```bash
php artisan test
vendor/bin/pint --test
npm run build
composer audit
npm audit --omit=dev
```

## Deployment

Sebelum production, pastikan setidaknya konfigurasi berikut tersedia:

```env
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
BACKUP_DISK=s3
BACKUP_ARCHIVE_PASSWORD=<secret-unik>
TRUSTED_PROXIES=<ip-proxy-atau-cdn>
```

Urutan deployment dan runbook operasional tersedia di:

- [Deployment checklist](docs/operations/deployment.md)
- [Restore runbook](docs/operations/restore.md)
- [Rollback plan](docs/operations/rollback.md)
