<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Dokumentasi Arsitektur {{ $appName }}</title>
    <style>
        @page { size: A4 portrait; margin: 18mm 16mm 20mm 16mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.45;
            color: #1a1a1a;
        }
        h1 { font-size: 20px; color: #14532d; margin: 0 0 6px; }
        h2 {
            font-size: 13px; color: #166534; margin: 16px 0 8px;
            padding-bottom: 4px; border-bottom: 1px solid #bbf7d0;
            page-break-after: avoid;
        }
        h3 { font-size: 11px; color: #15803d; margin: 12px 0 6px; page-break-after: avoid; }
        p { margin: 0 0 8px; text-align: justify; }
        ul, ol { margin: 0 0 8px 16px; padding: 0; }
        li { margin-bottom: 4px; }
        .cover { text-align: center; padding: 40px 0 24px; page-break-after: always; }
        .cover .subtitle { font-size: 13px; color: #374151; margin-bottom: 24px; }
        .cover .meta-box {
            margin: 32px auto 0; width: 85%; border: 1px solid #d1d5db;
            padding: 14px; text-align: left; font-size: 10px;
        }
        .cover .meta-box td { padding: 3px 6px; vertical-align: top; }
        .cover .meta-box td:first-child { width: 34%; font-weight: bold; color: #374151; }
        table.data {
            width: 100%; border-collapse: collapse; margin: 8px 0 12px; font-size: 9px;
        }
        table.data th, table.data td {
            border: 1px solid #d1d5db; padding: 5px 6px; vertical-align: top;
        }
        table.data th { background: #ecfdf5; color: #14532d; text-align: left; }
        pre, code { font-family: DejaVu Sans Mono, monospace; font-size: 8px; line-height: 1.35; }
        pre {
            background: #f3f4f6; border: 1px solid #e5e7eb; padding: 8px;
            margin: 6px 0 10px; white-space: pre-wrap; word-wrap: break-word;
        }
        .note {
            background: #f0fdf4; border-left: 3px solid #16a34a;
            padding: 8px 10px; margin: 8px 0 12px; font-size: 9px;
        }
        .arch-box {
            text-align: center; font-size: 9px; background: #f9fafb;
            border: 1px solid #e5e7eb; padding: 10px; margin: 10px 0;
        }
        .page-break { page-break-before: always; }
        .toc ol { margin-left: 18px; }
        .toc li { margin-bottom: 5px; }
        .footer-note {
            margin-top: 16px; font-size: 8px; color: #6b7280;
            text-align: center; border-top: 1px solid #e5e7eb; padding-top: 8px;
        }
    </style>
</head>
<body>

<div class="cover">
    <h1>Dokumentasi Arsitektur {{ $appName }}</h1>
    <p class="subtitle">Portal Manajemen Pertamanan &amp; Operasional Disperakimtan Kota Batam</p>
    <p style="font-size: 11px; color: #4b5563;">Sistem Aplikasi Manajemen Asri, Satuan Taman &amp; Anggaran</p>
    <table class="meta-box">
        <tr><td>Dokumen</td><td>Dokumentasi Arsitektur &amp; Struktur Development</td></tr>
        <tr><td>Stack</td><td>Laravel 12, PHP 8.2+, Blade, Tailwind 4, Vite 7, DomPDF</td></tr>
        <tr><td>Database</td><td>SQLite (dev) / MySQL-MariaDB (production)</td></tr>
        <tr><td>Pola</td><td>Monolith server-rendered, business logic di app/Support</td></tr>
        <tr><td>Test Suite</td><td>101 test PHPUnit (Feature + Unit)</td></tr>
        <tr><td>Dibuat</td><td>{{ $generatedAt }}</td></tr>
    </table>
</div>

<h2>Daftar Isi</h2>
<div class="toc">
    <ol>
        <li>Gambaran Umum</li>
        <li>Tech Stack</li>
        <li>Struktur Folder</li>
        <li>Routing &amp; Middleware</li>
        <li>Modul Aplikasi</li>
        <li>Model &amp; Database</li>
        <li>Layer Support (Business Logic)</li>
        <li>Otorisasi &amp; Role</li>
        <li>Views &amp; Frontend</li>
        <li>Testing</li>
        <li>Background Jobs &amp; Scheduler</li>
        <li>Build &amp; Deploy</li>
        <li>Pola &amp; Konvensi Development</li>
        <li>Status Kualitas &amp; Catatan Teknis</li>
    </ol>
</div>

<div class="page-break"></div>

<h2>1. Gambaran Umum</h2>
<p>{{ $appName }} (SITAMAN) adalah aplikasi web monolith berbasis Laravel untuk mengelola operasional pertamanan kota, portal informasi publik, engagement warga, dan monitoring DPA (Daftar Paket Anggaran).</p>

<div class="arch-box">
    Portal Publik (/) ──→ routes/public.php<br>
    Panel Admin (/admin) ──→ routes/admin.php<br>
    ↓<br>
    Controllers → app/Support (business logic) → Models → Database<br>
    ↓<br>
    Blade Views + Vite Assets (CSS/JS)
</div>

<p>Aplikasi <strong>tidak memiliki REST API</strong> dan <strong>tidak menggunakan SPA</strong> (React/Vue). Semua halaman di-render server-side via Blade templates.</p>

<h2>2. Tech Stack</h2>
<table class="data">
    <tr><th>Layer</th><th>Teknologi</th></tr>
    <tr><td>Backend</td><td>PHP ^8.2, Laravel ^12.0</td></tr>
    <tr><td>Frontend</td><td>Blade, Tailwind CSS v4 (@tailwindcss/vite), Vite 7</td></tr>
    <tr><td>Database</td><td>SQLite (development), MySQL/MariaDB (production)</td></tr>
    <tr><td>Session / Cache / Queue</td><td>Database driver (sessions, cache, jobs tables)</td></tr>
    <tr><td>PDF Export</td><td>dompdf/dompdf ^3.1 — wajib PHP extension GD</td></tr>
    <tr><td>Geocoding</td><td>OpenStreetMap Nominatim (WilayahGeocoder)</td></tr>
    <tr><td>Locale</td><td>Bahasa Indonesia, timezone Asia/Jakarta</td></tr>
    <tr><td>Testing</td><td>PHPUnit 11, SQLite in-memory</td></tr>
</table>

<h2>3. Struktur Folder</h2>
<pre>sitaman-batam/
├── app/
│   ├── Http/Controllers/     # 36 controller (Public + Admin + DPA)
│   ├── Models/               # 30 model Eloquent
│   ├── Support/              # 40 kelas business logic
│   ├── Policies/             # 14 policy otorisasi
│   ├── Http/Middleware/      # Admin access, viewer scope, audit
│   ├── Console/Commands/     # Scheduler, reminder, deploy docs
│   └── Mail/Notifications/   # Email digest &amp; notifikasi
├── bootstrap/app.php         # Routing, middleware, scheduler
├── config/                   # 12 file konfigurasi
├── database/
│   ├── migrations/           # 49 migration
│   └── seeders/              # 7 seeder
├── resources/
│   ├── views/                # ~148 Blade template
│   ├── css/                  # app.css, home.css
│   └── js/                   # app.js, home-page.js
├── routes/
│   ├── web.php               # Entry point
│   ├── public.php            # Route portal publik
│   └── admin.php             # Route panel admin
├── tests/
│   ├── Feature/              # 25 test fitur
│   └── Unit/                 # 1 test
└── docs/                     # Spesifikasi deploy &amp; environment</pre>

<div class="page-break"></div>

<h2>4. Routing &amp; Middleware</h2>
<h3>4.1 Route Files</h3>
<table class="data">
    <tr><th>File</th><th>Prefix</th><th>Isi Utama</th></tr>
    <tr>
        <td>routes/public.php</td><td>/</td>
        <td>Beranda, taman, RTH, ensiklopedia, aduan, survey, masukan</td>
    </tr>
    <tr>
        <td>routes/admin.php</td><td>/admin</td>
        <td>Dashboard, operasional, bibit, CMS, user, monitoring DPA</td>
    </tr>
</table>

<h3>4.2 Middleware Admin</h3>
<table class="data">
    <tr><th>Middleware</th><th>Fungsi</th></tr>
    <tr><td>auth</td><td>Wajib login</td></tr>
    <tr><td>admin.access</td><td>Cek role admin/operator/viewer + hak tulis/hapus</td></tr>
    <tr><td>admin.manage</td><td>Hanya admin — CMS, user, DPA, activity log</td></tr>
    <tr><td>admin.viewer.scope</td><td>Batasi viewer ke route read-only tertentu</td></tr>
    <tr><td>admin.audit</td><td>Log aktivitas mutasi admin</td></tr>
</table>

<p>Redirect: guest → /login, user terautentikasi → /admin</p>

<h2>5. Modul Aplikasi</h2>

<h3>5.1 Portal Publik</h3>
<ul>
    <li><strong>Beranda</strong> — statistik taman, pejabat, konten dinamis (KontenBerandaCache)</li>
    <li><strong>Taman &amp; RTH</strong> — peta, daftar, detail taman per wilayah</li>
    <li><strong>Ensiklopedia</strong> — artikel kategori + kuis interaktif</li>
    <li><strong>Masukan</strong> — hub aduan masyarakat &amp; survey kepuasan</li>
    <li><strong>Aduan</strong> — form dengan GPS, foto, honeypot anti-spam</li>
</ul>

<h3>5.2 Operasional Pertamanan (Admin)</h3>
<ul>
    <li>CRUD taman + galeri foto + import/export CSV + geocoder wilayah</li>
    <li>Pemeliharaan taman (tim armada, filter tanggal &amp; wilayah)</li>
    <li>Jadwal pemangkasan + notifikasi reminder</li>
    <li>Alat &amp; sarana operasional</li>
    <li>Laporan operasional pertamanan + export PDF</li>
    <li>Tim pelaksana &amp; pemetaan kelurahan</li>
</ul>

<h3>5.3 Manajemen Bibit</h3>
<ul>
    <li>Stok bibit, transaksi masuk/keluar</li>
    <li>Import CSV, alert stok minimum</li>
    <li>Laporan bibit + export PDF</li>
</ul>

<h3>5.4 Engagement Warga</h3>
<ul>
    <li>Review aduan masyarakat (admin)</li>
    <li>Survey kepuasan + cek status aduan publik</li>
    <li>Notifikasi admin (database notifications)</li>
</ul>

<h3>5.5 CMS &amp; Pengaturan (Admin only)</h3>
<ul>
    <li>Ensiklopedia (kategori + artikel)</li>
    <li>Pejabat, profil kota, kategori RTH</li>
    <li>Manajemen user (admin/operator/viewer)</li>
    <li>Activity log audit trail</li>
</ul>

<h3>5.6 Monitoring DPA (Admin only)</h3>
<p>Wizard 3 langkah: Pilih Tahun Anggaran → Sub Kegiatan → Kelola DPA/Paket</p>
<ul>
    <li>CRUD tahun anggaran, DPA, paket pekerjaan</li>
    <li>Import CSV paket pekerjaan</li>
    <li>Monitoring tahap: pengadaan → kontrak → selesai</li>
    <li>HPS/SPK items, progres, output</li>
    <li>Generasi dokumen PDF (usulan pengadaan, HPS, dll.)</li>
    <li>Master penyedia &amp; template dokumen</li>
</ul>

<div class="page-break"></div>

<h2>6. Model &amp; Database</h2>
<h3>6.1 Model (30)</h3>
<table class="data">
    <tr><th>Domain</th><th>Model</th></tr>
    <tr><td>Parks</td><td>Taman, TamanImage, Kecamatan, Kelurahan, RthKategori</td></tr>
    <tr><td>Operations</td><td>PemeliharaanTaman, PemeliharaanTamanArmada, Pemangkasan, AlatSaranaOperasional, TimPelaksana</td></tr>
    <tr><td>Bibit</td><td>Bibit, BibitMasuk, BibitKeluar</td></tr>
    <tr><td>Engagement</td><td>AduanMasyarakat, SurveyKepuasan</td></tr>
    <tr><td>Content</td><td>EnsiklopediaKategori, EnsiklopediaArtikel, Pejabat, KotaProfile</td></tr>
    <tr><td>DPA</td><td>DpaTahunAnggaran, Dpa, DpaPenyedia, DpaPaketPekerjaan, DpaPaketItemBelanja, DpaPaketDokumen, DpaPaketProgres, DpaPaketOutput, DpaDocumentTemplate</td></tr>
    <tr><td>System</td><td>User, ActivityLog</td></tr>
</table>

<h3>6.2 Migration (49 file)</h3>
<p>Termasuk tabel Laravel standar (users, sessions, cache, jobs, notifications) dan domain bisnis di atas.</p>

<h3>6.3 Seeder (7)</h3>
<table class="data">
    <tr><th>Seeder</th><th>Fungsi</th></tr>
    <tr><td>WilayahBatamSeeder</td><td>12 kecamatan, 64 kelurahan Batam</td></tr>
    <tr><td>TimPelaksanaSeeder</td><td>Data tim operasional</td></tr>
    <tr><td>DpaDocumentTemplateSeeder</td><td>Template dokumen DPA</td></tr>
    <tr><td>KontenBerandaSeeder</td><td>Konten beranda default</td></tr>
    <tr><td>EnsiklopediaSeeder</td><td>Artikel ensiklopedia sample</td></tr>
    <tr><td>TamanSeeder</td><td>Data taman sample (dev)</td></tr>
    <tr><td>DatabaseSeeder</td><td>Orkestrasi seeder + admin dev (non-production)</td></tr>
</table>

<h2>7. Layer Support (Business Logic)</h2>
<p>Logika bisnis utama berada di <strong>app/Support/</strong> (~40 kelas), bukan di controller:</p>
<table class="data">
    <tr><th>Kategori</th><th>Kelas Contoh</th></tr>
    <tr><td>Data builders</td><td>HomePageData, DashboardSummaryBuilder, TamanMapData, AdminLayoutData</td></tr>
    <tr><td>Import/Export</td><td>TamanCsvImporter, BibitCsvImporter, PaketPekerjaanCsvImporter, PdfExport</td></tr>
    <tr><td>Geography</td><td>WilayahGeocoder, KelurahanResolver, TamanWilayahAssigner, GeoDistance</td></tr>
    <tr><td>Operations</td><td>JadwalLayananQuery, OperationalAlertService, OperationalDigestDispatcher</td></tr>
    <tr><td>Access control</td><td>OperatorWilayahScope, ViewerRouteAllowlist, TimPelaksanaResolver</td></tr>
    <tr><td>DPA</td><td>DpaMonitoring, DpaDocumentGenerator, DpaDocumentFields, PaketPekerjaanCsvImporter</td></tr>
    <tr><td>Content</td><td>Ensiklopedia, KontenBerandaCache, PerdaKetertibanUmum</td></tr>
</table>

<div class="page-break"></div>

<h2>8. Otorisasi &amp; Role</h2>
<table class="data">
    <tr><th>Role</th><th>Hak Akses</th></tr>
    <tr>
        <td><strong>admin</strong></td>
        <td>Full access — operasional, CMS, user management, DPA, activity log, export</td>
    </tr>
    <tr>
        <td><strong>operator</strong></td>
        <td>Operasional tulis — dibatasi per wilayah/kelurahan via TimPelaksana. Tidak bisa CMS, user, DPA</td>
    </tr>
    <tr>
        <td><strong>viewer</strong></td>
        <td>Read-only — dashboard, lihat data operasional. Tidak bisa create/update/delete</td>
    </tr>
</table>

<p>Implementasi: 14 Policy classes + Gates di AppServiceProvider + Blade components (&lt;x-admin.can-write&gt;, dll.) + middleware stack.</p>

<p><strong>Wilayah scoping:</strong> Operator hanya melihat taman, pemeliharaan, aduan, pemangkasan di kelurahan yang ditugaskan via TimPelaksana (OperatorWilayahScope).</p>

<h2>9. Views &amp; Frontend</h2>
<h3>9.1 Layouts</h3>
<table class="data">
    <tr><th>Layout</th><th>Penggunaan</th></tr>
    <tr><td>layouts/public.blade.php</td><td>Portal publik — tema hijau, mobile navigation</td></tr>
    <tr><td>layouts/admin.blade.php</td><td>Panel admin — sidebar, alert operasional, notifikasi</td></tr>
    <tr><td>layouts/app.blade.php</td><td>Wrapper minimal (login)</td></tr>
</table>

<h3>9.2 Vite Entry Points</h3>
<pre>resources/css/app.css       → layout admin &amp; publik umum
resources/css/home.css      → halaman beranda
resources/js/app.js         → interaksi admin umum
resources/js/home-page.js   → interaksi beranda</pre>

<p>Build output: public/build/ (manifest.json + hashed assets). Wajib npm run build sebelum production.</p>

<h3>9.3 Konvensi Views</h3>
<ul>
    <li>_form.blade.php — partial form CRUD per resource</li>
    <li>admin/partials/ — komponen shared (table search, PDF styles, alerts)</li>
    <li>components/admin/ — permission wrappers, searchable select</li>
    <li>admin/*/pdf.blade.php — template export PDF operasional</li>
    <li>admin/dpa/documents/pdf/ — template dokumen DPA</li>
</ul>

<h2>10. Testing</h2>
<table class="data">
    <tr><th>Suite</th><th>Jumlah</th><th>Cakupan</th></tr>
    <tr><td>Feature</td><td>25 file</td><td>Keamanan, scope wilayah, import CSV, PDF, DPA, dashboard</td></tr>
    <tr><td>Unit</td><td>1 file</td><td>Placeholder</td></tr>
    <tr><td><strong>Total</strong></td><td><strong>101 test</strong></td><td><strong>311 assertions — semua lulus</strong></td></tr>
</table>

<p>Jalankan: <code>composer test</code> atau <code>php artisan test</code></p>

<p>Test penting: AdminAccessTest, ViewerScopeTest, OperatorWilayahScopeTest, P0SecurityTest, DpaMonitoringTest, TamanCsvImportTest, BibitImportTest, OperationalDigestTest</p>

<div class="page-break"></div>

<h2>11. Background Jobs &amp; Scheduler</h2>
<table class="data">
    <tr><th>Command</th><th>Jadwal</th><th>Fungsi</th></tr>
    <tr><td>layanan:send-reminders</td><td>07:00 harian</td><td>Reminder jadwal layanan pemangkasan</td></tr>
    <tr><td>operational:send-digest</td><td>07:30 harian</td><td>Email ringkasan operasional harian</td></tr>
    <tr><td>tamans:assign-kelurahan-from-coordinates</td><td>Manual</td><td>Backfill kelurahan taman dari koordinat GPS</td></tr>
</table>

<p>Queue: database driver — wajib Supervisor queue worker di production.</p>
<p>Cron: * * * * * php artisan schedule:run</p>

<h2>12. Build &amp; Deploy</h2>
<h3>12.1 Development</h3>
<pre>composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
composer dev    # serve + queue + vite concurrent</pre>

<h3>12.2 Production Checklist</h3>
<ol>
    <li>composer install --no-dev --optimize-autoloader</li>
    <li>npm ci &amp;&amp; npm run build</li>
    <li>php artisan migrate --force</li>
    <li>Seed: WilayahBatam, TimPelaksana, DpaDocumentTemplate</li>
    <li>php artisan storage:link</li>
    <li>php artisan config:cache / route:cache / view:cache</li>
    <li>Setup cron + Supervisor queue worker</li>
    <li>Pastikan PHP extension GD aktif</li>
    <li>APP_ENV=production, APP_DEBUG=false</li>
</ol>

<p>Dokumentasi deploy lengkap: docs/PANDUAN-DEPLOY-BIZNET-CLOUDFLARE.pdf</p>
<p>Referensi VPS production: docs/deploy/VPS-BATAMGARDEN.md (BatamGarden — 103.150.92.153)</p>
<p>Contoh config: docs/deploy/nginx-sitaman.conf.example, supervisor-sitaman.conf.example</p>

<h2>13. Pola &amp; Konvensi Development</h2>
<ol>
    <li><strong>Support-layer architecture</strong> — business logic di app/Support, controller tipis</li>
    <li><strong>Split route files</strong> — public.php vs admin.php</li>
    <li><strong>Layered authorization</strong> — middleware + policy + Blade components</li>
    <li><strong>Wilayah multi-tenancy</strong> — operator scoped per kelurahan</li>
    <li><strong>CSV import + PDF export</strong> — pola berulang untuk taman, bibit, paket DPA</li>
    <li><strong>Cache invalidation</strong> — model events di AppServiceProvider</li>
    <li><strong>Indonesian-first UX</strong> — label, flash message, route name dalam Bahasa Indonesia</li>
    <li><strong>Security extras</strong> — honeypot, throttle login/aduan, production-safe seeder</li>
    <li><strong>No API / No SPA</strong> — traditional server-rendered Laravel</li>
</ol>

<div class="page-break"></div>

<h2>14. Status Kualitas &amp; Catatan Teknis</h2>

<h3>14.1 Status Saat Ini</h3>
<table class="data">
    <tr><th>Aspek</th><th>Status</th></tr>
    <tr><td>Test suite</td><td>101/101 lulus — tidak ada test gagal</td></tr>
    <tr><td>Fitur inti operasional</td><td>Production-ready</td></tr>
    <tr><td>Modul DPA</td><td>Fungsional — MVP generasi PDF via Blade template</td></tr>
    <tr><td>Dokumentasi deploy</td><td>Tersedia (PDF + contoh config)</td></tr>
    <tr><td>Deploy production</td><td>Siap deploy — trustProxies sudah aktif, tinggal VPS Biznet</td></tr>
</table>

<h3>14.2 Catatan Teknis (Bukan Bug Kritis)</h3>
<table class="data">
    <tr><th>Item</th><th>Keterangan</th><th>Prioritas</th></tr>
    <tr>
        <td>trustProxies di bootstrap/app.php</td>
        <td>Sudah diimplementasi — Laravel percaya header Cloudflare (X-Forwarded-*)</td>
        <td>Selesai</td>
    </tr>
    <tr>
        <td>View orphan: tahun_anggarans/index.blade.php</td>
        <td>Sudah dihapus — route index diganti dashboard DPA</td>
        <td>Selesai</td>
    </tr>
    <tr>
        <td>Deprecated methods</td>
        <td>PemeliharaanTaman::timNames(), OperatorWilayahScope — legacy alias</td>
        <td>Rendah (technical debt)</td>
    </tr>
    <tr>
        <td>DPA PDF merge Word/Excel</td>
        <td>Fitur upload template Word/Excel belum diimplementasi — MVP pakai Blade</td>
        <td>Medium (enhancement)</td>
    </tr>
    <tr>
        <td>Unit test coverage</td>
        <td>Hanya 1 unit test placeholder — coverage dominan Feature test</td>
        <td>Rendah</td>
    </tr>
    <tr>
        <td>Form Request classes</td>
        <td>Hanya 6 Form Request — sebagian validasi inline di controller</td>
        <td>Rendah (refactor opsional)</td>
    </tr>
</table>

<h3>14.3 Prasyarat Production</h3>
<ul>
    <li>Ganti SQLite → MySQL/MariaDB</li>
    <li>npm run build + hapus public/hot</li>
    <li>APP_URL sesuai domain production</li>
    <li>Buat admin manual (jangan seed password lemah)</li>
    <li>Aktifkan cron scheduler + queue worker</li>
    <li>PHP extension GD aktif di server</li>
</ul>

<div class="note">
    <strong>Kesimpulan:</strong> Aplikasi dalam kondisi stabil untuk deployment. Tidak ada bug atau error yang terdeteksi dari test suite. Item yang tersisa adalah persiapan infrastruktur production (trustProxies, build assets, MySQL) dan cleanup/enhancement opsional.
</div>

<div class="footer-note">
    Dokumen ini digenerate otomatis dari proyek {{ $appName }} — {{ $generatedAt }}<br>
    Perintah generate ulang: php artisan docs:architecture-pdf
</div>

</body>
</html>
