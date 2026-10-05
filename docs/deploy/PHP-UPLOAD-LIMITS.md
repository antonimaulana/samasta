# Batas upload foto — PHP & Nginx (SIMTAMAN)

Aplikasi SIMTAMAN mengizinkan **10 MB per foto** (`Taman::GALLERY_MAX_UPLOAD_KILOBYTES`).  
Jika foto **di bawah 10 MB** tetap gagal dengan pesan **`upload_max_filesize`**, penyebabnya hampir selalu **limit di server PHP/Nginx**, bukan validasi Laravel.

## Gejala: foto di atas ~2 MB selalu gagal

Itu hampir pasti **`upload_max_filesize = 2M`** (default PHP). Aplikasi SIMTAMAN mengizinkan **10 MB**, tapi PHP menolak lebih dulu.

### Perbaikan cepat (VPS)

```bash
cd /var/www/samasta
git pull origin main   # pastikan script ada
sudo bash scripts/vps-set-upload-limits.sh
```

Script membuat `/etc/php/8.x/fpm/conf.d/99-simtaman-upload-limits.ini`, reload PHP-FPM, dan menyesuaikan `client_max_body_size` Nginx jika terdeteksi.

Versi PHP lain:

```bash
sudo PHP_VER=8.2 bash scripts/vps-set-upload-limits.sh
```

## Cek limit saat ini (di VPS)

```bash
cd /var/www/samasta
bash scripts/vps-check-upload-limits.sh
```

Atau manual:

```bash
php -i | grep -E 'upload_max_filesize|post_max_size'
grep -r client_max_body_size /etc/nginx/ 2>/dev/null | head -5
```

Contoh masalah: `upload_max_filesize => 2M` — foto 3 MB akan **selalu gagal** meskipun aplikasi mengizinkan 10 MB.

## Nilai yang disarankan (produksi)

| Setting | Min. | Disarankan |
|---------|------|------------|
| `upload_max_filesize` | 10M | **20M** |
| `post_max_size` | 10M | **20M** (harus ≥ total semua file + form dalam satu submit) |
| Nginx `client_max_body_size` | 10M | **20M** |

## Ubah PHP 8.x FPM (Ubuntu)

```bash
# Sesuaikan versi PHP (8.2 / 8.3)
PHP_VER=8.3
sudo nano /etc/php/${PHP_VER}/fpm/php.ini
```

Cari dan ubah (atau tambahkan):

```ini
upload_max_filesize = 20M
post_max_size = 20M
```

Simpan, lalu:

```bash
sudo systemctl reload php${PHP_VER}-fpm
php -i | grep -E 'upload_max_filesize|post_max_size'
```

**CLI vs FPM:** `php -i` di SSH memakai **CLI**. Yang dipakai website adalah **FPM**. Setelah edit `php.ini` FPM, verifikasi lewat:

```bash
echo '<?php phpinfo();' | sudo tee /var/www/samasta/public/_phpinfo_tmp.php
# Buka di browser (hapus file segera setelah cek): /_phpinfo_tmp.php → search upload_max_filesize
sudo rm -f /var/www/samasta/public/_phpinfo_tmp.php
```

Atau:

```bash
cd /var/www/samasta && php artisan simtaman:upload-limits
```

(jika command tersedia di versi deploy)

## Ubah Nginx

Di blok `server` situs SIMTAMAN (contoh `docs/deploy/nginx-sitaman.conf.example`):

```nginx
client_max_body_size 20M;
```

```bash
sudo nginx -t && sudo systemctl reload nginx
```

## Upload banyak foto sekaligus

`post_max_size` harus muat **semua foto + field form** dalam satu klik Simpan.  
Contoh: 5 × 8 MB ≈ 40 MB → set `post_max_size` dan `client_max_body_size` **min. 48M** atau unggah **batch lebih kecil**.

## Setelah ubah konfigurasi

Tidak perlu `git pull` hanya untuk ini. Cukup reload PHP-FPM + Nginx, lalu coba upload lagi di admin.

---

*Disperakimtan Kota Batam — SIMTAMAN*
