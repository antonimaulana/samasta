<?php

declare(strict_types=1);

/**
 * Generate KAK Aksi Perubahan Optimalisasi Data & Monitoring SIMTAMAN (.docx)
 *
 * Usage: php scripts/generate-kak-aksi-optimalisasi-docx.php
 */
require __DIR__.'/docx-builder.php';

$outPath = dirname(__DIR__).'/docs/KAK-AKSI-PERUBAHAN-OPTIMALISASI-DATA-MONITORING-SIMTAMAN.docx';

$paragraphs = [
    ['style' => 'title', 'text' => 'KERANGKA ACUAN KERJA (KAK)'],
    ['style' => 'subtitle', 'text' => 'Aksi Perubahan: Optimalisasi Pengelolaan Data dan Monitoring Operasional Ruang Terbuka Hijau (RTH) Taman melalui SIMTAMAN'],
    ['style' => 'meta', 'text' => 'Dinas Perumahan, Kawasan Permukiman dan Pertamanan Kota Batam'],
    ['style' => 'meta', 'text' => 'Versi 1.0 · September 2026'],
    ['style' => 'spacer'],

    ['style' => 'heading1', 'text' => '1. Informasi Umum'],
    ['style' => 'body', 'text' => 'Kegiatan ini memanfaatkan Sistem Informasi Manajemen Pertamanan (SIMTAMAN) untuk optimalisasi data profil taman/RTH dan monitoring operasional pertamanan (pemeliharaan rutin, permohonan layanan, input lapangan). Bukan pengembangan sistem dari nol, melainkan penataan proses, pemutakhiran data, pelatihan, dan operasionalisasi produksi.'],
    ['style' => 'heading2', 'text' => 'Lokasi'],
    ['style' => 'bullet', 'text' => 'Kantor Disperakimtan Kota Batam — koordinasi, pelatihan, validasi.'],
    ['style' => 'bullet', 'text' => 'Lapangan seluruh wilayah tim pelaksana — verifikasi GPS, foto, input operasional.'],
    ['style' => 'bullet', 'text' => 'Server produksi — deploy, backup, ketersediaan layanan.'],

    ['style' => 'heading1', 'text' => '2. Latar Belakang dan Permasalahan'],
    ['style' => 'body', 'text' => 'SIMTAMAN telah tersedia (portal publik, admin, input lapangan). Permasalahan operasional: profil taman belum seragam lengkap; input lapangan terhambat validasi; data masih dual (spreadsheet vs sistem); monitoring evaluasi belum rutin; infrastruktur backup/deploy perlu disiplin; peran pengguna belum sosialisasi merata.'],

    ['style' => 'heading1', 'text' => '3. Maksud, Tujuan, dan Sasaran'],
    ['style' => 'numbered', 'text' => 'Profil taman lengkap, terverifikasi, dan dapat diakses publik.'],
    ['style' => 'numbered', 'text' => '100% kegiatan operasional terdokumentasi (foto, tim, petugas).'],
    ['style' => 'numbered', 'text' => 'Siklus export–lengkapi–import CSV dan SOP pemutakhiran.'],
    ['style' => 'numbered', 'text' => 'Monitoring dashboard dan evaluasi operasional berkala.'],
    ['style' => 'numbered', 'text' => 'Keamanan, backup, dan ketersediaan produksi.'],

    ['style' => 'heading1', 'text' => '4. Ruang Lingkup Pekerjaan'],
    ['style' => 'heading2', 'text' => 'Paket A — Optimalisasi Data Profil Taman'],
    ['style' => 'bullet', 'text' => 'Inventarisasi baseline status Lengkap/Belum Lengkap.'],
    ['style' => 'bullet', 'text' => 'Pemutakhiran 13 kriteria kelengkapan (GPS, galeri, fasilitas, data pembangunan).'],
    ['style' => 'bullet', 'text' => 'Export CSV data existing → lengkapi kolom kosong → import (update by id/nama).'],
    ['style' => 'bullet', 'text' => 'Validasi wilayah Batam dan quality control sampling.'],
    ['style' => 'heading2', 'text' => 'Paket B — Monitoring Operasional'],
    ['style' => 'bullet', 'text' => 'Pemeliharaan rutin per tim (6 foto, petugas, lokasi).'],
    ['style' => 'bullet', 'text' => 'Progres permohonan layanan harian + PDF.'],
    ['style' => 'bullet', 'text' => 'Input Lapangan (/lapangan) untuk Pengawas/tim.'],
    ['style' => 'bullet', 'text' => 'Dashboard, evaluasi operasional, laporan PDF.'],
    ['style' => 'heading2', 'text' => 'Paket C — Tata Kelola & Infrastruktur'],
    ['style' => 'bullet', 'text' => 'Deploy produksi, storage:link, backup harian, restore uji.'],
    ['style' => 'bullet', 'text' => 'SOP, pelatihan Administrator/Admin/Pengawas, UAT, laporan akhir.'],

    ['style' => 'heading1', 'text' => '5. Metodologi'],
    ['style' => 'body', 'text' => 'PDCA (Plan–Do–Check–Act): baseline & export → pemutakhiran lapangan → monitoring → perbaikan & SOP. Koordinasi: kick-off, progres mingguan, helpdesk, UAT terstruktur.'],

    ['style' => 'heading1', 'text' => '6. Jadwal (12 minggu — template)'],
    ['style' => 'table_header', 'text' => "Minggu\tKegiatan"],
    ['style' => 'table_row', 'text' => "1\tPersiapan, deploy, backup, pelatihan admin"],
    ['style' => 'table_row', 'text' => "2–5\tPemutakhiran data taman (batch + import CSV)"],
    ['style' => 'table_row', 'text' => "6–9\tOperasional penuh input lapangan & monitoring"],
    ['style' => 'table_row', 'text' => "10\tEvaluasi operasional & laporan PDF"],
    ['style' => 'table_row', 'text' => "11–12\tUAT, laporan akhir, serah terima SOP"],

    ['style' => 'heading1', 'text' => '7. Keluaran'],
    ['style' => 'numbered', 'text' => 'Database taman & operasional terupdate + arsip CSV.'],
    ['style' => 'numbered', 'text' => 'Laporan kelengkapan dan operasional periode.'],
    ['style' => 'numbered', 'text' => 'SIMTAMAN produksi stabil (backup aktif).'],
    ['style' => 'numbered', 'text' => 'SOP pemutakhiran, BA pelatihan, BA UAT, laporan akhir.'],

    ['style' => 'heading1', 'text' => '8. Indikator Kinerja (contoh target)'],
    ['style' => 'table_header', 'text' => "Indikator\tTarget"],
    ['style' => 'table_row', 'text' => "Taman status Lengkap\t≥ 80%"],
    ['style' => 'table_row', 'text' => "Taman dengan GPS & kelurahan valid\t≥ 90%"],
    ['style' => 'table_row', 'text' => "Taman dengan galeri foto\t≥ 85%"],
    ['style' => 'table_row', 'text' => "Foto operasional lengkap (6 foto)\t≥ 90% record baru"],
    ['style' => 'table_row', 'text' => "Backup penuh sukses\t100% hari kerja"],
    ['style' => 'note', 'text' => 'Target disesuaikan baseline minggu ke-1 oleh PPK.'],

    ['style' => 'heading1', 'text' => '9. Spesifikasi Teknis Singkat'],
    ['style' => 'bullet', 'text' => 'PHP 8.2+, Laravel 12, MySQL/MariaDB, Nginx, PHP-FPM.'],
    ['style' => 'bullet', 'text' => 'Upload ≥ 20 MB; symlink public/storage; HTTPS direkomendasikan untuk GPS.'],
    ['style' => 'bullet', 'text' => 'RBAC: Administrator, Admin, Pengawas, Viewer.'],

    ['style' => 'heading1', 'text' => '10. Ketentuan'],
    ['style' => 'numbered', 'text' => 'Backup wajib sebelum import massal atau deploy major.'],
    ['style' => 'numbered', 'text' => 'Import CSV hanya Administrator setelah validasi sample.'],
    ['style' => 'numbered', 'text' => 'Garansi dukungan teknis 60 hari pasca penutupan kegiatan.'],
    ['style' => 'status', 'text' => 'Versi lengkap: docs/KAK-AKSI-PERUBAHAN-OPTIMALISASI-DATA-MONITORING-SIMTAMAN.md'],

    ['style' => 'spacer'],
    ['style' => 'table_header', 'text' => "Disusun\tDiperiksa\tDisetujui"],
    ['style' => 'table_row', 'text' => "Tim Pelaksana\tPPK\tPejabat Pengawas"],
    ['style' => 'table_row', 'text' => "Tanggal: ______\tTanggal: ______\tTanggal: ______"],
];

writeDocx($outPath, $paragraphs);

echo "Written: {$outPath}\n";
