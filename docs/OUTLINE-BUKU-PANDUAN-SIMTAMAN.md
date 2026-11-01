# Outline Buku Panduan SIMTAMAN

**Dokumen:** Draft outline per bab (siap diolah menjadi Buku Panduan resmi)  
**Sistem:** SIMTAMAN — Sistem Informasi Manajemen Pertamanan  
**Organisasi:** Dinas Perumahan, Kawasan Permukiman, dan Pertamanan Kota Batam · Bidang Pertamanan dan Pemakaman  
**Versi outline:** 0.1 · Oktober 2026  

**Cara pakai outline ini:** Setiap bab di bawah memuat subjudul wajib, isi yang harus ditulis, dan placeholder `[Screenshot: …]`. Isi teks prosedur disusun tim; screenshot diambil dari lingkungan **production/staging** dengan akun uji per peran.

**Legenda peran:** **ADM** Administrator · **OP** Admin/Operator · **PWG** Pengawas · **PIM** Pimpinan (viewer) · **PUB** Publik (tanpa login) · **LAP** Input lapangan (PIN/login pengawas)

---

## Daftar isi buku (target)

| Kode | Judul bab |
|------|-----------|
| A1–A4 | Umum |
| B1–B6 | Portal publik |
| C1–C3 | Dashboard & monitoring |
| D1–D4 | Data induk |
| E1–E5 | Operasional pertamanan |
| F1–F2 | Masukan masyarakat |
| G1–G3 | Evaluasi & pelaporan |
| H1–H5 | Sistem (administrator) |
| I1–I4 | Input lapangan |
| L1–L4 | Lampiran |

---

# BAGIAN A — UMUM

## A1. Pengenalan SIMTAMAN

**Tujuan bab:** Reader memahami fungsi sistem, manfaat, dan tiga kanal akses.

**Peran:** Semua

### A1.1 Latar belakang singkat
- Tantangan pengelolaan RTH Kota Batam sebelum sistem terintegrasi
- Peran SIMTAMAN dalam Disperakimtan / Bidang Pertamanan

### A1.2 Tujuan operasional sistem
- Basis data RTH, dokumentasi operasional, evaluasi, partisipasi masyarakat
- Tabel indikator keberhasilan (ringkas, rujuk KAK)

### A1.3 Tiga kanal akses
| Kanal | URL contoh | Pengguna |
|-------|------------|----------|
| Portal publik | `/`, `/taman`, `/rth-kota-batam` | Masyarakat |
| Back-office | `/admin` | ADM, OP, PIM (terbatas) |
| Input lapangan | `/lapangan` | PWG, petugas (PIN) |

- `[Screenshot: diagram tiga kanal — bisa dari flowchart KAK]`

### A1.4 Kontak & dukungan
- Helpdesk / admin data Bidang Pertamanan
- Jam layanan, eskalasi jika sistem tidak bisa diakses

---

## A2. Persyaratan perangkat & browser

**Tujuan bab:** Mengurangi kegagalan input (foto, GPS, upload).

**Peran:** OP, PWG, LAP

### A2.1 Perangkat lunak
- Browser yang disarankan (Chrome/Edge versi terbaru)
- Resolusi layar minimum untuk back-office

### A2.2 Perangkat keras & izin
- Kamera (foto kegiatan & aduan)
- GPS/lokasi (profil taman, aduan, tag lokasi)
- `[Screenshot: contoh prompt izin lokasi di browser mobile]`

### A2.3 Ukuran & format file
- Foto: JPG/PNG/WebP, batas ukuran (sesuai kebijakan server)
- Catatan: jika upload gagal → Lampiran L4

### A2.4 Koneksi internet
- Input lapangan di area sinyal lemah: simpan draft / retry (jelaskan perilaku aplikasi jika ada)

---

## A3. Akun, login, dan logout

**Tujuan bab:** Akses aman ke back-office dan lapangan.

**Peran:** ADM, OP, PIM, PWG

### A3.1 Jenis akun
- Tabel peran: Administrator, Admin, Pengawas, Pimpinan
- Perbedaan **login back-office** vs **Input Lapangan**

