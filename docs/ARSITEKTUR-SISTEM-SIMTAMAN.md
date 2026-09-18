# Rancangan Arsitektur Sistem SIMTAMAN

**Sistem Informasi Manajemen Pertamanan**  
Disperakimtan Kota Batam · Versi 1.0 · September 2026

---

## 1. Ringkasan Arsitektur

SIMTAMAN dibangun sebagai **aplikasi web monolith** berbasis **Laravel 12** dengan pola **server-side rendering (SSR)**. Seluruh logika bisnis, autentikasi, dan render HTML berada di satu codebase, di-deploy sebagai unit tunggal di VPS pemerintah.

### 1.1 Prinsip Desain

| Prinsip | Penerapan |
|---------|-----------|
| Separation of concerns | Controller tipis, logic di `app/Support/` |
| RBAC + policy | Laravel Policies per model |
| Wilayah scoping | `OperatorWilayahScope` di query layer |
| Report as code | Report Builder classes per modul evaluasi |
| Progressive enhancement | Blade + vanilla JS (picker, peta, chart) |
| Config-driven | `config/simtaman.php` + `.env` |

---

## 2. Diagram Arsitektur Tingkat Tinggi

```
                    ┌─────────────────────────────────────────┐
                    │           PENGGUNA / KLIEN              │
                    ├─────────────┬─────────────┬─────────────┤
                    │  Masyarakat │ Admin/Ops   │  Lapangan   │
                    │  (browser)  │ (browser)   │ (mobile web)│
                    └──────┬──────┴──────┬──────┴──────┬──────┘
                           │             │             │
                           ▼             ▼             ▼
                    ┌─────────────────────────────────────────┐
                    │     CDN / Cloudflare (opsional)         │
                    │     SSL termination, WAF, caching       │
                    └────────────────────┬────────────────────┘
                                         │ HTTPS
                                         ▼
                    ┌─────────────────────────────────────────┐
                    │              NGINX                      │
                    │   root: /var/www/.../public             │
                    │   static: build/, js/, storage symlink  │
                    └────────────────────┬────────────────────┘
                                         │ FastCGI
                                         ▼
                    ┌─────────────────────────────────────────┐
                    │            PHP-FPM 8.2+                 │
                    │         Laravel 12 Application          │
                    ├─────────────────────────────────────────┤
                    │  Routes → Middleware → Controllers      │
                    │           ↓                           │
                    │  Support (Business Logic)             │
                    │  Models (Eloquent ORM)                  │
                    │  Policies (Authorization)               │
                    └──────────┬──────────────┬───────────────┘
                               │              │
              ┌────────────────┘              └────────────────┐
              ▼                                                ▼
    ┌──────────────────┐                          ┌──────────────────┐
    │  MySQL/MariaDB   │                          │  File Storage    │
    │  (primary data)  │                          │  storage/app/    │
    └──────────────────┘                          │  public disk     │
                                                  └──────────────────┘

    ┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐
    │  Scheduler/Cron  │    │  Queue Worker    │    │  External APIs   │
    │  artisan schedule│    │  (opsional)      │    │  OSM, Nominatim  │
    └──────────────────┘    └──────────────────┘    └──────────────────┘
```

---

## 3. Layer Aplikasi

### 3.1 Presentation Layer

| Komponen | Teknologi | Lokasi |
|----------|-----------|--------|
| Template HTML | Blade | `resources/views/` |
| Layout admin | `layouts/admin.blade.php` | Sidebar nav, alert badges |
| Layout lapangan | `layouts/lapangan.blade.php` | Mobile-first, touch-friendly |
| Layout publik | `layouts/public.blade.php` | Portal masyarakat |
| CSS | Tailwind CSS 4 | Vite build → `public/build/` |
| JS interaktif | Vanilla JS | `public/js/` (petugas-picker, dll.) |
| Chart | Chart.js (CDN) | Dashboard, evaluasi, RTH publik |
| Peta | Leaflet (CDN) | Peta taman |
| PDF view | Blade + DomPDF | Laporan & dokumentasi |

### 3.2 Application Layer

| Komponen | Tanggung Jawab |
|----------|----------------|
| **Controllers** | HTTP request/response, validasi form, authorize |
| **Middleware** | Auth, RBAC, audit, lapangan PIN, viewer scope |
| **Policies** | Authorization per model/action |
| **Form Requests** | Validasi input (selective) |
| **Support classes** | Business logic, report builders, resolvers |

Struktur controller:

```
app/Http/Controllers/
├── Admin/           # Back-office (30+ controllers)
│   ├── Evaluasi*    # 8 modul evaluasi RAP
│   └── Dpa/         # Monitoring anggaran
├── Auth/            # LoginController
├── Lapangan/        # OperasionalController
└── (Public)         # Home, Rth, Taman, Aduan, Survey, Ensiklopedia
```

