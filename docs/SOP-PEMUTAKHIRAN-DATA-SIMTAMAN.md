# SOP Pemutakhiran Data Taman — SIMTAMAN

**Untuk:** petugas Admin / Administrator  
**Tujuan:** memperbarui profil taman di lapangan atau di kantor, sampai status data **Lengkap**.  
**Versi:** 1.0 · September 2026

---

## 1. Siapa yang mengerjakan

| Peran | Boleh memutakhirkan profil taman? | Cara kerja |
|--------|-----------------------------------|------------|
| **Admin** | Ya | Login → backpanel → **Data Taman → Kelola Taman → Edit** |
| **Administrator** | Ya | Sama, plus **Import CSV** jika perlu data massal |
| **Pengawas** | Tidak | Hanya **Input Lapangan** (pemeliharaan / permohonan), bukan edit profil taman |
| **Pimpinan** | Tidak | Hanya melihat laporan |

Profil taman (GPS, foto, fasilitas, kelurahan, data pembangunan) **hanya** diubah lewat **Kelola Taman → Edit**. Jangan pakai menu Input Lapangan untuk ini.

---

## 2. Persiapan

1. Pastikan akun sudah login dan masuk **dashboard admin** (bukan halaman Input Lapangan).
2. Siapkan di lokasi taman (atau dari foto/catatan lapangan):
   - nama taman yang benar
   - alamat
   - foto terkini (JPG / PNG / WebP)
   - catatan fasilitas & kondisinya (Baik / Rusak Ringan / Rusak Berat)
   - data pembangunan jika ada (tahun, nilai, kontraktor, konsultan)
3. Di HP/laptop: izinkan **akses lokasi (GPS)** di browser.
4. Utamakan taman berstatus **Belum Lengkap**. Di daftar Kelola Taman, kolom **Status Data** menampilkan badge hijau (Lengkap) atau kuning (Belum Lengkap).

---

## 3. Alur kerja (satu taman)

1. Buka **Data Taman → Kelola Taman**.
2. Cari nama taman, lalu klik **Edit**.
3. Isi **Identitas RTH / Taman**
   - Nama * dan Kategori * wajib.
   - Luasan (m²), alamat, deskripsi, kelurahan/kecamatan.
4. Isi **Koordinat & Tag Lokasi**
   - Berdiri di titik taman, klik **Tag lokasi saya (GPS)**.
   - Geser penanda jika perlu sampai akurat.
   - Kelurahan biasanya terisi otomatis dari GPS. Cek apakah sudah benar.
5. Unggah **Galeri Foto** (minimal 1 foto).
6. Isi **Data Pembangunan** (tahun, nilai, kontraktor, konsultan perencana).
7. Isi **Fasilitas & Kondisi** (minimal 1 baris). Tambah baris sesuai yang ada di lapangan.
8. Cek **Waktu Pemutakhiran**
   - Biarkan kosong jika data dicek **hari ini** (sistem mengisi waktu sekarang).
   - Isi tanggal/jam khusus jika verifikasi dilakukan pada waktu tertentu.
9. Klik **Simpan**.
10. Kembali ke daftar: pastikan taman muncul dan status data berubah sesuai kelengkapan.

Ulangi langkah 2–10 untuk taman berikutnya.

---

## 4. Syarat status **Lengkap**

Status **Lengkap** muncul otomatis jika **semua** isian berikut terisi. Jika salah satu kosong, status tetap **Belum Lengkap**.

| No | Yang harus terisi |
|----|-------------------|
| 1 | Nama taman |
| 2 | Kategori |
| 3 | Deskripsi |
| 4 | Alamat |
| 5 | Kelurahan / kecamatan |
| 6 | Latitude & longitude (bukan titik default peta) |
| 7 | Luasan > 0 |
| 8 | Minimal 1 foto galeri |
| 9 | Minimal 1 fasilitas & kondisi |
| 10 | Tahun pembangunan |
| 11 | Nilai pembangunan |
| 12 | Kontraktor |
| 13 | Konsultan perencana |

Kategori yang valid: Taman Kota, Taman Lingkungan, Jalur Hijau Jalan, TPU, Kebun Raya.

---

## 5. Cek hasil

Setelah menyimpan:

1. Buka kembali taman tersebut (Detail / Edit) — data dan foto harus tampil.
2. Di daftar Kelola Taman, badge status harus **Lengkap** (hijau) jika semua syarat terpenuhi.
3. Opsional: menu **Evaluasi → Kelengkapan Data** untuk melihat taman yang masih kosong atau data yang sudah lama tidak diverifikasi.
4. Opsional: cek portal publik (daftar taman / peta) apakah nama, lokasi, dan foto sudah sesuai.

---

## 6. Jika ada gangguan

| Gejala | Yang dilakukan |
|--------|----------------|
| Masuk ke Input Lapangan, bukan Kelola Taman | Akun kemungkinan **Pengawas**. Minta Administrator mengganti peran menjadi **Admin**. |
| GPS tidak jalan / kelurahan kosong | Izinkan lokasi di browser; pastikan internet HP/laptop aktif; isi kelurahan manual jika perlu. |
| Foto gagal diunggah | Cek format JPG/PNG/WebP dan koneksi internet. |
| Logout / error 419 saat simpan | Login ulang, lalu simpan lagi. Jangan buka banyak tab edit sekaligus. |
| Status tetap Belum Lengkap | Cek 13 isian di bagian 4 — biasanya yang kosong: foto, luasan, pembangunan, atau fasilitas. |
| Taman tidak terlihat di daftar | Admin hanya melihat wilayah timnya. Minta Administrator cek akses wilayah akun. |

---

## 7. Yang tidak boleh

- Jangan hapus taman yang sudah terdaftar kecuali atas instruksi Administrator.
- Jangan hapus semua foto sebelum foto baru berhasil tersimpan.
- Jangan menandai koordinat dari kantor jika belum dicek di lokasi (titik akan salah di peta publik).
- Jangan memakai **Import CSV** kecuali Administrator dan file sudah dicek. Foto tetap harus diunggah satu per satu setelah import.
- Input Lapangan **bukan** tempat mengubah profil taman.

---

## 8. Ringkas satu halaman

**Login Admin → Data Taman → Kelola Taman → Edit → isi identitas + GPS + foto + pembangunan + fasilitas → Simpan → cek status Lengkap.**