### A3.2 Login back-office (`/login`)
1. Buka halaman login portal admin  
2. Isi email & password  
3. Klik Masuk  
4. Redirect: Pengawas → Input Lapangan; lainnya → Dashboard  

- `[Screenshot: halaman login]`  
- `[Screenshot: dashboard setelah login Admin]`  

### A3.3 Logout
- Tombol Keluar di header admin  
- `[Screenshot: header admin — Keluar]`

### A3.4 Akun baru & reset password
- Prosedur permohonan ke Administrator (bukan self-service)
- Larangan berbagi password

### A3.5 Session Input Lapangan (PIN)
- Rujuk Bab I1 (ringkas saja di sini)

---

## A4. Glosarium & singkatan

**Tujuan bab:** Istilah seragam di seluruh buku dan SOP.

### A4.1 Istilah operasional
- Pemeliharaan rutin, permohonan layanan, tim wilayah, Tim Nursery, Tim Armada  
- Status permohonan: Rencana, Diproses, Selesai  
- Jadwal: hari ini, besok (H-1), sedang diproses, terlambat  

### A4.2 Istilah data
- RTH, luasan, status data taman (Lengkap / Belum Lengkap)  
- Stok bibit, varietas, siap tanam  

### A4.3 Istilah pelaporan
- Evaluasi, RAP (jika dipakai), DPA (jika modul aktif)  
- Catatan operasional / alert dashboard  

### A4.4 Daftar singkatan
- Disperakimtan, SIMTAMAN, RTRW, RTH, PDF, GPS, dll.

---

# BAGIAN B — PORTAL PUBLIK

## B1. Beranda & statistik umum

**Peran:** PUB, OP (sosialisasi)

### B1.1 Akses beranda
- URL, navigasi menu utama  
- `[Screenshot: beranda — hero & statistik]`

### B1.2 Statistik yang ditampilkan
- Makna angka (jumlah taman, luasan, dll.) — selaras data admin  
- `[Screenshot: blok statistik beranda]`

### B1.3 Tautan cepat
- Ke taman, RTH, ensiklopedia, masukan  

---

## B2. Statistik RTH Kota Batam

**Peran:** PUB, PIM, OP

### B2.1 Tujuan halaman
- Transparansi capaian RTH / RTRW  

### B2.2 Navigasi & filter
- Tahun, kategori, wilayah (jika ada)  
- `[Screenshot: halaman RTH Kota Batam — header]`

### B2.3 Membaca tabel / grafik
- Penjelasan indikator per baris (A–G atau sesuai tampilan UI)  
- Catatan metodologi (footer halaman)  
- `[Screenshot: tabel rekap tahunan]`  
- `[Screenshot: grafik kategori/wilayah jika ada]`

### B2.4 Hubungan dengan data admin
- Data bersumber SIMTAMAN; pemutakhiran via Bab D1  

---

## B3. Daftar taman, peta, dan detail taman

**Peran:** PUB

### B3.1 Daftar taman (`/taman`)
- Pencarian, filter  
- `[Screenshot: daftar taman]`

### B3.2 Peta taman
- Zoom, klik marker  
- `[Screenshot: peta taman]`

### B3.3 Detail profil taman
- Foto, fasilitas, luasan, lokasi  
- `[Screenshot: halaman detail taman publik]`

### B3.4 QR / WebAR (jika aktif)
- Scan QR di lokasi → profil AR  
- `[Screenshot: halaman AR scan]`  

---

## B4. Ensiklopedia & kuis

**Peran:** PUB

### B4.1 Daftar artikel & kategori  
### B4.2 Membaca artikel  
### B4.3 Kuis interaktif (aturan skor, tidak perlu login)  
- `[Screenshot: ensiklopedia index]`  
- `[Screenshot: artikel & kuis]`  

---

## B5. Aduan masyarakat (portal)

**Peran:** PUB

### B5.1 Mengisi formulir aduan
- Lokasi, foto, uraian  
- `[Screenshot: form aduan]`  

### B5.2 Konfirmasi terkirim  
- `[Screenshot: halaman sukses]`  

### B5.3 Cek status aduan
- Nomor aduan / data yang diminta  
- `[Screenshot: form cek status]`  

---