### 3.3 Domain / Business Logic Layer (`app/Support/`)

| Kelas/Kelompok | Fungsi |
|----------------|--------|
| `OperatorWilayahScope` | Filter query by operator team |
| `TimPelaksanaResolver` | Resolve kelurahan ↔ tim |
| `TamanCompleteness` | Skor kelengkapan profil taman |
| `PetugasRosterBuilder` | Roster petugas per tim |
| `PetugasAssignment` | Validasi & sync petugas ke kegiatan |
| `PemeliharaanTamanRecorder` | Simpan pemeliharaan (admin + lapangan) |
| `PermohonanProgressRecorder` | Update progres permohonan |
| `PublicRthStatisticsBuilder` | Statistik RTH portal publik |
| `RthArProfileBuilder` | Profil WebAR per taman |
| `Evaluasi/*ReportBuilder` | 8 builder laporan evaluasi RAP |
| `RapKonsolidasiReportBuilder` | Indeks kinerja gabungan |
| `DpaDocumentGenerator` | Generate dokumen pengadaan |
| `LapanganGuestAccess` | Session PIN lapangan |
| `LapanganMenu` | Menu input lapangan per tim |

### 3.4 Data Access Layer

- **Eloquent ORM** — 36 model di `app/Models/`
- Relasi: `belongsTo`, `hasMany`, `belongsToMany`
- Query scoping via global scope pattern (wilayah)
- Migration version control di `database/migrations/`

---

## 4. Routing & Kanal Akses

```
routes/web.php
├── public.php      → /, /taman, /rth-kota-batam, /aduan, /survey, ...
├── lapangan.php    → /lapangan/*  (middleware: lapangan.access)
└── admin.php       → /admin/*     (middleware: auth, admin.access, ...)
```

### 4.1 Middleware Pipeline

| Alias | Kelas | Fungsi |
|-------|-------|--------|
| `auth` | Laravel default | Session authentication |
| `admin.access` | `EnsureAdminAccess` | Write/delete rules, viewer block |
| `admin.manage` | `EnsureAdminManage` | Admin-only routes |
| `admin.viewer.scope` | `EnsureViewerScope` | Restrict viewer to allowlist |
| `admin.audit` | `LogAdminActivity` | Write activity_logs |
| `lapangan.access` | `EnsureLapanganAccess` | PIN session OR canWrite user |

### 4.2 Matriks Akses Modul

| Modul | Public | Viewer | Operator | Admin |
|-------|--------|--------|----------|-------|
| Portal informasi | ✓ | ✓ | ✓ | ✓ |
| Aduan/survey submit | ✓ | — | — | — |
| Dashboard admin | — | ✓ (terbatas) | ✓ | ✓ |
| CRUD operasional | — | — | ✓ (wilayah) | ✓ |
| Evaluasi RAP | — | ✓ (read) | ✓ (wilayah) | ✓ |
| User management | — | — | — | ✓ |
| DPA monitoring | — | — | — | ✓ |
| Input lapangan | PIN | — | ✓ | ✓ |

---

## 5. Alur Data Utama

### 5.1 Input Pemeliharaan Rutin

```
[Lapangan/Admin Form]
        │
        ▼
[PemeliharaanTamanController / PemeliharaanTamanRecorder]
        │ validate (tim, taman/wilayah, petugas, foto)
        ▼
[PetugasAssignment] ──→ sync pivot pemeliharaan_taman_petugas
        │
        ▼
[pemeliharaan_tamans] + [pemeliharaan_taman_armadas]
        │
        ▼
[Evaluasi: RTH Terpelihara, Kinerja Tim, Pemeliharaan per Taman]
```

### 5.2 Permohonan Layanan → Progres Harian

```
[Admin: create pemangkasan status=Rencana]
        │
        ▼
[Lapangan: update progres harian + foto + petugas]
        │
        ▼
[pemangkasan_progres] ──→ update persentase pemangkasans
        │
        ▼
[Evaluasi: Operasional Permohonan, Kinerja Tim]
```

### 5.3 Evaluasi RAP Konsolidasi

```
8 Report Builders (parallel queries)
        │
        ▼
[RapKonsolidasiReportBuilder::build]
        │ aggregate scores per pilar
        ▼
[View: index + PDF export]
```

---

## 6. Keamanan

### 6.1 Autentikasi

- Session driver: file/database
- Password hashing: bcrypt
- Login throttle: 5 attempts/minute
- Guest redirect: `/login`
- Authenticated redirect: `/admin`

### 6.2 Otorisasi

- Role-based: `users.role`
- Policy-based: per-model (`TamanPolicy`, `PemangkasanPolicy`, ...)
- Wilayah-based: operator filtered by `tim_pelaksana_user` + kelurahan assignment

### 6.3 Input Lapangan (PIN)

