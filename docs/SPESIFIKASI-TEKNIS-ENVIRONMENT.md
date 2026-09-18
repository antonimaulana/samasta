# Spesifikasi Teknis Environment  
## SIMTAMAN (Sistem Informasi Manajemen Pertamanan)

| Item | Keterangan |
|------|------------|
| **Aplikasi** | SIMTAMAN — Sistem Informasi Manajemen Pertamanan (Disperakimtan Kota Batam) |
| **Nama sistem** | SIMTAMAN |
| **Versi framework** | Laravel 12.x |
| **Bahasa** | PHP 8.2+ |
| **Penyusun dokumen** | Tim pengembang aplikasi |
| **Tujuan dokumen** | Lampiran pengajuan hosting & integrasi ke **Dinas Komunikasi dan Informatika Kota Batam** |

---

## 1. Latar belakang

Aplikasi **SIMTAMAN** adalah sistem informasi pertamanan dan ruang terbuka hijau (RTH) untuk:

- **Publik:** jelajahi taman, peta interaktif, aduan masyarakat, ensiklopedia, survey kepuasan.
- **Internal Disperakimtan:** dashboard operasional, data taman, pemeliharaan, kinerja pertamanan, bibit, aduan, laporan PDF, manajemen pengguna dengan hak akses per wilayah.

Dokumen ini menjelaskan **environment teknis** yang diperlukan agar aplikasi dapat dioperasikan sesuai standar infrastruktur pemerintahan daerah.

---

## 2. Arsitektur sistem

```
                    ┌─────────────────────────────────────┐
                    │         Pengguna (Browser)          │
                    └─────────────────┬───────────────────┘
                                      │ HTTPS (443)
                    ┌─────────────────▼───────────────────┐
                    │   Web Server (Nginx / Apache)       │
                    │   Document root: .../public/        │
                    └─────────────────┬───────────────────┘
                                      │ FastCGI
                    ┌─────────────────▼───────────────────┐
                    │   PHP-FPM 8.2+                        │
                    │   Laravel Application                 │
                    └───────┬─────────────────┬─────────────┘
                            │                 │
              ┌─────────────▼─────┐   ┌───────▼──────────────┐
              │ MySQL / MariaDB   │   │ storage/app/public   │
              │ (data aplikasi)   │   │ (upload foto/file)   │
              └───────────────────┘   └──────────────────────┘

Proses latar belakang:
  • Cron: php artisan schedule:run (setiap menit)
  • Supervisor: php artisan queue:work (notifikasi/job)
```

**Health check:** `GET https://domain.go.id/up` — endpoint bawaan Laravel untuk monitoring uptime.

---

## 3. Stack teknologi

| Komponen | Versi / Teknologi | Peran |
|----------|-------------------|-------|
| Runtime | PHP **8.2** atau lebih baru | Eksekusi backend |
| Framework | **Laravel 12** | MVC, auth, ORM, queue, scheduler |
| Template UI | **Blade** + **Tailwind CSS 4** | Tampilan publik & admin |
| Build asset | **Vite 7** + **Node.js 20+** | Kompilasi CSS/JS (hanya saat deploy/build) |
| Database (production) | **MySQL 8** / **MariaDB 10.6+** | Penyimpanan data |
| Database (development) | SQLite | Hanya untuk pengembangan lokal |
| PDF | DomPDF 3.x | Export laporan (butuh ekstensi **GD**) |
| Autentikasi | Session Laravel | Login email/password |
| Authorization | Role: Admin, Operator, Viewer + scope wilayah operator | Kontrol akses modul |

**Catatan:** Node.js **tidak** diperlukan di server production jika folder `public/build/` sudah dihasilkan saat proses build/deploy.

---

## 4. Lingkungan (environment) aplikasi

### 4.1 Development (lokal)

Digunakan tim pengembang di workstation.

| Parameter | Nilai |
|-----------|-------|
| OS | Windows / Linux / macOS |
| Server | `php artisan serve` atau XAMPP/Laragon |
| `APP_ENV` | `local` |
| `APP_DEBUG` | `true` |
| Database | SQLite (`database/database.sqlite`) |
| Email | `MAIL_MAILER=log` |

### 4.2 Staging / UAT

Digunakan uji coba internal Disperakimtan & Kominfo sebelum go-live.