## B6. Survey kepuasan (portal)

**Peran:** PUB

### B6.1 Kapan survey ditampilkan / diakses  
### B6.2 Kategori penilaian (4 aspek)  
### B6.3 Submit & privasi  
- `[Screenshot: form survey]`  

---

# BAGIAN C — DASHBOARD & MONITORING

## C1. Dashboard operasional

**Peran:** ADM, OP, PIM

### C1.1 Akses menu
- **Menu → Dashboard** atau `/admin`  
- `[Screenshot: sidebar — Dashboard]`  

### C1.2 Hero & status operasional
- Badge “Operasional Baik” / “Perlu Perhatian”  
- Tanggal & waktu snapshot  
- `[Screenshot: hero dashboard]`  

### C1.3 Kartu data induk (klik untuk drill-down)
| Kartu | Menu tujuan | Peran yang bisa klik |
|-------|-------------|----------------------|
| Data Taman | Kelola taman | ADM, OP, PIM* |
| Data Bibit | Kelola bibit | ADM, OP, PIM* |
| Data Armada | Armada | ADM, OP, PIM* |
| Data Petugas | Tim kerja | ADM, OP; PIM* |

\*PIM: hanya route yang diizinkan; lihat Lampiran matriks.

- `[Screenshot: 4 kartu data induk]`  

### C1.4 Kepuasan & masukan masyarakat
- `[Screenshot: 2 kartu kepuasan & masukan]`  

### C1.5 Operasional Pertamanan
- **Pemeliharaan rutin** — tile per tim, hari ini  
- **Operasional permohonan** — Hari ini, Besok, Diproses, Terlambat  
- `[Screenshot: section operasional pertamanan]`  

### C1.6 Catatan operasional
- Indikator alert (bibit, aduan, jadwal, dll.)  
- Klik baris → modul terkait  
- `[Screenshot: catatan operasional]`  

### C1.7 Mode read-only (Pimpinan)
- Perilaku sama tampilan; tanpa tombol ubah/hapus di modul downstream  
- Catatan footer “Klik kartu untuk melihat detail…”  

---

## C2. Unduh ringkasan PDF dashboard

**Peran:** ADM, OP, PIM (jika policy mengizinkan export)

### C2.1 Tombol Unduh Ringkasan  
### C2.2 Isi PDF vs layar dashboard  
### C2.3 Frekuensi penggunaan (rapat pimpinan)  
- `[Screenshot: tombol Unduh Ringkasan di hero]`  
- `[Screenshot: contoh 1 halaman PDF — opsional]`  

---

## C3. Panduan khusus Pimpinan

**Peran:** PIM

### C3.1 Tujuan penggunaan dashboard pimpinan  
### C3.2 Drill-down yang diizinkan (evaluasi, taman, permohonan, dll.)  
### C3.3 Yang tidak dapat diakses (redirect ke dashboard)  
### C3.4 Ritme monitoring (harian/mingguan) — rujuk SOP evaluasi  

---

# BAGIAN D — DATA INDUK

## D1. Data Taman

**Peran:** ADM, OP (PWG **tidak** edit profil — tegaskan)

> **Integrasi:** Gabungkan isi `docs/SOP-PEMUTAKHIRAN-DATA-SIMTAMAN.md` ke sub-bab D1.3–D1.6.

### D1.1 Menu & struktur
- Data Taman → Kelola Taman / Laporan Taman  
- `[Screenshot: submenu Data Taman]`  

### D1.2 Daftar taman
- Kolom penting: nama, kelurahan, status data, luasan  
- Filter & pencarian  
- `[Screenshot: index kelola taman]`  

### D1.3 Tambah taman baru
- Field wajib  
- `[Screenshot: form create — identitas]`  

### D1.4 Edit profil taman (langkah lengkap)
1. Identitas & kategori  
2. Koordinat GPS & tag peta  
3. Galeri foto  
4. Data pembangunan  
5. Fasilitas & kondisi  
6. Waktu pemutakhiran  
7. Simpan  

- `[Screenshot: edit — GPS]`  
- `[Screenshot: edit — galeri]`  
- `[Screenshot: edit — fasilitas]`  

