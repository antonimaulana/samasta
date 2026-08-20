<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Panduan Deploy {{ $appName }} — VPS Biznet + Cloudflare</title>
    <style>
        @page { size: A4 portrait; margin: 18mm 16mm 20mm 16mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.45;
            color: #1a1a1a;
        }
        h1 {
            font-size: 20px;
            color: #14532d;
            margin: 0 0 6px;
            line-height: 1.2;
        }
        h2 {
            font-size: 13px;
            color: #166534;
            margin: 16px 0 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #bbf7d0;
            page-break-after: avoid;
        }
        h3 {
            font-size: 11px;
            color: #15803d;
            margin: 12px 0 6px;
            page-break-after: avoid;
        }
        p { margin: 0 0 8px; text-align: justify; }
        ul, ol { margin: 0 0 8px 16px; padding: 0; }
        li { margin-bottom: 4px; }
        .cover {
            text-align: center;
            padding: 40px 0 24px;
            page-break-after: always;
        }
        .cover .subtitle {
            font-size: 13px;
            color: #374151;
            margin-bottom: 24px;
        }
        .cover .meta-box {
            margin: 32px auto 0;
            width: 85%;
            border: 1px solid #d1d5db;
            padding: 14px;
            text-align: left;
            font-size: 10px;
        }
        .cover .meta-box td { padding: 3px 6px; vertical-align: top; }
        .cover .meta-box td:first-child { width: 34%; font-weight: bold; color: #374151; }
        .note {
            background: #f0fdf4;
            border-left: 3px solid #16a34a;
            padding: 8px 10px;
            margin: 8px 0 12px;
            font-size: 9px;
        }
        .warn {
            background: #fffbeb;
            border-left: 3px solid #d97706;
            padding: 8px 10px;
            margin: 8px 0 12px;
            font-size: 9px;
        }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 12px;
            font-size: 9px;
        }
        table.data th,
        table.data td {
            border: 1px solid #d1d5db;
            padding: 5px 6px;
            vertical-align: top;
        }
        table.data th {
            background: #ecfdf5;
            color: #14532d;
            text-align: left;
        }
        pre, code {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 8px;
            line-height: 1.35;
        }
        pre {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            padding: 8px;
            margin: 6px 0 10px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .checklist td:first-child { width: 6%; text-align: center; }
        .page-break { page-break-before: always; }
        .footer-note {
            margin-top: 16px;
            font-size: 8px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
        .toc ol { margin-left: 18px; }
        .toc li { margin-bottom: 5px; }
        .arch {
            text-align: center;
            font-size: 9px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px;
            margin: 10px 0;
        }
    </style>
</head>
<body>

<div class="cover">
    <h1>Panduan Deploy {{ $appName }}</h1>
    <p class="subtitle">Hosting Production di VPS Biznet Gio Cloud + Cloudflare</p>
    <p style="font-size: 11px; color: #4b5563;">Portal SAMASTA — Disperakimtan Kota Batam</p>

    <table class="meta-box">
        <tr>
            <td>Dokumen</td>
            <td>Panduan Deploy VPS Biznet + Cloudflare</td>
        </tr>
        <tr>
            <td>Stack Aplikasi</td>
            <td>Laravel 12, PHP 8.2+, MySQL/MariaDB, Vite + Tailwind 4, DomPDF</td>
        </tr>
        <tr>
            <td>Target Server</td>
            <td>Biznet Gio — VPS BatamGarden, Ubuntu 24.04, West Java, 2 vCPU / 4 GB RAM</td>
        </tr>
        <tr>
            <td>IP Production</td>
            <td>103.150.92.153 (Reverse DNS: ip-153-92-150-103.wjv-1.biznetg.io)</td>
        </tr>
        <tr>
            <td>CDN / DNS / SSL</td>
            <td>Cloudflare (Free plan cukup untuk awal)</td>
        </tr>
        <tr>
            <td>Dibuat</td>
            <td>{{ $generatedAt }}</td>
        </tr>
    </table>
</div>

<h2>Daftar Isi</h2>
<div class="toc">
    <ol>
        <li>Gambaran Arsitektur</li>
        <li>Persyaratan Server</li>
        <li>Pesan VPS di Biznet Gio</li>
        <li>Akses Pertama &amp; Persiapan Server</li>
        <li>Install Stack LEMP (Nginx + PHP + MySQL)</li>
        <li>Firewall &amp; Keamanan Dasar</li>
        <li>Setup Database Production</li>
        <li>Deploy Aplikasi SAMASTA</li>
        <li>Konfigurasi Nginx</li>
        <li>Hubungkan Domain ke Cloudflare</li>
        <li>SSL &amp; Trust Proxy Laravel</li>
        <li>Cron Scheduler &amp; Queue Worker</li>
        <li>Checklist Go-Live</li>
        <li>Update Aplikasi ke Depan</li>
        <li>Estimasi Biaya &amp; Tips Instansi Pemerintah</li>
        <li>Troubleshooting Tampilan Berantakan</li>
        <li>Troubleshooting SSH ke VPS</li>
    </ol>
</div>

<div class="page-break"></div>

<h2>1. Gambaran Arsitektur</h2>
<div class="arch">
    Pengguna Browser → Cloudflare (DNS + SSL + Proteksi)<br>
    → VPS Biznet Gio (BatamGarden) → Nginx → PHP-FPM + Laravel<br>
    → MySQL/MariaDB + storage/app (upload &amp; PDF)
</div>

<div class="note">
    <strong>Penting:</strong> Cloudflare bukan hosting PHP. Cloudflare berperan sebagai DNS, SSL publik, CDN, dan proteksi DDoS. Aplikasi Laravel tetap harus berjalan di VPS Biznet.
</div>

<table class="data">
    <tr>
        <th>Komponen</th>
        <th>Di Cloudflare</th>
        <th>Di VPS Biznet</th>
    </tr>
    <tr>
        <td>Domain &amp; SSL publik</td>
        <td>Ya</td>
        <td>Sertifikat origin (Let's Encrypt)</td>
    </tr>
    <tr>
        <td>Menjalankan Laravel/PHP</td>
        <td>Tidak</td>
        <td>Ya</td>
    </tr>
    <tr>
        <td>Database</td>
        <td>Tidak</td>
        <td>MySQL/MariaDB lokal</td>
    </tr>
    <tr>
        <td>Upload file &amp; export PDF</td>
        <td>Cache static (opsional)</td>
        <td>storage/ di server</td>
    </tr>
    <tr>
        <td>Cron &amp; antrian job</td>
        <td>Tidak</td>
        <td>cron + Supervisor</td>
    </tr>
</table>

<h2>2. Persyaratan Server</h2>
<table class="data">
    <tr>
        <th>Kebutuhan</th>
        <th>Spesifikasi Minimum</th>
    </tr>
    <tr>
        <td>PHP</td>
        <td>8.2 atau 8.3 dengan extension: pdo, mbstring, openssl, tokenizer, xml, ctype, json, fileinfo, curl, zip, bcmath, intl, <strong>gd</strong> (wajib untuk PDF)</td>
    </tr>
    <tr>
        <td>Web server</td>
        <td>Nginx</td>
    </tr>
    <tr>
        <td>Database</td>
        <td>MySQL 8 atau MariaDB 10.11+ (production — jangan pakai SQLite)</td>
    </tr>
    <tr>
        <td>RAM</td>
        <td>Minimal 2 GB (1 GB terlalu sempit untuk MySQL + Laravel + PDF)</td>
    </tr>
    <tr>
        <td>Storage</td>
        <td>Minimal 60 GB SSD</td>
    </tr>
    <tr>
        <td>Build assets</td>
        <td>Node.js 20+ (saat deploy), hasil build disimpan di public/build/</td>
    </tr>
    <tr>
        <td>Layanan tambahan</td>
        <td>Supervisor (queue worker), cron (scheduler Laravel)</td>
    </tr>
</table>

<h2>3. Pesan VPS di Biznet Gio</h2>
<p>Login ke <strong>biznetgio.com</strong> → Compute → NEO Lite → Create New.</p>

<table class="data">
    <tr>
        <th>Setting</th>
        <th>Rekomendasi</th>
    </tr>
    <tr>
        <td>Region</td>
        <td><strong>Jakarta</strong> — latency terbaik untuk Batam &amp; Indonesia</td>
    </tr>
    <tr>
        <td>Operating System</td>
        <td><strong>Ubuntu 24.04 LTS</strong></td>
    </tr>
    <tr>
        <td>Paket</td>
        <td>Minimal <strong>2 vCPU / 2 GB RAM / 60 GB SSD</strong></td>
    </tr>
    <tr>
        <td>Snapshot</td>
        <td>Centang — backup otomatis sangat disarankan</td>
    </tr>
    <tr>
        <td>SSH Key</td>
        <td>Import SSH key (lebih aman dari password saja)</td>
    </tr>
</table>

<p>Catat setelah VPS aktif: <strong>Public IP</strong>, <strong>SSH username</strong> (bukan selalu ubuntu), dan simpan private key (.pem) dengan aman.</p>

<h3>3.1 VPS Production: BatamGarden (aktif)</h3>
<table class="data">
    <tr><th>Item</th><th>Nilai</th></tr>
    <tr><td>Nama layanan</td><td>BatamGarden</td></tr>
    <tr><td>OS</td><td>Ubuntu 24.04 LTS</td></tr>
    <tr><td>Spesifikasi</td><td>2 vCPU / 4 GB RAM / 60 GB SSD (MS 4.2)</td></tr>
    <tr><td>Region</td><td>West Java</td></tr>
    <tr><td>Public IP</td><td><strong>103.150.92.153</strong></td></tr>
    <tr><td>Reverse DNS</td><td>ip-153-92-150-103.wjv-1.biznetg.io</td></tr>
    <tr><td>SSH Username</td><td><strong>GardenBatam</strong></td></tr>
    <tr><td>SSH Key Pair</td><td>GardenBatam2026 (.pem)</td></tr>
    <tr><td>Path aplikasi</td><td>/var/www/samasta</td></tr>
</table>

<div class="warn">
    <strong>Penting:</strong> Nama layanan (BatamGarden) dan key pair (GardenBatam2026) <strong>bukan hostname SSH</strong>. Gunakan IP 103.150.92.153 atau reverse DNS Biznet.
</div>

<div class="page-break"></div>

<h2>4. Akses Pertama &amp; Persiapan Server</h2>
<h3>4.1 SSH dari Windows (PowerShell)</h3>
<pre>ssh -i "C:\Users\USER\Downloads\GardenBatam2026.pem" GardenBatam@103.150.92.153</pre>
<p>Alternatif hostname Biznet:</p>
<pre>ssh -i "C:\Users\USER\Downloads\GardenBatam2026.pem" GardenBatam@ip-153-92-150-103.wjv-1.biznetg.io</pre>

<p>Jika error permission key di Windows:</p>
<pre>icacls "C:\Users\USER\Downloads\GardenBatam2026.pem" /inheritance:r
icacls "C:\Users\USER\Downloads\GardenBatam2026.pem" /grant:r "%USERNAME%:(R)"</pre>

<h3>4.2 Alternatif: Console Biznet</h3>
<p>Jika file .pem hilang: Dashboard Biznet → BatamGarden → <strong>Open Console</strong>. Login dengan username <strong>GardenBatam</strong> + password Console Access.</p>

<h3>4.3 Persiapan setelah masuk</h3>
<pre>sudo apt update &amp;&amp; sudo apt upgrade -y
sudo timedatectl set-timezone Asia/Jakarta</pre>

<h2>5. Install Stack LEMP (Nginx + PHP + MySQL)</h2>
<pre>sudo apt install -y nginx

sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring \
  php8.3-xml php8.3-curl php8.3-zip php8.3-gd php8.3-bcmath \
  php8.3-intl php8.3-readline

sudo apt install -y mysql-server

sudo apt install -y git unzip curl supervisor certbot python3-certbot-nginx

curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs</pre>

<p>Verifikasi extension GD (wajib untuk export PDF):</p>
<pre>php -m | grep -i gd</pre>
<p>Output harus menampilkan: <code>gd</code></p>

<h2>6. Firewall &amp; Keamanan Dasar</h2>
<p>Di portal Biznet, pastikan Security Group mengizinkan port <strong>22, 80, 443</strong>. Di server:</p>
<pre>sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
sudo ufw status</pre>

<div class="warn">
    <strong>Jangan buka port MySQL (3306)</strong> ke internet publik. Database hanya boleh diakses dari localhost.
</div>

<h2>7. Setup Database Production</h2>
<pre>sudo mysql</pre>
<pre>CREATE DATABASE samasta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'samasta'@'localhost' IDENTIFIED BY 'PasswordKuatMin16Karakter!';
GRANT ALL PRIVILEGES ON samasta.* TO 'samasta'@'localhost';
FLUSH PRIVILEGES;
EXIT;</pre>

<div class="page-break"></div>

<h2>8. Deploy Aplikasi SAMASTA</h2>
<p><strong>Setup GitHub:</strong> lihat <code>docs/deploy/GITHUB-SETUP.md</code>. Repo: <code>github.com/antonimaulana/samasta</code></p>
<pre>sudo mkdir -p /var/www/samasta
sudo chown $USER:www-data /var/www/samasta
cd /var/www/samasta

# Setup deploy key (sekali) — lihat GITHUB-SETUP.md
bash scripts/vps-setup-deploy-key.sh

git clone git@github.com:antonimaulana/samasta.git .

composer install --no-dev --optimize-autoloader

cp .env.example .env
nano .env</pre>

<h3>8.1 Contoh .env Production</h3>
<pre>APP_NAME="Samasta"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://samasta.batam.go.id

APP_LOCALE=id
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=samasta
DB_USERNAME=samasta
DB_PASSWORD=PasswordKuatMin16Karakter!

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

LOG_LEVEL=warning</pre>

<div class="warn">
    Jangan isi ADMIN_PASSWORD di production. Buat akun admin manual via <code>php artisan tinker</code> dengan password kuat (minimal 12 karakter).
</div>

<h3>8.2 Deploy Checklist Aplikasi</h3>
<pre>php artisan key:generate

npm ci
npm run build
rm -f public/hot

php artisan migrate --force
php artisan db:seed --class=WilayahBatamSeeder --force
php artisan db:seed --class=TimPelaksanaSeeder --force
php artisan db:seed --class=DpaDocumentTemplateSeeder --force

php artisan storage:link

sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

php artisan config:cache
php artisan route:cache
php artisan view:cache</pre>

<h3>8.3 Buat Admin Production</h3>
<pre>php artisan tinker</pre>
<pre>\App\Models\User::create([
    'name' => 'Administrator Samasta',
    'email' => 'admin@sitaman.batam',
    'password' => bcrypt('PasswordAdminKuat!2026'),
    'role' => 'admin',
]);</pre>

<div class="page-break"></div>

<h2>9. Konfigurasi Nginx</h2>
<p>Buat file <code>/etc/nginx/sites-available/samasta</code>:</p>
<pre>server {
    listen 80;
    listen [::]:80;
    server_name samasta.batam.go.id;
    root /var/www/samasta/public;
    index index.php;

    client_max_body_size 25M;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}</pre>

<p>Aktifkan konfigurasi:</p>
<pre>sudo ln -s /etc/nginx/sites-available/samasta /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl reload nginx</pre>

<p>Contoh lengkap dengan HTTPS tersedia di repo: <code>docs/deploy/nginx-sitaman.conf.example</code></p>

<h2>10. Hubungkan Domain ke Cloudflare</h2>
<h3>10.1 Daftar Domain</h3>
<ol>
    <li>Login ke dash.cloudflare.com → Add a Site</li>
    <li>Masukkan domain (mis. batam.go.id atau subdomain)</li>
    <li>Pilih plan Free (cukup untuk awal)</li>
    <li>Ubah nameserver di registrar domain (PANDI) ke nameserver Cloudflare</li>
</ol>

<h3>10.2 Record DNS</h3>
<table class="data">
    <tr>
        <th>Type</th>
        <th>Name</th>
        <th>Content</th>
        <th>Proxy</th>
    </tr>
    <tr>
        <td>A</td>
        <td>samasta</td>
        <td>103.150.92.153</td>
        <td>Proxied (orange cloud)</td>
    </tr>
</table>

<p>Tunggu propagasi DNS (biasanya 5–30 menit, maksimal 48 jam).</p>

<h2>11. SSL &amp; Trust Proxy Laravel</h2>
<h3>11.1 SSL di Cloudflare</h3>
<ul>
    <li>SSL/TLS → Encryption mode: <strong>Full (strict)</strong></li>
    <li>Always Use HTTPS: ON</li>
    <li>Minimum TLS Version: 1.2</li>
</ul>

<h3>11.2 SSL di VPS (Let's Encrypt)</h3>
<pre>sudo certbot --nginx -d samasta.batam.go.id</pre>

<div class="note">
    <strong>APP_URL</strong> di .env harus sama persis dengan domain live (contoh: https://samasta.batam.go.id). Ketidakcocokan ini sering menyebabkan CSS/JS tidak termuat.
</div>

<h3>11.3 Trust Proxy (Laravel di belakang Cloudflare)</h3>
<p>Sudah diimplementasi di <code>bootstrap/app.php</code>:</p>
<pre>$middleware->trustProxies(at: '*');</pre>
<p>Setelah deploy, pastikan cache config terbaru:</p>
<pre>php artisan config:clear
php artisan config:cache</pre>

<div class="page-break"></div>

<h2>12. Cron Scheduler &amp; Queue Worker</h2>
<p>Aplikasi SAMASTA memiliki scheduler harian (reminder layanan, digest operasional) dan queue database.</p>

<h3>12.1 Cron Scheduler</h3>
<p>Jalankan <code>sudo crontab -e -u www-data</code> dan tambahkan:</p>
<pre>* * * * * cd /var/www/samasta &amp;&amp; php artisan schedule:run >> /dev/null 2>&1</pre>

<h3>12.2 Queue Worker (Supervisor)</h3>
<p>Buat file <code>/etc/supervisor/conf.d/samasta-worker.conf</code>:</p>
<pre>[program:samasta-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/samasta/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/samasta/storage/logs/worker.log</pre>

<pre>sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start samasta-worker:*</pre>

<p>Contoh lengkap: <code>docs/deploy/supervisor-sitaman.conf.example</code></p>

<h2>13. Checklist Go-Live</h2>
<table class="data checklist">
    <tr>
        <th>☐</th>
        <th>Item</th>
    </tr>
    <tr><td>☐</td><td>VPS BatamGarden aktif (103.150.92.153, 4 GB RAM)</td></tr>
    <tr><td>☐</td><td>PHP 8.3 + extension gd terpasang</td></tr>
    <tr><td>☐</td><td>MySQL production (bukan SQLite)</td></tr>
    <tr><td>☐</td><td>npm run build — ada file public/build/manifest.json</td></tr>
    <tr><td>☐</td><td>Tidak ada file public/hot</td></tr>
    <tr><td>☐</td><td>APP_DEBUG=false, APP_URL=https://domain-anda</td></tr>
    <tr><td>☐</td><td>Admin dibuat manual dengan password kuat</td></tr>
    <tr><td>☐</td><td>php artisan storage:link + permission storage benar</td></tr>
    <tr><td>☐</td><td>Domain Cloudflare A record → 103.150.92.153 (Proxied)</td></tr>
    <tr><td>☐</td><td>SSL Let's Encrypt + Cloudflare Full (strict)</td></tr>
    <tr><td>☑</td><td>trustProxies di bootstrap/app.php (sudah aktif di codebase)</td></tr>
    <tr><td>☐</td><td>Cron scheduler + queue worker aktif</td></tr>
    <tr><td>☐</td><td>Test: login admin, halaman publik, upload, export PDF</td></tr>
</table>

<h2>14. Update Aplikasi ke Depan</h2>
<pre>cd /var/www/samasta
bash scripts/vps-deploy-update.sh</pre>
<p>Atau manual:</p>
<pre>cd /var/www/samasta
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci &amp;&amp; npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart samasta-worker:*</pre>

<div class="page-break"></div>

<h2>15. Estimasi Biaya &amp; Tips Instansi Pemerintah</h2>
<table class="data">
    <tr>
        <th>Komponen</th>
        <th>Estimasi Bulanan</th>
    </tr>
    <tr>
        <td>Biznet MS 4.2 (2 vCPU / 4 GB)</td>
        <td>± Rp 150–250 ribu/bulan (BatamGarden)</td>
    </tr>
    <tr>
        <td>Cloudflare Free</td>
        <td>Rp 0</td>
    </tr>
    <tr>
        <td>Domain .go.id</td>
        <td>Sesuai tarif PANDI</td>
    </tr>
</table>

<h3>Tips Khusus</h3>
<ul>
    <li>Region West Java (BatamGarden) — latency ke Batam ± 30–50 ms</li>
    <li>Aktifkan snapshot Biznet sebelum update besar</li>
    <li>Jangan expose port MySQL ke publik</li>
    <li>File .env jangan di-commit ke Git — simpan backup terpisah</li>
    <li>Pantau log di storage/logs/laravel.log secara berkala</li>
    <li>Upgrade paket NEO Lite jika traffic meningkat</li>
</ul>

<h2>16. Troubleshooting Tampilan Berantakan</h2>
<p>Gejala: halaman tampil tanpa CSS/styling. Penyebab umum dan solusi:</p>

<table class="data">
    <tr>
        <th>Penyebab</th>
        <th>Solusi</th>
    </tr>
    <tr>
        <td>Belum npm run build</td>
        <td>Jalankan npm ci &amp;&amp; npm run build di server</td>
    </tr>
    <tr>
        <td>Ada file public/hot</td>
        <td>Hapus: rm -f public/hot</td>
    </tr>
    <tr>
        <td>APP_URL tidak cocok dengan domain</td>
        <td>Update .env lalu php artisan config:cache</td>
    </tr>
    <tr>
        <td>Cloudflare tanpa trustProxies</td>
        <td>Sudah di bootstrap/app.php — jalankan config:cache setelah deploy</td>
    </tr>
    <tr>
        <td>Review via trycloudflare.com</td>
        <td>URL berubah tiap restart — update APP_URL setiap kali (hanya untuk demo, bukan production)</td>
    </tr>
</table>

<h3>Perintah Perbaikan Cepat</h3>
<pre>rm -f public/hot
npm ci &amp;&amp; npm run build
php artisan config:clear
php artisan view:clear
php artisan config:cache</pre>

<h2>17. Troubleshooting SSH ke VPS</h2>
<table class="data">
    <tr><th>Error / Gejala</th><th>Penyebab</th><th>Solusi</th></tr>
    <tr>
        <td>Could not resolve hostname gardenbatam</td>
        <td>Nama layanan Biznet bukan DNS</td>
        <td>Pakai IP 103.150.92.153 atau reverse DNS Biznet</td>
    </tr>
    <tr>
        <td>Permission denied (publickey)</td>
        <td>Key .pem tidak dipakai atau username salah</td>
        <td>ssh -i GardenBatam2026.pem GardenBatam@103.150.92.153</td>
    </tr>
    <tr>
        <td>Permission denied + username ubuntu</td>
        <td>Username Biznet bukan ubuntu</td>
        <td>Gunakan username GardenBatam</td>
    </tr>
    <tr>
        <td>File .pem tidak ditemukan</td>
        <td>Private key hilang</td>
        <td>Open Console di dashboard Biznet</td>
    </tr>
    <tr>
        <td>UNPROTECTED PRIVATE KEY FILE</td>
        <td>Permission key terlalu terbuka (Windows)</td>
        <td>Jalankan icacls — lihat bagian 4.1</td>
    </tr>
</table>

<div class="note">
    Referensi VPS: docs/deploy/VPS-BATAMGARDEN.md · Nginx: docs/deploy/nginx-sitaman.conf.example · Supervisor: docs/deploy/supervisor-sitaman.conf.example · .env: docs/deploy/.env.production.example
</div>

<div class="footer-note">
    Dokumen ini digenerate otomatis dari proyek {{ $appName }} — {{ $generatedAt }}<br>
    Perintah generate ulang: php artisan docs:deploy-guide-pdf
</div>

</body>
</html>