| Parameter | Nilai |
|-----------|-------|
| OS | **Linux** (disarankan) |
| Web server | Nginx + PHP-FPM |
| `APP_ENV` | `staging` |
| `APP_DEBUG` | **`false`** |
| Database | MySQL/MariaDB terpisah dari production |
| SSL | HTTPS dengan sertifikat (boleh self-signed internal) |

### 4.3 Production

| Parameter | Nilai |
|-----------|-------|
| OS | **Linux** server Kominfo |
| Web server | **Nginx** + PHP-FPM (atau Apache setara) |
| `APP_ENV` | **`production`** |
| `APP_DEBUG` | **`false` (wajib)** |
| `APP_URL` | `https://sitaman.batam.go.id` (contoh) |
| Database | MySQL/MariaDB dengan backup terjadwal |
| Session | `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true` |

---

## 5. Spesifikasi server minimum (production)

| Resource | Minimum | Disarankan |
|----------|---------|------------|
| CPU | 2 vCPU | 4 vCPU |
| RAM | 2 GB | 4 GB |
| Storage | 20 GB SSD | 50 GB SSD (termasuk foto) |
| OS | Ubuntu 22.04 LTS / RHEL setara | LTS dengan patch keamanan aktif |

### 5.1 Ekstensi PHP wajib

```
php8.2-fpm
php8.2-mysql      (PDO MySQL)
php8.2-mbstring
php8.2-xml
php8.2-curl
php8.2-zip
php8.2-bcmath
php8.2-intl
php8.2-gd         ← wajib untuk export PDF
php8.2-fileinfo
php8.2-openssl
```

Verifikasi GD:

```bash
php -m | grep -i gd
```

### 5.2 Perangkat lunak pendukung

| Software | Fungsi |
|----------|--------|
| **Composer 2.x** | Install dependency PHP |
| **Nginx** atau Apache | Web server |
| **MySQL/MariaDB** | Database |
| **Supervisor** | Menjalankan queue worker |
| **Cron** | Menjalankan Laravel scheduler |
| **Certbot / sertifikat SSL** | HTTPS |

---

## 6. Struktur direktori deployment

```
/var/www/sitaman-batam/
├── app/                    ← kode aplikasi
├── bootstrap/
├── config/
├── database/
├── public/                 ← DOCUMENT ROOT web server
│   ├── index.php
│   ├── build/              ← hasil npm run build
│   └── storage/            ← symlink ke ../storage/app/public
├── resources/
├── routes/
├── storage/                ← harus writable (www-data)
│   ├── app/public/         ← upload foto
│   └── logs/
├── vendor/                 ← composer install --no-dev
└── .env                    ← konfigurasi (rahasia, tidak di Git)
```

**Penting:** Web server hanya boleh mengakses folder `public/`, bukan root proyek.

---

## 7. Modul aplikasi & kebutuhan data

| Modul | Akses | Keterangan |
|-------|-------|------------|
| Beranda & taman publik | Publik | Pencarian, peta, detail taman |
| Aduan masyarakat | Publik (form) | Upload foto, throttle 5 req/menit |
| Survey kepuasan | Publik | Throttle 10 req/menit |
| Ensiklopedia & kuis | Publik | Konten edukasi |
| Admin dashboard | Auth | Statistik operasional |
| CRUD taman, bibit, layanan, pemeliharaan | Auth (operator+) | Scope wilayah untuk operator |
| Kelola pengguna | Admin saja | RBAC admin/operator/viewer |
| Export PDF | Auth | Butuh ekstensi GD |
| Log aktivitas admin | Admin | Audit trail |

---

## 8. Keamanan aplikasi

### 8.1 Fitur keamanan yang sudah ada

- Autentikasi session dengan password hashing (bcrypt)
- Role-based access control (Admin, Operator, Viewer)
- Pembatasan akses data per wilayah kerja (operator tim pelaksana)
- CSRF protection pada form
- Rate limiting login & form publik (aduan, survey)
- Activity log untuk aksi admin
- Policy Laravel per model (view/update/delete)

### 8.2 Konfigurasi wajib production

| Setting | Nilai |
|---------|-------|
| `APP_DEBUG` | `false` |
| `SESSION_ENCRYPT` | `true` |
| `SESSION_SECURE_COOKIE` | `true` (dengan HTTPS) |
| `APP_KEY` | Unik per environment, dirahasiakan |
| Akun admin default | **Jangan** gunakan password seed di production |

### 8.3 Rekomendasi layer server (Kominfo)