### D1.5 Status Lengkap vs Belum Lengkap
- Tabel 13 syarat (dari SOP pemutakhiran)  

### D1.6 Import CSV (Administrator)
- Template, unggah, validasi error  
- `[Screenshot: halaman import]`  

### D1.7 Laporan taman & export PDF
- Filter laporan  
- `[Screenshot: laporan taman]`  

### D1.8 QR AR (opsional)
- Generate QR dari admin  
- `[Screenshot: QR di detail taman admin]`  

---

## D2. Data Bibit

**Peran:** ADM, OP

### D2.1 Master bibit (varietas, stok)  
### D2.2 Stok masuk  
### D2.3 Stok keluar  
### D2.4 Laporan bibit & export PDF  
### D2.5 Alert stok rendah (dashboard & evaluasi)  
- `[Screenshot: index bibit]`  
- `[Screenshot: form stok masuk/keluar]`  
- `[Screenshot: laporan bibit]`  

---

## D3. Armada

**Peran:** ADM, OP

### D3.1 Inventaris armada (jenis kendaraan/alat Tim Armada)  
### D3.2 Tambah / edit / nonaktifkan unit  
### D3.3 Detail & riwayat penggunaan (pemeliharaan + permohonan)  
- `[Screenshot: index armada]`  
- `[Screenshot: detail armada — riwayat]`  

---

## D4. Tim kerja & petugas

**Peran:** ADM (kelola), OP (baca/roster)

### D4.1 Daftar tim pelaksana (wilayah, nursery, armada)  
### D4.2 Petugas per tim — roster  
### D4.3 Kaitan tim dengan filter operasional & input lapangan  
- `[Screenshot: tim kerja index]`  
- `[Screenshot: form tim / petugas]`  

---

# BAGIAN E — OPERASIONAL PERTAMANAN

## E1. Pemeliharaan rutin (back-office)

**Peran:** ADM, OP

### E1.1 Menu Pemeliharaan Rutin  
### E1.2 Filter: tanggal, tim, taman, view (hari ini, dll.)  
### E1.3 Buat entri pemeliharaan
- Tim, taman/lokasi manual, uraian, foto  
- Multi-hari: hari ke / total hari / progres %  
- Petugas (picker)  
- `[Screenshot: form pemeliharaan]`  

### E1.4 Tim Armada — baris armada & sopir  
### E1.5 Edit & hapus (hak akses)  
### E1.6 Export PDF (rekap & per kegiatan)  
- `[Screenshot: index pemeliharaan dengan filter]`  
- `[Screenshot: PDF operasional contoh]`  

---

## E2. Pemeliharaan rutin (input lapangan)

**Peran:** PWG, LAP

- Rujuk detail Bab I2; di sini ringkas alur + link silang  
### E2.1 Pilih menu tim di Input Lapangan  
### E2.2 Form sama dengan admin (perbedaan UX mobile)  
- `[Screenshot: lapangan — form pemeliharaan]`  

---

## E3. Permohonan layanan

**Peran:** ADM, OP

### E3.1 Jenis layanan (pemangkasan, pohon tumbang, mini garden, dll.)  
### E3.2 Pendaftaran permohonan baru  
- Data pemohon, lokasi, jadwal rencana  
- `[Screenshot: form permohonan]`  

### E3.3 Daftar permohonan & filter `view`
- hari_ini, besok, diproses, terlambat  
- `[Screenshot: index permohonan dengan chip view]`  

### E3.4 Ubah status (Rencana → Diproses → Selesai)  
### E3.5 Jadwal multi-hari & deteksi bentrok (jika UI menampilkan)  
- `[Screenshot: detail permohonan]`  

---

## E4. Progres permohonan harian

**Peran:** ADM, OP, PWG (lapangan)

### E4.1 Menambah entri progres per hari  
### E4.2 Foto, petugas, armada (opsional)  
### E4.3 Edit progres  
### E4.4 Export PDF progres  
- `[Screenshot: form progres admin]`  
- `[Screenshot: progres PDF]`  

---

## E5. Laporan operasional pertamanan

**Peran:** ADM, OP, PIM

