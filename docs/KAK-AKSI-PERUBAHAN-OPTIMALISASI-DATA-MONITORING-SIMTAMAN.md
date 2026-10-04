# KERANGKA ACUAN KERJA (KAK)

## Aksi Perubahan: Optimalisasi Pengelolaan Data dan Monitoring Operasional Ruang Terbuka Hijau (RTH) Taman melalui Sistem Informasi Manajemen Pertamanan (SIMTAMAN)

| | |
|---|---|
| **Satuan Kerja** | Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam (Disperakimtan Kota Batam) |
| **Nama Sistem** | SIMTAMAN — *Sistem Informasi Manajemen Pertamanan* |
| **Jenis Kegiatan** | Aksi Perubahan / Optimalisasi Pemanfaatan Sistem Informasi |
| **Versi Dokumen** | 1.0 |
| **Tanggal** | September 2026 |

---

## DAFTAR ISI

1. [Informasi Umum Kegiatan](#1-informasi-umum-kegiatan)
2. [Latar Belakang dan Permasalahan](#2-latar-belakang-dan-permasalahan)
3. [Maksud, Tujuan, dan Sasaran](#3-maksud-tujuan-dan-sasaran)
4. [Ruang Lingkup Pekerjaan](#4-ruang-lingkup-pekerjaan)
5. [Metodologi Pelaksanaan](#5-metodologi-pelaksanaan)
6. [Jadwal dan Tahapan](#6-jadwal-dan-tahapan)
7. [Keluaran (Output)](#7-keluaran-output)
8. [Indikator Kinerja dan Target Capaian](#8-indikator-kinerja-dan-target-capaian)
9. [Peran dan Tanggung Jawab](#9-peran-dan-tanggung-jawab)
10. [Spesifikasi Teknis dan Infrastruktur](#10-spesifikasi-teknis-dan-infrastruktur)
11. [Persyaratan dan Ketentuan](#11-persyaratan-dan-ketentuan)
12. [Lampiran](#12-lampiran)

---

## 1. Informasi Umum Kegiatan

### 1.1 Nama Kegiatan

**Optimalisasi Pengelolaan Data dan Monitoring Operasional Ruang Terbuka Hijau (RTH) Taman melalui Sistem Informasi Manajemen Pertamanan (SIMTAMAN) pada Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam.**

### 1.2 Uraian Singkat

Kegiatan ini bertujuan **memanfaatkan, menstandarkan, dan mengoperasionalkan** SIMTAMAN yang telah tersedia guna:

- meningkatkan **kelengkapan dan mutu data profil taman/RTH**;
- mendokumentasikan **kegiatan operasional pertamanan** (pemeliharaan rutin dan layanan permohonan) secara terstruktur dan dapat diaudit;
- menyediakan **monitoring dan evaluasi operasional** berbasis data untuk pengambilan keputusan di Disperakimtan;
- memperkuat **akses input lapangan** oleh tim pelaksana dan pengawas;
- menjamin **keamanan, ketersediaan, dan cadangan data** pada lingkungan produksi.

Kegiatan ini **bukan** pengembangan sistem dari nol, melainkan **optimalisasi pemanfaatan**, penyesuaian proses bisnis, pemutakhiran data, pelatihan, serta penyiapan tata kelola operasional berkelanjutan.

### 1.3 Lokasi Pelaksanaan

| Lokasi | Kegiatan |
|--------|----------|
| Kantor Disperakimtan Kota Batam | Koordinasi, pelatihan admin, validasi data, monitoring dashboard |
| Lapangan (seluruh kecamatan/kelurahan sesuai tim pelaksana) | Verifikasi GPS, foto, fasilitas, input operasional |
| Server produksi (VPS/infrastruktur Disperakimtan) | Deploy, backup, pemeliharaan aplikasi |

### 1.4 Dasar Hukum (contoh acuan)

- Peraturan Daerah / Peraturan Walikota terkait RTH dan pertamanan Kota Batam;
- Rencana Strategis (Renstra) Disperakimtan Kota Batam;
- Rencana Aksi Perubahan (RAP) satuan kerja terkait digitalisasi/penataan data RTH;
- Standar pengelolaan data dan tata kelola TI pemerintah daerah (sesuai kebijakan Pemkot Batam).

*(Daftar peraturan spesifik dilengkapi PPK sesuai dokumen induk anggaran.)*

---

## 2. Latar Belakang dan Permasalahan

### 2.1 Latar Belakang

Kota Batam memiliki kewajiban pengelolaan RTH yang luas dan tersebar. Disperakimtan melaksanakan fungsi perencanaan, pembangunan, pemeliharaan, pelayanan masyarakat, pengelolaan bibit, serta pelaporan kinerja. SIMTAMAN telah dikembangkan sebagai platform web terintegrasi dengan kanal **Portal Publik**, **Back-Office Admin**, dan **Input Lapangan**.

Pada tahap operasional, tantangan utama bergeser dari *“apakah sistem ada”* menjadi *“apakah data dan operasional benar-benar terkelola dan dimonitor”*.

### 2.2 Permasalahan yang Diatasi

| No | Permasalahan | Dampak |
|----|--------------|--------|
| 1 | Profil taman/RTH belum seragam lengkap (GPS, foto, fasilitas, data pembangunan) | Portal publik dan laporan RTH tidak representatif; status *Belum Lengkap* dominan |
| 2 | Input operasional masih terhambat di lapangan (validasi form, foto wajib, pemilihan taman/petugas) | Kegiatan pemeliharaan/permohonan tidak tercatat atau terlambat tercatat |
| 3 | Data tersebar antara spreadsheet dan sistem | Duplikasi, inkonsistensi, sulit rekonsiliasi |
| 4 | Monitoring operasional belum rutin memanfaatkan modul evaluasi & dashboard | Pengambilan keputusan masih manual |
| 5 | Infrastruktur produksi (symlink storage, backup, cache deploy) belum disiplin | Risiko kehilangan foto/data saat update |
| 6 | Peran pengguna (Administrator, Admin/Operator, Pengawas, Viewer) belum sosialisasi merata | Salah modul (mis. input lapangan vs edit profil taman) |

---

## 3. Maksud, Tujuan, dan Sasaran

### 3.1 Maksud

Meningkatkan efektivitas pengelolaan data RTH taman dan monitoring operasional pertamanan melalui optimalisasi SIMTAMAN sebagai single source of truth yang dapat dipertanggungjawabkan.

### 3.2 Tujuan

1. Terwujudnya **profil taman** yang lengkap, terverifikasi, dan dapat diakses publik.
2. Terdokumentasinya **100% kegiatan operasional** (pemeliharaan rutin dan progres permohonan) dengan bukti foto dan personil.
3. Tersedianya **siklus pemutakhiran data** (export → lengkapi → import) dan SOP operasional.
4. Terlaksananya **monitoring berkala** melalui dashboard admin dan modul evaluasi operasional.
5. Terjaminnya **keamanan, backup, dan ketersediaan** layanan produksi.

### 3.3 Sasaran (Outcome)

| Sasaran | Uraian |
|---------|--------|
| Sasaran 1 | Peningkatan persentase taman berstatus data **Lengkap** |
| Sasaran 2 | Peningkatan frekuensi dan kelengkapan input operasional per tim pelaksana |
| Sasaran 3 | Pengurangan waktu penyusunan laporan operasional/evaluasi |
| Sasaran 4 | Peningkatan transparansi informasi RTH kepada masyarakat |

---

## 4. Ruang Lingkup Pekerjaan

### 4.1 Paket A — Optimalisasi Data Profil Taman (Master RTH)

| No | Pekerjaan | Detil |
|----|-----------|-------|
| A.1 | Inventarisasi baseline | Rekap jumlah taman, status *Lengkap* / *Belum Lengkap*, per kategori dan tim wilayah |
| A.2 | Standar kelengkapan data | Penerapan 13 kriteria kelengkapan (`TamanCompleteness`): nama, kategori, deskripsi, alamat, kelurahan, koordinat, luasan, galeri, fasilitas, tahun/nilai/kontraktor/konsultan |
| A.3 | Pemutakhiran lapangan & kantor | Edit profil via **Kelola Taman**; GPS/tag lokasi; unggah galeri; fasilitas & kondisi |
| A.4 | Siklus CSV massal | **Export CSV** data existing → lengkapi kolom kosong di Excel → **Import CSV** (update by `id` / `nama_taman`) |
| A.5 | Validasi wilayah | Kesesuaian kelurahan/kecamatan dengan master wilayah Batam; geocoder dari koordinat bila diperlukan |
| A.6 | Quality control | Sampling audit 10% record; koreksi duplikasi nama; penandaan `data_verified_at` |

**Di luar ruang lingkup paket ini:** penggantian seluruh database legacy tanpa backup; pengumpulan foto tanpa unggah ke sistem.

### 4.2 Paket B — Optimalisasi Monitoring Operasional Pertamanan

| No | Pekerjaan | Detil |
|----|-----------|-------|
| B.1 | Pemeliharaan rutin | Input per tim (Wilayah 1–4, Nursery, Armada): tanggal, lokasi/taman, petugas, 6 foto dokumentasi, uraian |
| B.2 | Permohonan layanan | Progres harian permohonan (pemangkasan, pohon tumbang, mini garden, dll.): status, foto, petugas, PDF progres |
| B.3 | Input Lapangan | Portal `/lapangan` (akun Pengawas/Operator atau PIN sesi); UX validasi form; panduan 4 langkah |
| B.4 | Dashboard & alert | Pemanfaatan dashboard admin: taman belum lengkap, alert operasional, filter status |
| B.5 | Evaluasi operasional | Modul evaluasi aktif (mis. pemeliharaan per taman, kinerja tim, RTH terpelihara, armada) — **sesuai konfigurasi fitur produksi** |
| B.6 | Laporan PDF | Ekspor laporan operasional pemeliharaan, laporan RTH, dashboard PDF |

**Catatan konfigurasi:** Modul **RAP Konsolidasi** dan **Monitoring DPA** dapat diaktifkan/nonaktifkan via feature flag (`SIMTAMAN_FEATURE_*`); ruang lingkup aksi perubahan ini **prioritas** modul data taman + operasional inti.

### 4.3 Paket C — Tata Kelola, Infrastruktur, dan Keberlanjutan

| No | Pekerjaan | Detil |
|----|-----------|-------|
| C.1 | Deploy produksi | Sinkronisasi kode (Git), `vps-deploy-update.sh`, migrate, cache, `storage:link` |
| C.2 | Backup & recovery | Backup penuh harian + snapshot sebelum deploy/pemutakhiran massal; uji restore |
| C.3 | SOP & pelatihan | SOP pemutakhiran data; panduan go-live; pelatihan Administrator, Admin, Pengawas |
| C.4 | Keamanan akses | RBAC: Administrator, Admin (operator), Pengawas (input lapangan), Viewer; audit trail |
| C.5 | HTTPS & domain | Penyiapan domain resmi `.go.id` / sertifikat SSL (rencana); mitigasi GPS di HTTP |
| C.6 | Dokumentasi | KAK aksi perubahan, SOP, checklist UAT, berita acara serah terima operasional |

### 4.4 Di Luar Ruang Lingkup

- Pengembangan aplikasi mobile native (Android/iOS);
- Integrasi langsung SIPD/keuangan daerah;
- Pengadaan perangkat keras baru (kecuali disediakan terpisah oleh SKPD);
- Pengisian konten ensiklopedia massal non-prioritas (opsional fase lanjut).

---

## 5. Metodologi Pelaksanaan

### 5.1 Pendekatan

Metode **Plan–Do–Check–Act (PDCA)** per paket pekerjaan:

1. **Plan:** baseline data, pembagian tim, jadwal turun lapangan, template CSV export.
2. **Do:** pemutakhiran profil taman, input operasional, pelatihan harian.
3. **Check:** dashboard kelengkapan, sampling audit, UAT bersama Pengguna Barang/Jasa.
4. **Act:** perbaikan sistem/proses, update SOP, lock konfigurasi produksi.

### 5.2 Mekanisme Koordinasi

- **Rapat kick-off** (Pejabat Pengawas Kegiatan, PPK, Pengguna Barang/Jasa, tim IT, koordinator tim pelaksana).
- **Rapat progres mingguan** dengan laporan capaian indikator.
- **Helpdesk** selama masa pemutakhiran (channel WA/grup internal + tiket sederhana).
- **UAT terstruktur** dengan checklist modul (admin, lapangan, export/import CSV).

### 5.3 Alur Bisnis Utama (Ringkas)

```
[Baseline Export CSV] → [Lengkapi di Excel/Lapangan] → [Import CSV / Edit Form]
        ↓
[Status Lengkap ↑] → [Portal Publik akurat] → [Input Operasional rutin]
        ↓
[Dashboard & Evaluasi] → [Laporan PDF] → [RAP / Renja evidence]
```

---

## 6. Jadwal dan Tahapan

*(Template 12 minggu — disesuaikan kalender kerja Disperakimtan.)*

| Minggu | Tahap | Kegiatan Utama | Paket |
|--------|-------|----------------|-------|
| 1 | Persiapan | Kick-off, baseline, backup penuh, deploy versi stabil, pelatihan administrator | C |
| 2–3 | Data taman | Export CSV seluruh taman; pemutakhiran batch 1 (prioritas Belum Lengkap) | A |
| 4–5 | Data taman | Pemutakhiran batch 2; verifikasi GPS & galeri; import CSV | A |
| 6–7 | Operasional | Sosialisasi Input Lapangan; uji coba pemeliharaan rutin & progres permohonan | B |
| 8–9 | Operasional | Operasional penuh semua tim; monitoring dashboard harian | B |
| 10 | Evaluasi | Rekap evaluasi operasional; laporan PDF periode | B |
| 11 | QA & UAT | Perbaikan bug/UX; uji restore backup; preflight go-live | C |
| 12 | Penutup | Laporan akhir, serah terima SOP, berita acara | C |

---

## 7. Keluaran (Output)

| No | Keluaran | Bentuk | Keterangan |
|----|----------|--------|------------|
| 1 | Data profil taman terupdate | Database produksi + CSV arsip | Baseline & versi akhir export |
| 2 | Rekap status kelengkapan | Laporan PDF/Excel | Per kategori, kecamatan, tim |
| 3 | Rekap operasional periode | Database + PDF | Pemeliharaan & permohonan |
| 4 | SIMTAMAN versi operasional | Aplikasi web produksi | HTTPS (target), backup aktif |
| 5 | SOP Pemutakhiran Data | MD/DOCX | `docs/SOP-PEMUTAKHIRAN-DATA-SIMTAMAN.md` |
| 6 | Panduan Input Lapangan | MD/PDF | Alur form, foto wajib, PIN |
| 7 | Berita Acara Pelatihan | PDF | Minimal 2 sesi (admin + lapangan) |
| 8 | Berita Acara UAT | PDF | Checklist lulus/tidak lulus per modul |
| 9 | Laporan Akhir Kegiatan | PDF | Capaian IKU, kendala, rekomendasi |
| 10 | Dokumentasi KAK Aksi Perubahan | MD/DOCX | Dokumen ini |

---

## 8. Indikator Kinerja dan Target Capaian

| No | Indikator | Cara Ukur | Target (contoh) | Sumber Data SIMTAMAN |
|----|-----------|-----------|-----------------|----------------------|
| 1 | Persentase taman status **Lengkap** | (jumlah lengkap / total taman) × 100% | ≥ 80% at end line | `tamans.status_data`, Kelola Taman |
| 2 | Taman dengan koordinat terverifikasi | Jumlah taman dengan GPS bukan default & kelurahan terisi | ≥ 90% total taman | Profil taman, `kelurahan_id` |
| 3 | Taman dengan ≥ 1 foto galeri | Count galeri/`foto` legacy | ≥ 85% total taman | `taman_images`, `foto` |
| 4 | Kegiatan pemeliharaan tercatat | Jumlah record `pemeliharaan_tamans` per bulan per tim | 100% tim submit ≥ 1/minggu (sesuai Rencana Kerja) | Modul operasional |
| 5 | Permohonan aktif dengan progres | % permohonan status Diproses yang memiliki progres dalam jadwal | ≥ 95% | `pemangkasans`, `pemangkasan_progres` |
| 6 | Kelengkapan bukti foto operasional | % record dengan 6 foto lengkap | ≥ 90% record baru | Field foto pemeliharaan/progres |
| 7 | Ketersediaan sistem | Uptime `/up` + tidak maintenance unplanned | ≥ 99% bulan operasi | Monitoring server |
| 8 | Backup sukses | Jumlah backup `--full` sukses / rencana | 100% hari kerja | `simtaman:backup` log |
| 9 | Petugas terlatih | Jumlah peserta pelatihan / total petugas target | ≥ 80% petugas input lapangan | Daftar hadir BA |
| 10 | Waktu input lapangan | Rata-rata menit per kegiatan (sampling) | ≤ 5 menit | Observasi lapangan |

*Target angka disesuaikan PPK dengan baseline minggu ke-1.*

---

## 9. Peran dan Tanggung Jawab

| Peran | Tanggung Jawab dalam Kegiatan Ini |
|-------|-----------------------------------|
| **Pejabat Pembuat Komitmen (PPK)** | Persetujuan rencana, monitoring anggaran, persetujuan laporan akhir |
| **Pejabat Pengawas Kegiatan** | Pengawasan mutu, kesesuaian ruang lingkup, verifikasi keluaran |
| **Pengguna Barang/Jasa (Kepala Bidang/SKPD)** | Penetapan prioritas taman, validasi data, keputusan operasional |
| **Administrator SIMTAMAN** | User management, import CSV, konfigurasi `.env`, backup, koordinasi IT |
| **Admin / Operator** | Edit profil taman, input operasional back-office, koreksi data |
| **Pengawas / Tim Pelaksana** | Input lapangan pemeliharaan & progres permohonan, foto dokumentasi |
| **Pelaksana Teknis / IT** | Deploy, perbaikan bug, dukungan teknis, dokumentasi |
| **Masyarakat (publik)** | Aduan & survey (pembenaran data tidak langsung via aduan tanpa verifikasi SKPD) |

---

## 10. Spesifikasi Teknis dan Infrastruktur

### 10.1 Aplikasi SIMTAMAN (acuan implementasi)

| Komponen | Spesifikasi |
|----------|-------------|
| Backend | PHP 8.2+, Laravel 12 |
| Basis data produksi | MySQL/MariaDB 10.x+ |
| Frontend | Blade, Tailwind CSS 4, Vite |
| PDF | DomPDF |
| Peta | Leaflet / OpenStreetMap |

### 10.2 Modul Prioritas Aksi Perubahan

- **Kelola Taman:** CRUD, export/import CSV, kelengkapan data, galeri, QR/WebAR.
- **Operasional Pertamanan:** pemeliharaan rutin, permohonan, armada (Tim Armada), laporan PDF.
- **Input Lapangan:** pemeliharaan per tim, progres permohonan, validasi client-side/server.
- **Evaluasi:** pemeliharaan, kinerja tim, RTH terpelihara (modul aktif di produksi).
- **Portal publik:** daftar/detail taman, peta, RTH Kota Batam.

### 10.3 Infrastruktur Produksi (minimum)

| Item | Spesifikasi |
|------|-------------|
| Server | VPS/cloud ≥ 2 vCPU, 4 GB RAM, 80 GB SSD |
| Web server | Nginx + PHP-FPM |
| Storage upload | `storage/app/public` + symlink `public/storage` |
| Upload limit | ≥ 20 MB (PHP & Nginx) untuk foto operasional/galeri |
| Backup | Database + `storage/app/public` harian, retensi ≥ 30 hari |
| Akses admin | Session, `SESSION_LIFETIME` memadai untuk kerja lapangan |

### 10.4 Keamanan

- RBAC dan scope wilayah operator;
- Rate limiting login, aduan, PIN lapangan;
- Audit trail (`activity_logs`);
- `APP_DEBUG=false` di produksi;
- HTTPS direkomendasikan untuk geolocation GPS di browser.

---

## 11. Persyaratan dan Ketentuan

1. Data dan source code SIMTAMAN menjadi asset Disperakimtan Kota Batam sesuai kontrak/kesepakatan pengembangan awal.
2. Pelaksana wajib menjaga **kerahasiaan data** operasional dan aduan masyarakat.
3. Setiap pemutakhiran massal wajib **backup penuh** terlebih dahulu.
4. Import CSV hanya oleh **Administrator** setelah validasi sample.
5. Perubahan konfigurasi produksi (`.env`, feature flag) didokumentasikan.
6. Masa **garansi dukungan teknis** pasca penutupan kegiatan: **60 hari** (bug critical/high).
7. Laporan akhir diserahkan paling lambat **14 hari kerja** setelah minggu operasional penuh.
8. Bukti foto operasional disimpan di server Disperakimtan, bukan hanya perangkat pribadi petugas.

---

## 12. Lampiran

| Kode | Judul | Lokasi / Keterangan |
|------|-------|---------------------|
| A | KAK Pengembangan SIMTAMAN (induk) | `docs/KAK-SIMTAMAN.md` |
| B | SOP Pemutakhiran Data Taman | `docs/SOP-PEMUTAKHIRAN-DATA-SIMTAMAN.md` |
| C | Desain Basis Data | `docs/DESAIN-BASIS-DATA-SIMTAMAN.md` |
| D | Arsitektur Sistem | `docs/ARSITEKTUR-SISTEM-SIMTAMAN.md` |
| E | Panduan Go-Live / VPS | `docs/PANDUAN-GO-LIVE-SIMTAMAN.docx`, `docs/deploy/` |
| F | Checklist Kesiapan Pemutakhiran | `docs/KEGIATAN-PEMUTAKHIRAN-TAMAN.md` |
| G | Template Import/Export CSV | Menu Admin → Import CSV → Template / Export Data Taman |
| H | Checklist UAT Optimalisasi | *(disusun saat minggu 11 — lampiran terpisah)* |
| I | Flowchart rancangan sistem (1 halaman) | `docs/LAMPIRAN-KAK-FLOWCHART-SIMTAMAN-1-HALAMAN.md` / `.docx` via `php scripts/generate-lampiran-flowchart-kak-docx.php` |
| J | Flowchart lengkap (11 diagram) | `docs/FLOWCHART-RANCANGAN-SISTEM-SIMTAMAN.md` |
| I | Flowchart rancangan sistem (1 halaman) | `docs/LAMPIRAN-KAK-FLOWCHART-SIMTAMAN-1-HALAMAN.md` / `.docx` via `php scripts/generate-lampiran-flowchart-kak-docx.php` |
| J | Flowchart lengkap (11 diagram) | `docs/FLOWCHART-RANCANGAN-SISTEM-SIMTAMAN.md` |

---

## Penutup

Dokumen Kerangka Acuan Kerja ini menjadi acuan pelaksanaan **aksi perubahan optimalisasi pengelolaan data dan monitoring operasional RTH taman melalui SIMTAMAN** pada Disperakimtan Kota Batam. Perubahan teknis minor (perbaikan bug, penyesuaian UX) diperbolehkan selama tidak mengurangi ruang lingkup dan indikator capaian yang disepakati PPK.

| Disusun oleh | Diperiksa oleh | Disetujui oleh |
|--------------|----------------|----------------|
| Tim Pelaksana Teknis | Pejabat Pengawas Kegiatan | Pejabat Pembuat Komitmen |
| (_________________) | (_________________) | (_________________) |
| Tanggal: __________ | Tanggal: __________ | Tanggal: __________ |

---

*Dokumen ini disusun berdasarkan kondisi implementasi SIMTAMAN per September 2026, termasuk fitur export/import CSV taman, Input Lapangan, kelengkapan data 13 field, dan tata kelola backup/deploy produksi.*