- HTTPS dengan TLS 1.2+
- Firewall: buka 443 (dan 80 redirect)
- Backup database & storage harian
- Pembaruan patch OS & PHP berkala
- Monitoring log Nginx + Laravel (`storage/logs/`)
- Pembatasan akses SSH server

---

## 9. Layanan eksternal (internet)

Aplikasi memanggil resource di luar server untuk beberapa fitur:

| Layanan | URL | Fitur |
|---------|-----|-------|
| OpenStreetMap tiles | `tile.openstreetmap.org` | Peta taman |
| Nominatim OSM | `nominatim.openstreetmap.org` | Geocoder koordinat → kelurahan |
| CDN (sementara) | `unpkg.com`, `cdn.jsdelivr.net` | Leaflet, Swiper, Chart.js |

**Rekomendasi untuk lingkungan pemerintah:**

1. Konfirmasi ke Kominfo apakah akses keluar ke domain di atas diizinkan.
2. Jika tidak: **self-host** library JS/CSS dan pertimbangkan tile server internal.
3. Set `WILAYAH_GEOCODER_ENABLED=false` jika geocoder eksternal tidak diizinkan (input kelurahan manual tetap bisa).

---

## 10. Proses background

### 10.1 Cron (wajib)

Tambahkan ke crontab user web server:

```cron
* * * * * cd /var/www/sitaman-batam && php artisan schedule:run >> /dev/null 2>&1
```

**Jadwal terjadwal aplikasi:**

| Waktu (WIB) | Perintah | Fungsi |
|-------------|----------|--------|
| 07:00 | `layanan:send-reminders` | Reminder jadwal layanan |
| 07:30 | `operational:send-digest` | Email ringkasan operasional (jika SMTP aktif) |

### 10.2 Queue worker (wajib jika `QUEUE_CONNECTION=database`)

Lihat file contoh: `docs/deploy/supervisor-sitaman.conf.example`

```bash
php artisan queue:work database --sleep=3 --tries=3
```

---

## 11. Prosedur deployment production

### 11.1 Checklist deploy

| No | Langkah | Perintah / Keterangan |
|----|---------|------------------------|
| 1 | Upload kode | Git clone / CI/CD ke `/var/www/sitaman-batam` |
| 2 | Install PHP deps | `composer install --no-dev --optimize-autoloader` |
| 3 | Environment | Salin `docs/deploy/.env.production.example` → `.env`, sesuaikan |
| 4 | App key | `php artisan key:generate` |
| 5 | Migrasi DB | `php artisan migrate --force` |
| 6 | Seed referensi | `php artisan db:seed --class=WilayahBatamSeeder` |
| 7 | Seed tim | `php artisan db:seed --class=TimPelaksanaSeeder` |
| 8 | Admin | Buat manual via `php artisan tinker` (lihat §11.3) |
| 9 | Storage link | `php artisan storage:link` |
| 10 | Build frontend | `npm ci && npm run build` (bisa di mesin build) |
| 11 | Permission | `chown -R www-data:www-data storage bootstrap/cache` |
| 12 | Cache config | `php artisan config:cache && route:cache && view:cache` |
| 13 | Nginx | Aktifkan config (lihat `docs/deploy/nginx-sitaman.conf.example`) |
| 14 | Supervisor | Aktifkan queue worker |
| 15 | Cron | Aktifkan schedule:run |
| 16 | Uji | Akses `/up`, login admin, upload foto, export PDF |

### 11.2 Build frontend (Node.js)

Dijalankan **sekali per release** (boleh di mesin CI, bukan di server production):

```bash
npm ci
npm run build
```

Hasil build: folder `public/build/` (wajib ada sebelum go-live).

### 11.3 Pembuatan akun admin production

```bash
php artisan tinker
```

```php
\App\Models\User::create([
    'name' => 'Administrator',
    'email' => 'admin@disperakimtan.batam.go.id',
    'password' => 'GantiPasswordKuatMin12Karakter',
    'role' => 'admin',
]);
```

---

## 12. Template environment production

File lengkap: **`docs/deploy/.env.production.example`**

Variabel kritis:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sitaman.batam.go.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=sitaman_batam
DB_USERNAME=sitaman_app
DB_PASSWORD=********

SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