```
.env: SIMTAMAN_LAPANGAN_PIN=xxxx
Session key: lapangan_guest_unlocked_at
TTL: 480 menit (default)
```

### 6.4 Proteksi Tambahan

- CSRF token semua form POST
- Rate limit aduan (5/min), survey (10/min)
- File upload: validasi mime & storage disk `public`
- Activity log: semua aksi admin write
- DomPDF/HTML: escape output Blade `{{ }}`

---

## 7. Integrasi Eksternal

| Layanan | Protokol | Penggunaan | Fallback |
|---------|----------|------------|----------|
| OpenStreetMap | HTTPS tile | Peta taman | — |
| Nominatim | HTTPS REST | Reverse geocode → kelurahan | Manual assign |
| CDN (jsdelivr/unpkg) | HTTPS | Leaflet, Chart.js, Swiper | Self-host opsional |
| SMTP | TLS | Digest operasional email | Disabled jika kosong |

---

## 8. Deployment Architecture

### 8.1 Environment

| Env | URL contoh | Database |
|-----|------------|----------|
| Local dev | `localhost:8000` | SQLite |
| Staging VPS | BatamGarden Biznet | MySQL |
| Production | `sitaman.batam.go.id` | MySQL |

### 8.2 Deploy Flow

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build          # or pre-built assets
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl reload php8.2-fpm nginx
```

### 8.3 Process Supervision

| Proses | Tool | Config |
|--------|------|--------|
| Web | Nginx + PHP-FPM | `docs/deploy/nginx-sitaman.conf.example` |
| Scheduler | Cron | `* * * * * php artisan schedule:run` |
| Queue | Supervisor | `docs/deploy/supervisor-sitaman.conf.example` |

### 8.4 Monitoring

- Health: `GET /up` (Laravel built-in)
- Log: `storage/logs/laravel.log`
- Disk: monitor `storage/app/public` growth (foto operasional)

---

## 9. Frontend Build Pipeline

```
resources/css/app.css  ──┐
resources/js/app.js    ──┼──► Vite 7 ──► public/build/manifest.json
                         │              public/build/assets/*.css|js
Tailwind CSS 4           ──┘

Blade @vite(['resources/css/app.css', 'resources/js/app.js'])
Standalone JS: public/js/petugas-picker.js (loaded via @push scripts)
```

---

## 10. Testing Architecture

```
tests/
├── Feature/     # HTTP tests per modul (auth, operasional, evaluasi, ...)
├── Unit/        # Pure logic tests
└── Support/     # Test helpers (PetugasTestHelpers, ...)
```

- PHPUnit 11, in-memory SQLite for tests
- `RefreshDatabase` trait per test class
- CI-ready: `php artisan test`

---

## 11. Skalabilitas & Evolusi

### 11.1 Kondisi Saat Ini

Monolith single-server cocok untuk:
- ≤ 100 concurrent users
- ≤ 10.000 record operasional/tahun
- Single organization (Disperakimtan)

### 11.2 Jalur Evolusi (Roadmap)

| Fase | Perubahan Arsitektur |
|------|---------------------|
| Short-term | Redis cache session/query, CDN static assets |
| Medium-term | REST API layer (`/api/v1`) for mobile app |
| Long-term | Read replica MySQL, object storage (S3-compatible) for foto |

---

## 12. Diagram Komponen Modul

```
┌─────────────────────────────────────────────────────────────────┐
│                        SIMTAMAN MONOLITH                        │
├──────────────┬──────────────┬──────────────┬────────────────────┤
│   PORTAL     │    ADMIN     │   LAPANGAN   │    BACKGROUND      │
│              │              │              │                    │
│ HomeController│ Dashboard   │ Operasional  │ Schedule:          │
│ RthController │ Pemeliharaan│ Controller   │  - layanan:remind  │
│ TamanController│ Pemangkasan│              │  - operational:digest│
│ AduanController│ Evaluasi* │              │ Queue (optional)   │
│ SurveyController│ Dpa/*    │              │                    │
│ Ensiklopedia  │ Tim/Petugas │              │                    │
└──────────────┴──────────────┴──────────────┴────────────────────┘
                              │
                    app/Support (shared)
                              │
                    app/Models + MySQL
```

---

## 13. Dokumen Terkait

| Dokumen | Path |
|---------|------|
| KAK | `docs/KAK-SIMTAMAN.md` |
| Desain Basis Data | `docs/DESAIN-BASIS-DATA-SIMTAMAN.md` |
| Spesifikasi Environment | `docs/SPESIFIKASI-TEKNIS-ENVIRONMENT.md` |
| PDF Arsitektur (generate) | `php artisan docs:architecture-pdf` |

---

*Arsitektur ini mencerminkan implementasi aktual repository `sitaman-batam` per September 2026.*
