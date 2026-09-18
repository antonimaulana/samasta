# SIMTAMAN

**Sistem Informasi Manajemen Pertamanan** — aplikasi web untuk Disperakimtan Kota Batam.

SIMTAMAN mengintegrasikan basis data RTH, monitoring operasional pertamanan, portal informasi publik, aduan & survey masyarakat, serta modul evaluasi RAP.

## Stack

- PHP 8.2+ / Laravel 12
- SQLite atau MySQL
- Tailwind CSS + Vite

## Setup singkat

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

Variabel penting di `.env`:

```env
APP_NAME="SIMTAMAN"
APP_FULL_NAME="Sistem Informasi Manajemen Pertamanan"
```

## Dokumentasi

- **KAK (Kerangka Acuan Kerja):** `docs/KAK-SIMTAMAN.md`
- **Desain basis data:** `docs/DESAIN-BASIS-DATA-SIMTAMAN.md`
- **Arsitektur sistem:** `docs/ARSITEKTUR-SISTEM-SIMTAMAN.md`
- Spesifikasi environment: `docs/SPESIFIKASI-TEKNIS-ENVIRONMENT.md`
- Deploy VPS: `docs/deploy/`
- Generate PDF arsitektur: `php artisan docs:architecture-pdf`
- Generate Word (KAK + DB + arsitektur): `php scripts/generate-kak-documentation-docx.php`

## Lisensi

Proprietary — Disperakimtan Kota Batam.
