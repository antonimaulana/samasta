# Kesiapan live — pemutakhiran data taman (SIMTAMAN)

Panduan singkat agar kegiatan lapangan besok **lancar**, data taman **tersimpan aman**, dan dapat **dipulihkan** jika terjadi gangguan.

---

## 1. Alur input yang dipakai besok

| Peran | Cara akses | Modul |
|--------|------------|--------|
| **Admin** (operator) | Login → langsung **backpanel** | **Data Taman → Kelola Taman → Edit** (GPS, fasilitas, kondisi, foto, kelurahan) |
| **Administrator** | Sama + menu Sistem bila perlu | Kelola Taman + import CSV hanya administrator |
| **Pengawas** | Login → **Input Lapangan** (bukan edit profil penuh) | Pemeliharaan / progres permohonan di lapangan |

Profil taman lengkap (koordinat, galeri, status data) diperbarui lewat **admin → Kelola Taman → Edit**, bukan modul Input Lapangan.

---

## 2. Malam ini / pagi hari H — checklist VPS

Jalankan di server (`/var/www/samasta`):

```bash
# 1) Tarik kode terbaru (backup otomatis sebelum pull jika BACKUP_BEFORE_DEPLOY=1)
bash scripts/vps-deploy-update.sh

# 2) Cek kesiapan aplikasi
php artisan simtaman:preflight-pemutakhiran

# 3) Snapshot manual sebelum tim turun lapangan
php artisan simtaman:backup --full --label=sebelum-pemutakhiran
```

Pastikan di `.env` production:

- `APP_ENV=production`, `APP_DEBUG=false`
- `APP_URL=https://…` (domain live)
- `SESSION_LIFETIME=480` (opsional — sesi admin tidak putus saat kerja lapangan panjang)
- `WILAYAH_GEOCODER_ENABLED=true` (isi kelurahan dari GPS)
- `BACKUP_PATH=/var/backups/samasta` (buat folder: `sudo mkdir -p /var/backups/samasta && sudo chown www-data:www-data /var/backups/samasta`)

PHP/Nginx (foto taman):

- `upload_max_filesize` / `post_max_size` ≥ **20M**
- `client_max_body_size` Nginx ≥ **20M**

```bash
php artisan storage:link   # jika preflight mengeluh symlink
php artisan up             # pastikan tidak maintenance mode
curl -sf https://domain-anda/up && echo OK
```

---

## 3. Backup — apa yang dicadangkan

Perintah `php artisan simtaman:backup`:

| File | Isi |
|------|-----|
| `*_database.sql.gz` atau `*.sqlite` | Seluruh database (termasuk tabel `tamans`, `taman_images`, log aktivitas) |
| `*_tamans.json` | Snapshot JSON semua taman + relasi gambar/kelurahan (mudah audit & banding) |
| `*_storage-public.zip` (dengan `--full`) | Semua foto upload di `storage/app/public` |

**Retensi:** `BACKUP_KEEP_DAYS` (default 30 hari). File lama di folder backup dihapus otomatis.

**Otomatis:** scheduler Laravel `simtaman:backup --full` setiap hari **02:00** (butuh cron `* * * * * php artisan schedule:run`).

**Saat kegiatan (disarankan):** cron tiap jam:

```cron
0 * * * * cd /var/www/samasta && BACKUP_LABEL=pemutakhiran bash scripts/vps-backup.sh >> /var/log/samasta-backup.log 2>&1
```

Atau manual dari SSH kapan saja:

```bash
php artisan simtaman:backup --full --label=pemutakhiran-siang
```

**Deploy:** `scripts/vps-deploy-update.sh` menjalankan backup `--label=sebelum-deploy` sebelum `git pull` (nonaktifkan dengan `BACKUP_BEFORE_DEPLOY=0` jika perlu).

---

## 4. Agar data tidak hilang

1. **Jangan** jalankan `migrate:fresh`, `db:wipe`, atau hapus folder `storage/app/public` di production.
2. **Jangan** deploy tanpa backup jika tim sedang input — tunggu `simtaman:backup` selesai.
3. Setiap perubahan taman via admin tercatat di **Log Aktivitas** (menu Sistem, administrator).
4. Simpan salinan backup **di luar VPS** (unduh `*_tamans.json` + `*_storage-public.zip` ke PC tim minimal sekali sebelum & sesudah kegiatan).

### Pulihkan database (MySQL) — contoh

```bash
gunzip -c /var/backups/samasta/simtaman_YYYY-MM-DD_HHMMSS_*_database.sql.gz | mysql -u USER -p NAMA_DB
```

### Pulihkan foto

```bash
unzip -o /var/backups/samasta/simtaman_*_storage-public.zip -d /tmp/restore-public
rsync -a /tmp/restore-public/ /var/www/samasta/storage/app/public/
```

---

## 5. Troubleshooting cepat di lapangan

| Gejala | Tindakan |
|--------|----------|
| Foto tidak muncul setelah upload | `php artisan storage:link`; cek permission `storage/` & `bootstrap/cache/` |
| GPS/kelurahan tidak terisi | Cek internet server ke Nominatim; `WILAYAH_GEOCODER_ENABLED=true` |
| 419 / logout saat edit | Perpanjang `SESSION_LIFETIME`; pastikan satu domain (HTTPS) |
| 502 / lambat | `php artisan config:clear` jangan di tengah input massal; cek RAM VPS |
| Akun Admin tidak bisa edit | Pastikan role **Admin** (operator), bukan Pengawas |

---

## 6. Setelah kegiatan

```bash
php artisan simtaman:backup --full --label=sesudah-pemutakhiran
```

Unduh backup terbaru dari `/var/backups/samasta` (atau `storage/app/backups` jika path default). Opsional: `php artisan tamans:assign-kelurahan-from-coordinates` untuk taman yang koordinatnya baru diisi tanpa kelurahan.

---

## 7. Push kode dari developer (Windows)

Pastikan branch yang dipakai production sudah di-push ke GitHub, lalu di VPS:

```bash
cd /var/www/samasta && bash scripts/vps-deploy-update.sh
```

Tanpa push + deploy, fitur terbaru (form GPS, backup, redirect login Admin) **belum** ada di server live.