MAIL_MAILER=smtp
MAIL_HOST=smtp.batam.go.id
```

---

## 13. Contoh konfigurasi Nginx

File lengkap: **`docs/deploy/nginx-sitaman.conf.example`**

Poin penting:

- `root` → `/var/www/samasta/public`
- Redirect HTTP → HTTPS
- `client_max_body_size 20M` (upload foto)
- PHP-FPM socket: `unix:/run/php/php8.3-fpm.sock`
- Trust proxy Cloudflare: `$middleware->trustProxies(at: '*');` di `bootstrap/app.php` (sudah aktif)

---

## 14. Backup & recovery

| Objek | Frekuensi | Metode |
|-------|-----------|--------|
| Database MySQL | Harian | `mysqldump` + retensi 30 hari |
| `storage/app/public/` | Harian | rsync / snapshot |
| `.env` | Saat perubahan | Simpan aman (vault/password manager) |
| Kode aplikasi | Per release | Git tag / arsip zip |

**Uji restore** minimal sekali sebelum go-live.

---

## 15. Monitoring

| Item | Endpoint / Lokasi |
|------|-------------------|
| Uptime | `GET /up` |
| Log aplikasi | `storage/logs/laravel.log` |
| Log Nginx | `/var/log/nginx/sitaman-*.log` |
| Log queue | `storage/logs/queue-worker.log` |
| Disk usage | Monitor folder `storage/` (foto) |

---

## 16. Daftar lampiran teknis

| Lampiran | Path di repositori |
|----------|-------------------|
| A. Template `.env` production | `docs/deploy/.env.production.example` |
| B. Contoh Nginx | `docs/deploy/nginx-sitaman.conf.example` |
| C. Contoh Supervisor queue | `docs/deploy/supervisor-sitaman.conf.example` |
| D. Template `.env` development | `.env.example` (root proyek) |
| E. Referensi VPS BatamGarden | `docs/deploy/VPS-BATAMGARDEN.md` |
| F. Setup GitHub &amp; deploy key | `docs/deploy/GITHUB-SETUP.md` |
| G. Panduan deploy PDF | `docs/PANDUAN-DEPLOY-BIZNET-CLOUDFLARE.pdf` |
| H. Dokumentasi arsitektur PDF | `docs/DOKUMENTASI-ARSITEKTUR-SIMTAMAN.pdf` |

---

## 17. Checklist pengajuan ke Kominfo Batam

| No | Persyaratan | Status tim dev | Keterangan |
|----|-------------|----------------|------------|
| 1 | Domain / subdomain `.go.id` | ☐ | Koordinasi Disperakimtan + Kominfo |
| 2 | Server Linux + Nginx + PHP 8.2 | ☐ | Spesifikasi §5 |
| 3 | MySQL/MariaDB + user khusus app | ☐ | Bukan akun root |
| 4 | Sertifikat SSL | ☐ | HTTPS wajib |
| 5 | Akses keluar OSM/CDN (jika diperlukan) | ☐ | Lihat §9 |
| 6 | SMTP email Pemda | ☐ | Notifikasi operasional |
| 7 | Cron + Supervisor | ☐ | §10 |
| 8 | Backup DB & storage | ☐ | §14 |
| 9 | Akun admin production | ☐ | Buat manual, §11.3 |
| 10 | UAT staging | ☐ | Sebelum DNS production |

---

## 18. Kontak teknis

| Peran | Nama / Unit | Kontak |
|-------|-------------|--------|
| Pemilik aplikasi | Disperakimtan Kota Batam | *(isi)* |
| Pengembang | *(isi)* | *(isi)* |
| Infrastruktur | Dinas Kominfo Kota Batam | *(isi)* |

---

## 19. Server production sementara (Biznet Gio)

VPS aktif untuk staging/deploy awal:

| Item | Nilai |
|------|--------|
| Nama | BatamGarden |
| Provider | Biznet Gio Cloud (MS 4.2) |
| OS | Ubuntu 24.04 LTS |
| Spesifikasi | 2 vCPU · 4 GB RAM · 60 GB SSD |
| Region | West Java |
| Public IP | `103.150.92.153` |
| Reverse DNS | `ip-153-92-150-103.wjv-1.biznetg.io` |
| SSH user | `GardenBatam` |
| SSH key | `GardenBatam2026.pem` |
| Path app | `/var/www/samasta` |

Detail SSH dan checklist deploy: **`docs/deploy/VPS-BATAMGARDEN.md`**

---

*Dokumen ini disusun berdasarkan kondisi codebase SIMTAMAN per Agustus 2026. Revisi dokumen mengikuti perubahan major pada aplikasi.*
