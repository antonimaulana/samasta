<?php

declare(strict_types=1);

/**
 * Generate combined Word (.docx) for KAK, Desain Basis Data, and Arsitektur SIMTAMAN.
 *
 * Usage: php scripts/generate-kak-documentation-docx.php
 */
require __DIR__.'/docx-builder.php';

$outPath = dirname(__DIR__).'/docs/KAK-DESAIN-DB-ARSITEKTUR-SIMTAMAN.docx';

$paragraphs = [
    ['style' => 'title', 'text' => 'Dokumentasi SIMTAMAN'],
    ['style' => 'subtitle', 'text' => 'Kerangka Acuan Kerja · Desain Basis Data · Arsitektur Sistem'],
    ['style' => 'meta', 'text' => 'Sistem Informasi Manajemen Pertamanan — Disperakimtan Kota Batam'],
    ['style' => 'meta', 'text' => 'Versi 1.0 · September 2026 · Laravel 12 / PHP 8.2+'],
    ['style' => 'spacer'],

    ['style' => 'heading1', 'text' => 'BAGIAN A — KERANGKA ACUAN KERJA (KAK)'],
    ['style' => 'heading2', 'text' => 'A.1 Latar Belakang'],
    ['style' => 'body', 'text' => 'Disperakimtan Kota Batam bertanggung jawab atas pengelolaan Ruang Terbuka Hijau (RTH) meliputi pembangunan, pemeliharaan, pelayanan permohonan masyarakat, pengelolaan bibit, dan pelaporan kinerja. SIMTAMAN dikembangkan sebagai platform web terintegrasi untuk mendigitalisasi seluruh siklus tersebut.'],
    ['style' => 'heading2', 'text' => 'A.2 Tujuan'],
    ['style' => 'bullet', 'text' => 'Basis data RTH lengkap, akurat, dan dapat diakses publik.'],
    ['style' => 'bullet', 'text' => 'Dokumentasi operasional pemeliharaan dan permohonan layanan secara real-time.'],
    ['style' => 'bullet', 'text' => 'Laporan evaluasi RAP (8 modul + konsolidasi) per periode.'],
    ['style' => 'bullet', 'text' => 'Partisipasi masyarakat melalui aduan dan survey kepuasan.'],
    ['style' => 'bullet', 'text' => 'Input lapangan efisien via portal /lapangan (PIN atau akun operator).'],
    ['style' => 'heading2', 'text' => 'A.3 Ruang Lingkup'],
    ['style' => 'table_header', 'text' => "Kanal\tModul Utama"],
    ['style' => 'table_row', 'text' => "Portal Publik\tBeranda, RTH Kota Batam, taman/peta, WebAR, ensiklopedia, aduan, survey"],
    ['style' => 'table_row', 'text' => "Admin Back-Office\tDashboard, taman, bibit, operasional, evaluasi RAP, masukan, tim/petugas, DPA, users"],
    ['style' => 'table_row', 'text' => "Input Lapangan\tPemeliharaan rutin, progres permohonan, PDF progres"],
    ['style' => 'heading2', 'text' => 'A.4 Keluaran'],
    ['style' => 'numbered', 'text' => 'Aplikasi web SIMTAMAN versi produksi (HTTPS, domain .go.id).'],
    ['style' => 'numbered', 'text' => 'Basis data MySQL terstruktur + seeder wilayah Batam.'],
    ['style' => 'numbered', 'text' => 'Dokumentasi teknis (KAK, DB, arsitektur, deploy).'],
    ['style' => 'numbered', 'text' => 'Source code repository + test suite PHPUnit.'],
    ['style' => 'heading2', 'text' => 'A.5 Spesifikasi Teknis Singkat'],
    ['style' => 'bullet', 'text' => 'Stack: PHP 8.2+, Laravel 12, Blade, Tailwind 4, Vite 7, DomPDF.'],
    ['style' => 'bullet', 'text' => 'Server: Nginx + PHP-FPM, Ubuntu LTS, minimum 2 vCPU / 2 GB RAM.'],
    ['style' => 'bullet', 'text' => 'Keamanan: RBAC (admin/operator/viewer), audit trail, rate limiting, HTTPS.'],

    ['style' => 'heading1', 'text' => 'BAGIAN B — DESAIN BASIS DATA'],
    ['style' => 'heading2', 'text' => 'B.1 Prinsip'],
    ['style' => 'body', 'text' => 'Basis data dirancang relasional normalisasi (3NF), dengan Eloquent ORM Laravel. Konvensi penamaan snake_case plural. Primary key BIGINT id. Foreign key {model}_id. Produksi: MySQL/MariaDB; development: SQLite.'],
    ['style' => 'heading2', 'text' => 'B.2 Domain Data'],
    ['style' => 'table_header', 'text' => "Domain\tTabel Utama\tFungsi"],
    ['style' => 'table_row', 'text' => "Wilayah & Organisasi\tkecamatans, kelurahans, tim_pelaksanas, petugas, users\tReferensi wilayah Batam, tim pelaksana, roster petugas, RBAC"],
    ['style' => 'table_row', 'text' => "Master RTH\ttamans, taman_images, rth_kategoris\tProfil taman, galeri, kategori RTH publik"],
    ['style' => 'table_row', 'text' => "Operasional\tpemeliharaan_tamans, pemangkasans, pemangkasan_progres, alat_sarana_operasionals\tPemeliharaan rutin, permohonan layanan, progres harian, inventaris armada"],
    ['style' => 'table_row', 'text' => "Petugas (pivot)\tpemeliharaan_taman_petugas, pemangkasan_progres_petugas\tAssignment petugas lapangan per kegiatan"],
    ['style' => 'table_row', 'text' => "Bibit\tbibits, bibit_masuks, bibit_keluars\tStok pembibitan masuk/keluar"],
    ['style' => 'table_row', 'text' => "Masyarakat\taduan_masyarakats, survey_kepuasans\tAduan publik dan survey kepuasan"],
    ['style' => 'table_row', 'text' => "DPA\tdpa_tahun_anggarans, dpas, dpa_paket_pekerjaans, ...\tMonitoring anggaran pengadaan"],
    ['style' => 'table_row', 'text' => "Konten & Audit\tensiklopedia_*, activity_logs, notifications\tCMS edukasi, audit trail, notifikasi"],
    ['style' => 'heading2', 'text' => 'B.3 Relasi Kunci'],
    ['style' => 'bullet', 'text' => 'kelurahan → tim_pelaksana (N:1 via kelurahan_tim_pelaksana, unique per kelurahan).'],
    ['style' => 'bullet', 'text' => 'taman → kelurahan (N:1); pemeliharaan → taman (N:1, nullable untuk lokasi manual).'],
    ['style' => 'bullet', 'text' => 'pemeliharaan ↔ petugas (N:M); pemangkasan → progres (1:N); progres ↔ petugas (N:M).'],
    ['style' => 'bullet', 'text' => 'user ↔ tim_pelaksana (N:M) untuk scope operator wilayah kerja.'],
    ['style' => 'heading2', 'text' => 'B.4 Aturan Integritas Bisnis'],
    ['style' => 'bullet', 'text' => 'Operator hanya mengakses data di wilayah tim pelaksana yang ditugaskan.'],
    ['style' => 'bullet', 'text' => 'Petugas yang dipilih harus berasal dari tim pelaksana kegiatan (PetugasAssignment).'],
    ['style' => 'bullet', 'text' => 'Skor kelengkapan profil taman dihitung dari 13 field (TamanCompleteness).'],
    ['style' => 'bullet', 'text' => 'Indikator RTH terpelihara berdasarkan pemeliharaan rutin dalam window 30–90 hari.'],

    ['style' => 'heading1', 'text' => 'BAGIAN C — ARSITEKTUR SISTEM'],
    ['style' => 'heading2', 'text' => 'C.1 Pola Arsitektur'],
    ['style' => 'body', 'text' => 'SIMTAMAN menggunakan arsitektur monolith server-rendered (Laravel SSR). Satu codebase, satu deployment unit. Business logic terpusat di app/Support/ (Report Builders, Resolvers, Recorders). Presentation layer: Blade templates + Tailwind CSS.'],
    ['style' => 'heading2', 'text' => 'C.2 Layer Aplikasi'],
    ['style' => 'table_header', 'text' => "Layer\tKomponen\tLokasi"],
    ['style' => 'table_row', 'text' => "Presentation\tBlade views, Tailwind, JS\tresources/views/, public/js/"],
    ['style' => 'table_row', 'text' => "Application\tControllers, Middleware, Policies\tapp/Http/"],
    ['style' => 'table_row', 'text' => "Domain/Business\tSupport classes, Report Builders\tapp/Support/"],
    ['style' => 'table_row', 'text' => "Data Access\tEloquent Models, Migrations\tapp/Models/, database/"],
    ['style' => 'heading2', 'text' => 'C.3 Routing & Middleware'],
    ['style' => 'bullet', 'text' => 'public.php — portal tanpa login (/taman, /rth-kota-batam, /aduan, /survey).'],
    ['style' => 'bullet', 'text' => 'lapangan.php — /lapangan/* dengan middleware lapangan.access (PIN atau canWrite).'],
    ['style' => 'bullet', 'text' => 'admin.php — /admin/* dengan auth + admin.access + admin.viewer.scope + admin.audit.'],
    ['style' => 'heading2', 'text' => 'C.4 Deployment'],
    ['style' => 'body', 'text' => 'Production: Nginx (document root public/) → PHP-FPM 8.2 → Laravel. Cron schedule:run setiap menit. Queue worker opsional via Supervisor. SSL via Let\'s Encrypt atau Cloudflare. Backup harian database + storage/app/public.'],
    ['style' => 'heading2', 'text' => 'C.5 Integrasi Eksternal'],
    ['style' => 'bullet', 'text' => 'OpenStreetMap — tile peta taman.'],
    ['style' => 'bullet', 'text' => 'Nominatim — reverse geocode koordinat ke kelurahan (opsional).'],
    ['style' => 'bullet', 'text' => 'SMTP — digest operasional email harian (opsional).'],
    ['style' => 'heading2', 'text' => 'C.6 Evolusi'],
    ['style' => 'bullet', 'text' => 'Jangka pendek: Redis cache, CDN static assets.'],
    ['style' => 'bullet', 'text' => 'Jangka menengah: REST API /api/v1 untuk aplikasi mobile.'],
    ['style' => 'bullet', 'text' => 'Jangka panjang: object storage untuk foto operasional skala besar.'],

    ['style' => 'spacer'],
    ['style' => 'note', 'text' => 'Dokumen lengkap tersedia dalam format Markdown di folder docs/: KAK-SIMTAMAN.md, DESAIN-BASIS-DATA-SIMTAMAN.md, ARSITEKTUR-SISTEM-SIMTAMAN.md. Generate PDF arsitektur: php artisan docs:architecture-pdf'],
    ['style' => 'meta', 'text' => '— Dokumen dihasilkan otomatis dari kode sumber SIMTAMAN —'],
];

writeDocx($outPath, $paragraphs);

echo "Created: {$outPath}\n";
