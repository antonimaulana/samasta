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

**Keamanan:** file `.env` **tidak boleh** masuk Git. Setelah clone, pasang hook lokal:

```bash
php scripts/install-git-hooks.php   # Windows: .\scripts\install-git-hooks.ps1
composer check-secrets
```

Panduan lengkap (rotasi rahasia jika pernah ter-push): `docs/deploy/KEAMANAN-ENV-DAN-GIT.md`.

Variabel penting di `.env`:

```env
APP_NAME="SIMTAMAN"
APP_FULL_NAME="Sistem Informasi Manajemen Pertamanan"
```

## Dokumentasi

- **KAK (Kerangka Acuan Kerja):** `docs/KAK-SIMTAMAN.md`
- **Desain basis data:** `docs/DESAIN-BASIS-DATA-SIMTAMAN.md`
- **Arsitektur sistem:** `docs/ARSITEKTUR-SISTEM-SIMTAMAN.md`
- **Flowchart rancangan sistem:** `docs/FLOWCHART-RANCANGAN-SISTEM-SIMTAMAN.md`
- **Lampiran KAK flowchart (1 halaman):** `docs/LAMPIRAN-KAK-FLOWCHART-SIMTAMAN-1-HALAMAN.md` — `php scripts/generate-lampiran-flowchart-kak-docx.php`
- Spesifikasi environment: `docs/SPESIFIKASI-TEKNIS-ENVIRONMENT.md`
- Deploy VPS: `docs/deploy/`
- Generate PDF arsitektur: `php artisan docs:architecture-pdf`
- Generate Word (KAK + DB + arsitektur): `php scripts/generate-kak-documentation-docx.php`

## Lisensi

Proprietary — Disperakimtan Kota Batam.
