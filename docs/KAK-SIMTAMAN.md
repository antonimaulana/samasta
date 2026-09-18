# KERANGKA ACUAN KERJA (KAK)

## Pengembangan dan Operasionalisasi Sistem Informasi Manajemen Pertamanan (SIMTAMAN)

**Organisasi Pengguna:** Dinas Perumahan, Kawasan Permukiman, dan Pertamanan (Disperakimtan) Kota Batam  
**Nama Sistem:** SIMTAMAN — *Sistem Informasi Manajemen Pertamanan*  
**Versi Dokumen:** 1.0  
**Tanggal:** September 2026  

---

## DAFTAR ISI

1. [Latar Belakang](#1-latar-belakang)
2. [Maksud dan Tujuan](#2-maksud-dan-tujuan)
3. [Ruang Lingkup Pekerjaan](#3-ruang-lingkup-pekerjaan)
4. [Keluaran (Deliverables)](#4-keluaran-deliverables)
5. [Metodologi Pelaksanaan](#5-metodologi-pelaksanaan)
6. [Spesifikasi Teknis](#6-spesifikasi-teknis)
7. [Peran dan Tanggung Jawab](#7-peran-dan-tanggung-jawab)
8. [Jadwal Pelaksanaan](#8-jadwal-pelaksanaan)
9. [Persyaratan dan Ketentuan](#9-persyaratan-dan-ketentuan)
10. [Lampiran](#10-lampiran)

---

## 1. Latar Belakang

Kota Batam memiliki kewajiban pengelolaan Ruang Terbuka Hijau (RTH) yang luas dan tersebar di berbagai kecamatan. Disperakimtan Kota Batam melaksanakan fungsi perencanaan, pembangunan, pemeliharaan, dan pelayanan pertamanan kepada masyarakat. Kegiatan operasional meliputi pemeliharaan rutin taman, penanganan permohonan layanan (pemangkasan, pohon tumbang, mini garden), pengelolaan bibit, pendataan aset RTH, monitoring anggaran (DPA), serta evaluasi kinerja pelaksana.

Sebelum adanya sistem terintegrasi, data operasional masih tersebar dalam berbagai format (dokumen, spreadsheet, laporan manual) sehingga menyulitkan:

- Monitoring capaian pemeliharaan RTH secara real-time
- Pelaporan evaluasi RAP (Rencana Aksi Perubahan) yang terukur
- Transparansi informasi RTH kepada masyarakat
- Akuntabilitas tim pelaksana dan petugas lapangan
- Pengambilan keputusan berbasis data

**SIMTAMAN** dikembangkan sebagai platform web terintegrasi untuk mengatasi permasalahan tersebut, dengan tiga kanal akses utama: **Portal Publik**, **Back-Office Admin**, dan **Input Lapangan**.

---

## 2. Maksud dan Tujuan

### 2.1 Maksud

Menyediakan sistem informasi terpadu yang mendukung siklus pengelolaan pertamanan — dari basis data RTH, operasional lapangan, partisipasi masyarakat, hingga evaluasi kinerja dan pelaporan RAP.

### 2.2 Tujuan

| No | Tujuan | Indikator Keberhasilan |
|----|--------|------------------------|
| 1 | Terwujudnya basis data RTH yang lengkap, akurat, dan dapat diakses publik | ≥ 80% profil taman berstatus *lengkap*; portal publik aktif |
| 2 | Terdokumentasinya seluruh kegiatan pemeliharaan rutin dan layanan permohonan | 100% kegiatan tercatat dengan foto, tim, dan petugas |
| 3 | Tersedianya laporan evaluasi operasional dan RAP konsolidasi per periode | 8 modul evaluasi + ekspor PDF berjalan |
| 4 | Partisipasi masyarakat melalui aduan dan survey kepuasan terkelola | Workflow aduan + evaluasi masukan masyarakat |
| 5 | Efisiensi input data lapangan oleh tim pelaksana | Input lapangan via PIN/login; waktu input ≤ 5 menit/kegiatan |
| 6 | Kesiapan deploy produksi di infrastruktur pemerintah | HTTPS, backup, audit trail, RBAC |

---

## 3. Ruang Lingkup Pekerjaan

### 3.1 Portal Publik (Tanpa Login)

| Fitur | Deskripsi |
|-------|-----------|
| Beranda | Statistik RTH, taman unggulan, ensiklopedia, profil kota |
| RTH Kota Batam | Rekapitulasi luasan, capaian RTRW, grafik kategori & wilayah |
| Daftar & Detail Taman | Pencarian, filter, galeri foto, profil lengkap |
| Peta Taman | Peta interaktif Leaflet/OpenStreetMap |
| WebAR / QR Scan | Profil AR per taman via QR code |
| Ensiklopedia | Artikel edukasi pertamanan + kuis interaktif |
| Aduan Masyarakat | Formulir aduan dengan foto & koordinat GPS |
| Survey Kepuasan | Penilaian 4 kategori (operasional, kondisi taman, respon aduan, portal) |

### 3.2 Back-Office Admin (`/admin`)

| Modul | Fungsi |
|-------|--------|
| Dashboard | KPI operasional, alert (bibit, aduan, jadwal terlambat), ekspor PDF |
| Data Taman | CRUD, import CSV, geocoder wilayah, galeri, QR AR, laporan RTH PDF |
| Data Bibit | Master bibit, stok masuk/keluar, laporan, alert stok minimum |
| Operasional Pertamanan | Pemeliharaan rutin, permohonan layanan, alat/armada, laporan operasional PDF |
| Evaluasi RAP | 8 sub-laporan + konsolidasi indeks kinerja |
| Masukan Masyarakat | Manajemen aduan & survey |
| Ensiklopedia CMS | Kategori & artikel (admin only) |
| Tim Pelaksana & Petugas | Wilayah kerja, roster petugas lapangan |
| Manajemen Pengguna | RBAC: admin, operator, viewer |
| Monitoring DPA | Tahun anggaran, paket pekerjaan, HPS/SPK, progres, dokumen |
| Activity Log | Audit trail aktivitas admin |

### 3.3 Input Lapangan (`/lapangan`)

| Fitur | Deskripsi |
|-------|-----------|
| Akses PIN | Petugas lapangan tanpa akun via PIN session (konfigurasi `.env`) |
| Pemeliharaan Rutin | Input per tim wilayah/nursery/armada dengan foto & petugas |
| Progres Permohonan | Update harian permohonan layanan + ekspor PDF progres |

### 3.4 Di Luar Ruang Lingkup (Fase Berikutnya)

- Aplikasi mobile native (Android/iOS)
- API publik terbuka untuk integrasi pihak ketiga
- Integrasi langsung dengan sistem keuangan/SIPD
- Single Sign-On (SSO) LDAP pemerintah

---

## 4. Keluaran (Deliverables)

| No | Keluaran | Format | Keterangan |
|----|----------|--------|------------|
| 1 | Aplikasi SIMTAMAN versi produksi | Web application | Deploy di VPS/server Disperakimtan |
| 2 | Basis data terstruktur | MySQL/MariaDB | Schema + seeder wilayah Batam |
| 3 | Dokumentasi KAK | PDF/DOCX | Dokumen ini |
| 4 | Dokumentasi Desain Basis Data | PDF/DOCX/Markdown | `docs/DESAIN-BASIS-DATA-SIMTAMAN.md` |
| 5 | Dokumentasi Arsitektur Sistem | PDF/DOCX/Markdown | `docs/ARSITEKTUR-SISTEM-SIMTAMAN.md` |
| 6 | Panduan environment & deploy | Markdown | `docs/SPESIFIKASI-TEKNIS-ENVIRONMENT.md`, `docs/deploy/` |
| 7 | Manual pengguna (admin & lapangan) | PDF | *(disusun terpisah)* |
| 8 | Source code & repositori Git | Repository | `sitaman-batam` |
| 9 | Test suite otomatis | PHPUnit | ≥ 100 test feature/unit |

---

## 5. Metodologi Pelaksanaan

### 5.1 Pendekatan Pengembangan

- **Iteratif-incremental** berbasis modul (portal → operasional → evaluasi → DPA)
- **Monolith Laravel** server-rendered untuk efisiensi deploy dan maintenance
- **Business logic terpusat** di `app/Support/` (Report Builders, Resolvers, Recorders)
- **Test-driven** untuk modul kritis (operasional, evaluasi, RBAC, lapangan)

### 5.2 Tahapan Pelaksanaan

```
Fase 1 — Fondasi          : Auth, RBAC, wilayah, taman, bibit
Fase 2 — Operasional      : Pemeliharaan, permohonan, armada, lapangan
Fase 3 — Publik & Masukan : Portal, aduan, survey, ensiklopedia, WebAR
Fase 4 — Evaluasi RAP     : 8 modul evaluasi + konsolidasi
Fase 5 — Penyempurnaan    : Petugas pelaksana, statistik RTH publik, DPA
Fase 6 — Deploy Produksi  : VPS, Nginx, SSL, backup, monitoring
```

### 5.3 Quality Assurance

- PHPUnit feature tests per modul
- Laravel Pint (code style)
- UAT bersama operator lapangan dan admin Disperakimtan
- Penetration testing dasar (RBAC, rate limiting, upload validation)

---

## 6. Spesifikasi Teknis

### 6.1 Stack Teknologi

| Komponen | Spesifikasi |
|----------|-------------|
| Bahasa | PHP 8.2+ |
| Framework | Laravel 12 |
| Database | SQLite (dev), MySQL/MariaDB 10.x+ (produksi) |
| Frontend | Blade, Tailwind CSS 4, Vite 7 |
| PDF | DomPDF |
| QR Code | simple-qrcode |
| Web Server | Nginx + PHP-FPM |
| Cache/Queue | Database driver (opsional Redis) |

### 6.2 Persyaratan Server Produksi

| Item | Minimum | Direkomendasikan |
|------|---------|------------------|
| CPU | 2 vCPU | 4 vCPU |
| RAM | 2 GB | 4 GB |
| Storage | 40 GB SSD | 80 GB SSD |
| OS | Ubuntu 22.04/24.04 LTS | Ubuntu 24.04 LTS |
| PHP | 8.2 + extensions: pdo_mysql, gd, mbstring, xml, curl, zip | 8.3 |
| SSL | Let's Encrypt / Cloudflare | WAF + CDN |

### 6.3 Keamanan

- Autentikasi session Laravel + bcrypt
- RBAC 3 peran (admin, operator, viewer)
- Scope wilayah kerja operator via pivot `tim_pelaksana_user`
- Rate limiting: login, aduan, survey, PIN lapangan
- Audit trail (`activity_logs`)
- Validasi upload file (tipe, ukuran)
- HTTPS wajib produksi; `trustProxies` untuk Cloudflare

### 6.4 Ketersediaan & Backup

- Health check endpoint: `GET /up`
- Cron: `schedule:run` setiap menit
- Backup database harian (mysqldump) + `storage/app/public`
- Retensi backup minimum 30 hari

---

## 7. Peran dan Tanggung Jawab

| Peran | Tanggung Jawab |
|-------|----------------|
| **Pejabat Pembuat Komitmen (PPK)** | Pengambilan keputusan, persetujuan deliverables |
| **Pengguna Barang/Jasa** | UAT, validasi kebutuhan fungsional |
| **Tim IT / Pengembang** | Pengembangan, testing, deploy, dokumentasi teknis |
| **Administrator SIMTAMAN** | Manajemen user, konfigurasi, monitoring sistem |
| **Operator / Tim Pelaksana** | Input data operasional, pemeliharaan data taman |
| **Petugas Lapangan** | Input via `/lapangan` (PIN atau akun operator) |
| **Masyarakat** | Aduan, survey, akses informasi publik |

---

## 8. Jadwal Pelaksanaan

*(Template — disesuaikan dengan kontrak/SPK)*

| Tahap | Kegiatan | Durasi | Output |
|-------|----------|--------|--------|
| 1 | Analisis kebutuhan & desain | 2 minggu | KAK, desain DB, arsitektur |
| 2 | Pengembangan modul inti | 8 minggu | Portal, admin, operasional |
| 3 | Evaluasi RAP & lapangan | 4 minggu | 8 evaluasi, input lapangan |
| 4 | UAT & perbaikan | 2 minggu | Bug fix, penyesuaian UX |
| 5 | Deploy produksi & serah terima | 2 minggu | Go-live, training, dokumentasi |

**Total estimasi:** 18 minggu

---

## 9. Persyaratan dan Ketentuan

1. Source code menjadi milik Disperakimtan Kota Batam
2. Dokumentasi teknis diserahkan lengkap saat serah terima
3. Pelaksana wajib memberikan garansi perbaikan bug **60 hari** pasca go-live
4. Data pribadi pelapor aduan dijaga sesuai peraturan perlindungan data
5. Sistem tidak boleh bergantung pada layanan berbayar wajib (self-hosted)
6. Deploy produksi menggunakan domain resmi `.go.id`
7. Integrasi CDN/peta eksternal memerlukan persetujuan kebijakan IT Pemkot

---

## 10. Lampiran

| Lampiran | File |
|----------|------|
| A — Desain Basis Data | `docs/DESAIN-BASIS-DATA-SIMTAMAN.md` |
| B — Arsitektur Sistem | `docs/ARSITEKTUR-SISTEM-SIMTAMAN.md` |
| C — Spesifikasi Environment | `docs/SPESIFIKASI-TEKNIS-ENVIRONMENT.md` |
| D — Panduan Deploy VPS | `docs/deploy/VPS-BATAMGARDEN.md` |
| E — Analisis RAP vs SIMTAMAN | `docs/ANALISIS-RAP-vs-SIMTAMAN.docx` |

---

*Dokumen ini disusun berdasarkan kondisi implementasi SIMTAMAN per September 2026.*
