<?php

declare(strict_types=1);

/**
 * SOP singkat pemutakhiran data taman SIMTAMAN (Word).
 *
 * Usage: php scripts/generate-sop-pemutakhiran-docx.php
 */
require __DIR__.'/docx-builder.php';

$outPath = dirname(__DIR__).'/docs/SOP-PEMUTAKHIRAN-DATA-SIMTAMAN.docx';

$paragraphs = [
    ['style' => 'title', 'text' => 'SOP Pemutakhiran Data Taman'],
    ['style' => 'subtitle', 'text' => 'SIMTAMAN — Sistem Informasi Manajemen Pertamanan'],
    ['style' => 'meta', 'text' => 'Disperakimtan Kota Batam · Untuk petugas Admin / Administrator'],
    ['style' => 'meta', 'text' => 'Versi 1.0 · September 2026 · Dokumen operasional singkat'],
    ['style' => 'spacer'],

    ['style' => 'status', 'text' => 'Ringkas: Login Admin → Data Taman → Kelola Taman → Edit → isi identitas + GPS + foto + pembangunan + fasilitas → Simpan → cek status Lengkap.'],
    ['style' => 'spacer'],

    ['style' => 'heading1', 'text' => '1. Tujuan'],
    ['style' => 'body', 'text' => 'Memperbarui profil taman (identitas, lokasi GPS, foto, fasilitas, dan data pembangunan) agar akurat di sistem dan portal publik, sampai status data menjadi Lengkap.'],

    ['style' => 'heading1', 'text' => '2. Siapa yang mengerjakan'],
    ['style' => 'table_header', 'text' => "Peran\tBoleh edit profil taman?\tCara kerja"],
    ['style' => 'table_row', 'text' => "Admin\tYa\tLogin → backpanel → Data Taman → Kelola Taman → Edit"],
    ['style' => 'table_row', 'text' => "Administrator\tYa\tSama, plus Import CSV jika perlu data massal"],
    ['style' => 'table_row', 'text' => "Pengawas\tTidak\tHanya Input Lapangan (pemeliharaan / permohonan)"],
    ['style' => 'table_row', 'text' => "Pimpinan\tTidak\tHanya melihat laporan"],
    ['style' => 'note', 'text' => 'Profil taman (GPS, foto, fasilitas, kelurahan, data pembangunan) hanya diubah lewat Kelola Taman → Edit. Jangan pakai menu Input Lapangan untuk ini.'],

    ['style' => 'heading1', 'text' => '3. Persiapan'],
    ['style' => 'numbered', 'text' => 'Pastikan akun sudah login dan masuk dashboard admin (bukan halaman Input Lapangan).'],
    ['style' => 'numbered', 'text' => 'Siapkan nama taman, alamat, foto terkini (JPG/PNG/WebP), catatan fasilitas & kondisi, serta data pembangunan jika ada.'],
    ['style' => 'numbered', 'text' => 'Izinkan akses lokasi (GPS) di browser HP atau laptop.'],
    ['style' => 'numbered', 'text' => 'Utamakan taman berstatus Belum Lengkap (badge kuning di kolom Status Data). Badge hijau berarti sudah Lengkap.'],

    ['style' => 'heading1', 'text' => '4. Alur kerja (satu taman)'],
    ['style' => 'numbered', 'text' => 'Buka Data Taman → Kelola Taman.'],
    ['style' => 'numbered', 'text' => 'Cari nama taman, lalu klik Edit.'],
    ['style' => 'numbered', 'text' => 'Isi Identitas RTH / Taman: Nama dan Kategori wajib; lengkapi luasan (m²), alamat, deskripsi, dan kelurahan/kecamatan.'],
    ['style' => 'numbered', 'text' => 'Isi Koordinat & Tag Lokasi: berdiri di titik taman, klik Tag lokasi saya (GPS), geser penanda jika perlu. Cek kelurahan yang terisi otomatis.'],
    ['style' => 'numbered', 'text' => 'Unggah Galeri Foto (minimal 1 foto).'],
    ['style' => 'numbered', 'text' => 'Isi Data Pembangunan: tahun, nilai, kontraktor, dan konsultan perencana.'],
    ['style' => 'numbered', 'text' => 'Isi Fasilitas & Kondisi (minimal 1 baris). Kondisi: Baik, Rusak Ringan, atau Rusak Berat.'],
    ['style' => 'numbered', 'text' => 'Cek Waktu Pemutakhiran. Kosongkan jika dicek hari ini (sistem mengisi waktu sekarang). Isi tanggal/jam khusus jika verifikasi pada waktu tertentu.'],
    ['style' => 'numbered', 'text' => 'Klik Simpan, lalu cek daftar taman: status data harus sesuai kelengkapan.'],
    ['style' => 'body', 'text' => 'Ulangi langkah 2–9 untuk taman berikutnya.'],

    ['style' => 'heading1', 'text' => '5. Syarat status Lengkap'],
    ['style' => 'body', 'text' => 'Status Lengkap muncul otomatis jika semua isian berikut terisi. Jika salah satu kosong, status tetap Belum Lengkap.'],
    ['style' => 'table_header', 'text' => "No\tIsian yang harus terisi"],
    ['style' => 'table_row', 'text' => "1\tNama taman"],
    ['style' => 'table_row', 'text' => "2\tKategori"],
    ['style' => 'table_row', 'text' => "3\tDeskripsi"],
    ['style' => 'table_row', 'text' => "4\tAlamat"],
    ['style' => 'table_row', 'text' => "5\tKelurahan / kecamatan"],
    ['style' => 'table_row', 'text' => "6\tLatitude & longitude (bukan titik default peta)"],
    ['style' => 'table_row', 'text' => "7\tLuasan > 0"],
    ['style' => 'table_row', 'text' => "8\tMinimal 1 foto galeri"],
    ['style' => 'table_row', 'text' => "9\tMinimal 1 fasilitas & kondisi"],
    ['style' => 'table_row', 'text' => "10\tTahun pembangunan"],
    ['style' => 'table_row', 'text' => "11\tNilai pembangunan"],
    ['style' => 'table_row', 'text' => "12\tKontraktor"],
    ['style' => 'table_row', 'text' => "13\tKonsultan perencana"],
    ['style' => 'note', 'text' => 'Kategori yang valid: Taman Kota, Taman Lingkungan, Jalur Hijau Jalan, TPU, Kebun Raya.'],

    ['style' => 'heading1', 'text' => '6. Cek hasil'],
    ['style' => 'numbered', 'text' => 'Buka kembali taman tersebut (Detail / Edit) — data dan foto harus tampil.'],
    ['style' => 'numbered', 'text' => 'Di daftar Kelola Taman, badge status harus Lengkap (hijau) jika semua syarat terpenuhi.'],
    ['style' => 'numbered', 'text' => 'Opsional: Evaluasi → Kelengkapan Data untuk melihat taman yang masih kosong atau sudah lama tidak diverifikasi.'],
    ['style' => 'numbered', 'text' => 'Opsional: cek portal publik (daftar taman / peta) apakah nama, lokasi, dan foto sudah sesuai.'],

    ['style' => 'heading1', 'text' => '7. Jika ada gangguan'],
    ['style' => 'table_header', 'text' => "Gejala\tYang dilakukan"],
    ['style' => 'table_row', 'text' => "Masuk ke Input Lapangan, bukan Kelola Taman\tAkun kemungkinan Pengawas. Minta Administrator mengganti peran menjadi Admin."],
    ['style' => 'table_row', 'text' => "GPS tidak jalan / kelurahan kosong\tIzinkan lokasi di browser; pastikan internet aktif; isi kelurahan manual jika perlu."],
    ['style' => 'table_row', 'text' => "Foto gagal diunggah\tCek format JPG/PNG/WebP dan koneksi internet."],
    ['style' => 'table_row', 'text' => "Logout / error 419 saat simpan\tLogin ulang, lalu simpan lagi. Jangan buka banyak tab edit sekaligus."],
    ['style' => 'table_row', 'text' => "Status tetap Belum Lengkap\tCek 13 isian di bagian 5 — biasanya yang kosong: foto, luasan, pembangunan, atau fasilitas."],
    ['style' => 'table_row', 'text' => "Taman tidak terlihat di daftar\tAdmin hanya melihat wilayah timnya. Minta Administrator cek akses wilayah akun."],

    ['style' => 'heading1', 'text' => '8. Yang tidak boleh'],
    ['style' => 'bullet', 'text' => 'Jangan hapus taman yang sudah terdaftar kecuali atas instruksi Administrator.'],
    ['style' => 'bullet', 'text' => 'Jangan hapus semua foto sebelum foto baru berhasil tersimpan.'],
    ['style' => 'bullet', 'text' => 'Jangan menandai koordinat dari kantor jika belum dicek di lokasi (titik akan salah di peta publik).'],
    ['style' => 'bullet', 'text' => 'Jangan memakai Import CSV kecuali Administrator dan file sudah dicek. Foto tetap harus diunggah satu per satu setelah import.'],
    ['style' => 'bullet', 'text' => 'Input Lapangan bukan tempat mengubah profil taman.'],

    ['style' => 'spacer'],
    ['style' => 'meta', 'text' => '— Dokumen dihasilkan otomatis: php scripts/generate-sop-pemutakhiran-docx.php —'],
    ['style' => 'meta', 'text' => 'Sumber teks: docs/SOP-PEMUTAKHIRAN-DATA-SIMTAMAN.md'],
];

writeDocx($outPath, $paragraphs);

echo "Created: {$outPath}\n";
