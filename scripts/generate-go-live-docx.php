<?php

declare(strict_types=1);

/**
 * Panduan go-live / update VPS SIMTAMAN (Word).
 *
 * Usage: php scripts/generate-go-live-docx.php
 */
require __DIR__.'/docx-builder.php';

$outPath = dirname(__DIR__).'/docs/PANDUAN-GO-LIVE-SIMTAMAN.docx';

$paragraphs = [
    ['style' => 'title', 'text' => 'Panduan Go-Live SIMTAMAN'],
    ['style' => 'subtitle', 'text' => 'Deploy & Pemutakhiran Data Taman di VPS BatamGarden'],
    ['style' => 'meta', 'text' => 'Disperakimtan Kota Batam · Sistem Informasi Manajemen Pertamanan'],
    ['style' => 'meta', 'text' => 'September 2026 · Repo: github.com/antonimaulana/samasta (branch main)'],
    ['style' => 'spacer'],

    ['style' => 'status', 'text' => 'Ringkas: SSH ke VPS → cd /var/www/samasta → bash scripts/vps-deploy-update.sh → cek APP_URL di .env → buka situs di browser → login Admin.'],
    ['style' => 'spacer'],

    ['style' => 'heading1', 'text' => '1. Informasi server production'],
    ['style' => 'table_header', 'text' => "Item\tNilai"],
    ['style' => 'table_row', 'text' => "Provider\tBiznet Gio Cloud (BatamGarden)"],
    ['style' => 'table_row', 'text' => "IP publik\t103.150.92.153"],
    ['style' => 'table_row', 'text' => "User SSH\tGardenBatam"],
    ['style' => 'table_row', 'text' => "Key SSH\tGardenBatam2026.pem (key pair Biznet)"],
    ['style' => 'table_row', 'text' => "Path aplikasi\t/var/www/samasta"],
    ['style' => 'table_row', 'text' => "Repository\tgit@github.com:antonimaulana/samasta.git"],

    ['style' => 'heading1', 'text' => '2. Masuk ke VPS dari Windows (PowerShell)'],
    ['style' => 'body', 'text' => 'ssh -i "C:\\Users\\USER\\Downloads\\GardenBatam2026.pem" GardenBatam@103.150.92.153'],
    ['style' => 'note', 'text' => 'Sesuaikan path file .pem jika disimpan di folder lain. Username bukan ubuntu — harus GardenBatam. Hostname BatamGarden bukan DNS; gunakan IP atau reverse DNS Biznet.'],
    ['style' => 'body', 'text' => 'Jika error permission key (PowerShell):'],
    ['style' => 'body', 'text' => 'icacls "C:\\Users\\USER\\Downloads\\GardenBatam2026.pem" /inheritance:r'],
    ['style' => 'body', 'text' => 'icacls "C:\\Users\\USER\\Downloads\\GardenBatam2026.pem" /grant:r "%USERNAME%:(R)"'],

    ['style' => 'heading1', 'text' => '3. Update aplikasi (server sudah pernah live)'],
    ['style' => 'body', 'text' => 'Jalankan perintah berikut setelah SSH masuk ke server:'],
    ['style' => 'body', 'text' => 'cd /var/www/samasta'],
    ['style' => 'body', 'text' => 'git remote -v'],
    ['style' => 'body', 'text' => 'git fetch origin && git checkout main && git pull origin main'],
    ['style' => 'body', 'text' => 'bash scripts/vps-deploy-update.sh'],
    ['style' => 'note', 'text' => 'Script deploy otomatis: backup sebelum pull (simtaman:backup), composer install, npm build, migrate, config/route/view cache. Branch default: main.'],
    ['style' => 'body', 'text' => 'php artisan simtaman:preflight-pemutakhiran'],
    ['style' => 'body', 'text' => 'php artisan simtaman:backup --full --label=sebelum-pemutakhiran'],

    ['style' => 'heading2', 'text' => '3.1 Folder backup (jika backup gagal)'],
    ['style' => 'body', 'text' => 'sudo mkdir -p /var/backups/samasta'],
    ['style' => 'body', 'text' => 'sudo chown www-data:www-data /var/backups/samasta'],
    ['style' => 'body', 'text' => 'Tambahkan di .env: BACKUP_PATH=/var/backups/samasta'],
    ['style' => 'body', 'text' => 'php artisan config:clear && php artisan config:cache'],
    ['style' => 'body', 'text' => 'php artisan simtaman:backup --full --label=sebelum-pemutakhiran'],

    ['style' => 'heading1', 'text' => '4. File .env production (wajib dicek)'],
    ['style' => 'body', 'text' => 'nano /var/www/samasta/.env'],
    ['style' => 'table_header', 'text' => "Variabel\tNilai disarankan"],
    ['style' => 'table_row', 'text' => "APP_ENV\tproduction"],
    ['style' => 'table_row', 'text' => "APP_DEBUG\tfalse"],
    ['style' => 'table_row', 'text' => "APP_URL\tURL publik (domain Cloudflare atau https://IP)"],
    ['style' => 'table_row', 'text' => "DB_*\tMySQL production (jangan ganti tanpa backup)"],
    ['style' => 'table_row', 'text' => "SESSION_LIFETIME\t480 (opsional, sesi admin lebih lama)"],
    ['style' => 'table_row', 'text' => "BACKUP_PATH\t/var/backups/samasta"],
    ['style' => 'table_row', 'text' => "WILAYAH_GEOCODER_ENABLED\ttrue (isi kelurahan dari GPS)"],
    ['style' => 'body', 'text' => 'Setelah mengubah .env:'],
    ['style' => 'body', 'text' => 'cd /var/www/samasta && php artisan config:cache && php artisan route:cache && php artisan view:cache'],

    ['style' => 'heading1', 'text' => '5. Web server & verifikasi'],
    ['style' => 'body', 'text' => 'sudo systemctl status nginx'],
    ['style' => 'body', 'text' => 'sudo systemctl status php8.2-fpm   # atau php8.3-fpm sesuai server'],
    ['style' => 'body', 'text' => 'sudo nginx -t && sudo systemctl reload nginx'],
    ['style' => 'body', 'text' => 'cd /var/www/samasta && php artisan storage:link'],
    ['style' => 'body', 'text' => 'php artisan up   # keluar maintenance mode jika aktif'],
    ['style' => 'body', 'text' => 'curl -I http://127.0.0.1/up'],
    ['style' => 'body', 'text' => 'Dari browser: buka APP_URL → /login → akun Admin → harus masuk dashboard admin (backpanel), bukan Input Lapangan.'],

    ['style' => 'heading1', 'text' => '6. DNS Cloudflare (jika pakai domain)'],
    ['style' => 'bullet', 'text' => 'Record A → 103.150.92.153 (Proxied).'],
    ['style' => 'bullet', 'text' => 'APP_URL harus sama dengan domain HTTPS yang dibuka pengguna.'],
    ['style' => 'body', 'text' => 'Panduan lengkap: docs/PANDUAN-DEPLOY-BIZNET-CLOUDFLARE.pdf dan docs/deploy/VPS-BATAMGARDEN.md'],

    ['style' => 'heading1', 'text' => '7. Cron (scheduler & backup harian)'],
    ['style' => 'body', 'text' => 'crontab -e'],
    ['style' => 'body', 'text' => '* * * * * cd /var/www/samasta && php artisan schedule:run >> /dev/null 2>&1'],
    ['style' => 'note', 'text' => 'Scheduler menjalankan backup penuh simtaman:backup setiap hari jam 02:00. Saat kegiatan lapangan, opsional backup tiap jam: 0 * * * * cd /var/www/samasta && BACKUP_LABEL=pemutakhiran bash scripts/vps-backup.sh'],

    ['style' => 'heading1', 'text' => '8. Kegiatan pemutakhiran data taman'],
    ['style' => 'table_header', 'text' => "Peran\tAkses\tModul"],
    ['style' => 'table_row', 'text' => "Admin (operator)\tLogin → backpanel\tData Taman → Kelola Taman → Edit (GPS, fasilitas, foto)"],
    ['style' => 'table_row', 'text' => "Administrator\tBackpanel penuh\t+ menu Sistem, import CSV, DPA"],
    ['style' => 'table_row', 'text' => "Pengawas\tLogin → Input Lapangan\tPemeliharaan / progres permohonan (bukan edit profil penuh taman)"],
    ['style' => 'bullet', 'text' => 'Profil taman lengkap hanya lewat admin Kelola Taman → Edit.'],
    ['style' => 'bullet', 'text' => 'Setelah kegiatan: php artisan simtaman:backup --full --label=sesudah-pemutakhiran'],
    ['style' => 'bullet', 'text' => 'Unduh file backup dari /var/backups/samasta ke PC tim sebagai salinan di luar server.'],
    ['style' => 'body', 'text' => 'Detail: docs/KEGIATAN-PEMUTAKHIRAN-TAMAN.md'],

    ['style' => 'heading1', 'text' => '9. Isi backup (simtaman:backup)'],
    ['style' => 'bullet', 'text' => 'Database: file .sql.gz (MySQL) atau salinan .sqlite.'],
    ['style' => 'bullet', 'text' => 'Snapshot tamans.json — semua taman + relasi gambar/kelurahan.'],
    ['style' => 'bullet', 'text' => 'Dengan --full: ZIP storage/app/public (foto upload).'],
    ['style' => 'note', 'text' => 'Jangan jalankan migrate:fresh atau db:wipe di production. Jangan deploy tanpa backup saat tim sedang input.'],

    ['style' => 'heading1', 'text' => '10. Install baru (VPS belum pernah di-setup)'],
    ['style' => 'body', 'text' => 'Urutan: install LEMP (Ubuntu) → clone repo ke /var/www/samasta → salin .env → composer install --no-dev → npm ci && npm run build → php artisan migrate --force → seeder WilayahBatam & TimPelaksana (hanya DB kosong) → storage:link → Nginx (lihat docs/deploy/nginx-sitaman.conf.example).'],
    ['style' => 'body', 'text' => 'Checklist lengkap ada di docs/deploy/VPS-BATAMGARDEN.md bagian Checklist deploy.'],

    ['style' => 'heading1', 'text' => '11. Ngrok vs VPS'],
    ['style' => 'body', 'text' => 'Ngrok hanya tunnel sementara dari PC/laptop — tidak menggantikan deploy ke VPS 103.150.92.153 untuk kegiatan resmi dan backup terpusat.'],

    ['style' => 'heading1', 'text' => '12. Troubleshooting singkat'],
    ['style' => 'table_header', 'text' => "Gejala\tTindakan"],
    ['style' => 'table_row', 'text' => "502 / blank\tCek nginx & php-fpm; php artisan config:clear lalu config:cache"],
    ['style' => 'table_row', 'text' => "Foto tidak tampil\tphp artisan storage:link; permission storage/"],
    ['style' => 'table_row', 'text' => "419 / logout cepat\tPerpanjang SESSION_LIFETIME; pastikan HTTPS & APP_URL konsisten"],
    ['style' => 'table_row', 'text' => "GPS tanpa kelurahan\tWILAYAH_GEOCODER_ENABLED=true; cek internet server ke Nominatim"],

    ['style' => 'spacer'],
    ['style' => 'meta', 'text' => '— Dokumen dihasilkan otomatis: php scripts/generate-go-live-docx.php —'],
];

writeDocx($outPath, $paragraphs);

echo "Created: {$outPath}\n";
