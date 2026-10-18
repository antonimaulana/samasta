# Verifikasi deploy export CSV Kelola Taman

## Tanda versi export **sudah aktif** (build 2)

1. Tombol di Kelola Taman: **Export CSV (223 lokasi)** — angka sama dengan “Total lokasi sesuai filter”.
2. Nama file unduhan: `data-taman-223-lokasi-2026-10-19_HHMMSS.csv` (bukan `data-taman-2026-10-19_....csv` tanpa jumlah).
3. Baris pertama file (setelah BOM): `# simtaman_export build=2 rows=223`
4. Header respons (DevTools → Network): `X-Simtaman-Export-Build: 2` dan `X-Export-Row-Count: 223`

## Jika masih file lama (251 baris, nama tanpa `-223-lokasi-`)

Kode PHP di server belum terbaru. Di VPS:

```bash
cd /var/www/samasta
git fetch origin && git log -1 --oneline   # harus ada commit export AdminTamanListQuery
bash scripts/vps-deploy-update.sh
sudo systemctl reload php8.3-fpm   # atau php8.2-fpm — wajib agar OPcache muat ulang
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Uji lagi export dari **Kelola Taman** (bukan halaman Import).

## Import CSV

Baris komentar `# simtaman_export ...` diabaikan otomatis oleh importer.