### E5.1 Menu Laporan Operasional  
### E5.2 Parameter filter periode  
### E5.3 Export PDF & arsip  
- `[Screenshot: laporan operasional index]`  

---

# BAGIAN F — MASUKAN MASYARAKAT

## F1. Aduan (back-office)

**Peran:** ADM, OP

### F1.1 Daftar aduan & filter status (Baru, Ditinjau, Diproses, Selesai)  
### F1.2 Detail aduan — foto & lokasi  
### F1.3 Update status & catatan tindak lanjut  
### F1.4 SLA internal (rujuk SOP-04)  
- `[Screenshot: index aduan]`  
- `[Screenshot: detail aduan admin]`  

---

## F2. Survey kepuasan (back-office)

**Peran:** ADM, OP, PIM

### F2.1 Daftar respons & rata-rata  
### F2.2 Filter periode  
### F2.3 Interpretasi skor untuk rapat  
- `[Screenshot: index survey admin]`  

---

# BAGIAN G — EVALUASI & PELAPORAN

## G1. Halaman Evaluasi (overview)

**Peran:** ADM, OP, PIM

### G1.1 Akses menu Evaluasi  
### G1.2 Daftar sub-modul evaluasi  
### G1.3 Kapan membuka modul mana  
- `[Screenshot: evaluasi index]`  

---

## G2. Sub-modul evaluasi (satuan bab ringkas atau sub-sub-bab)

**Peran:** ADM, OP, PIM

Untuk **masing-masing** modul di bawah, gunakan pola yang sama:
1. Tujuan laporan  
2. Filter periode / wilayah  
3. Membaca tabel & grafik  
4. Export PDF  
5. `[Screenshot: …]`  

| Kode | Modul |
|------|--------|
| G2.1 | Evaluasi pemeliharaan |
| G2.2 | Evaluasi armada |
| G2.3 | Evaluasi kinerja tim |
| G2.4 | Evaluasi operasional permohonan |
| G2.5 | Evaluasi RTH terpelihara |
| G2.6 | Evaluasi masukan masyarakat |
| G2.7 | Evaluasi kelengkapan data |
| G2.8 | RAP konsolidasi *(jika fitur aktif)* |

---

## G3. Siklus pelaporan evaluasi

**Peran:** Kasi/Kabid, ADM

### G3.1 Frekuensi (mingguan/bulanan/triwulan)  
### G3.2 Penyusun, reviewer, penandatangan  
### G3.3 Arsip PDF & nomor surat  
### G3.4 Hubungan data evaluasi dengan dashboard  

---

# BAGIAN H — SISTEM (ADMINISTRATOR)

## H1. Kelola pengguna

**Peran:** ADM

### H1.1 Daftar user  
### H1.2 Tambah user — pilih role  
### H1.3 Nonaktifkan / ganti role  
- `[Screenshot: users index]`  
- `[Screenshot: form user]`  

---

## H2. Log aktivitas

**Peran:** ADM

### H2.1 Tujuan audit trail  
### H2.2 Filter log  
- `[Screenshot: activity log]`  

---

## H3. Konten beranda & FAQ

**Peran:** ADM

### H3.1 Slide beranda (urutan, aktif/nonaktif)  
### H3.2 FAQ beranda  
- `[Screenshot: slides index]`  
- `[Screenshot: FAQ admin]`  

---

## H4. Ensiklopedia (CMS)

**Peran:** ADM

### H4.1 Kategori & artikel  
### H4.2 Pengaturan portal ensiklopedia  
- `[Screenshot: ensiklopedia CMS]`  

---

## H5. Notifikasi in-app

**Peran:** ADM, OP

### H5.1 Ikon notifikasi di header  
### H5.2 Tandai dibaca  
- `[Screenshot: dropdown notifikasi]`  

### H6. Monitoring DPA *(bab opsional — jika `admin.feature:dpa_monitoring` aktif)*
- Dashboard DPA, paket pekerjaan, dokumen, progres  

---

# BAGIAN I — INPUT LAPANGAN

## I1. Membuka sesi Input Lapangan

**Peran:** PWG, LAP

### I1.1 URL `/lapangan/buka`  
### I1.2 Login pengawas vs PIN operasional  
### I1.3 Kunci sesi (`/lapangan/kunci`)  
- `[Screenshot: halaman buka PIN]`  
- `[Screenshot: menu utama lapangan]`  

---

## I2. Menu operasional per tim

**Peran:** PWG, LAP

### I2.1 Daftar tile/menu (pemeliharaan per tim, permohonan)  
### I2.2 Pembatasan wilayah tim (operator vs pengawas)  
- `[Screenshot: index lapangan — daftar menu tim]`  

---

## I3. Tips lapangan

### I3.1 Foto: pencahayaan, jarak, multiple angle  
### I3.2 GPS: tunggu akurasi, refresh  
### I3.3 Entri petugas — chip & pencarian  
### I3.4 Armada Tim Armada di lapangan  

---

## I4. Troubleshooting lapangan (ringkas)

| Gejala | Langkah |
|--------|---------|
| PIN ditolak | Cek `.env` / minta admin |
| Upload gagal | Kompres foto, cek sinyal |
| Tim tidak muncul | Wilayah user / roster petugas |

→ Detail teknis: Lampiran L4  

---

# LAMPIRAN

## L1. Checklist harian koordinator tim
- [ ] Pemeliharaan hari ini tercatat per tim  
- [ ] Permohonan jadwal hari ini / terlambat ditindak  
- [ ] Aduan baru ditinjau  
- … *(disetujui Kasi)*  

## L2. Daftar jenis layanan permohonan
- Tabel: jenis · ikon · contoh lokasi · tim pelaksana tipikal  

## L3. Contoh dokumentasi baik vs kurang
- Foto buram / tanpa konteks vs foto jelas dengan landmark  
- Uraian pekerjaan spesifik vs generik  

## L4. Troubleshooting umum (user & IT)
- Login gagal, 500, Vite/CSS tidak load, migrate, upload limit PHP  
- Rujuk `docs/deploy/` untuk IT  

## L5. Matriks Menu × Peran *(wajib disahkan)*
- Spreadsheet: baris = menu admin; kolom = ADM / OP / PWG / PIM  
- Centang: Lihat / Tambah / Ubah / Hapus / Export PDF  

## L6. Indeks SOP terkait
| SOP | Judul |
|-----|--------|
| SOP-01 | Tata kelola data RTH |
| SOP-02 | Pemeliharaan rutin |
| SOP-03 | Layanan permohonan |
| SOP-04 | Masukan masyarakat |
| SOP-05 | Evaluasi & RAP |
| SOP-06 | Keamanan & akun |
| SOP-07 | Kelangsungan operasi IT |

---

# Jadwal produksi konten (usulan)

| Minggu | Bab | Penanggung jawab konten | Screenshot |
|--------|-----|-------------------------|------------|
| 1 | I1–I4, E2 | Pengawas lapangan | Mobile |
| 2 | E1, E3, E4 | Admin operasional | Desktop |
| 3 | D1 (+ merge SOP pemutakhiran) | Admin data taman | Desktop + GPS |
| 4 | C1–C3, G1–G2.1 | Kabid / staf evaluasi | Desktop |
| 5 | B1–B6, F1–F2 | Humas / front office | Desktop + mobile publik |
| 6 | D2–D4, H*, L5 | Administrator | Desktop |

---

# Daftar screenshot master (checklist produksi)

Gunakan penomoran file: `BP-{bab}-{urut}.png` (contoh: `BP-C1-01-hero.png`).

| ID | Deskripsi |
|----|-----------|
| BP-A3-01 | Login admin |
| BP-C1-01 | Hero dashboard |
| BP-C1-02 | Kartu data induk |
| BP-C1-03 | Operasional pertamanan tiles |
| BP-D1-01 | Index kelola taman |
| BP-D1-02 | Edit GPS |
| BP-E1-01 | Form pemeliharaan admin |
| BP-E3-01 | Index permohonan view terlambat |
| BP-I1-01 | Unlock PIN lapangan |
| BP-I2-01 | Menu tim lapangan |
| … | Lengkapi per bab saat penulisan |

---

*Outline ini belum disahkan. Revisi berikutnya: nomor halaman, daftar gambar, dan persetujuan pejabat.*
