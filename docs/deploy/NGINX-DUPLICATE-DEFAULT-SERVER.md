# Nginx gagal start: duplicate default server

## Gejala

```text
nginx.service: Failed with result 'exit-code'
[emerg] duplicate default server for 0.0.0.0:80
curl: (7) Failed to connect to 127.0.0.1 port 80
```

PHP-FPM bisa **active (running)**, tapi situs tetap down karena **Nginx tidak jalan**.

## Penyebab

Lebih dari satu file di `/etc/nginx/sites-enabled/` memakai `default_server` pada port **80** (atau **443**).

### Kasus umum: file `*.bak-simtaman` di sites-enabled

Skrip lama `vps-set-upload-limits.sh` pernah membuat backup `samasta.bak-simtaman` **di folder sites-enabled**. Nginx memuat **samasta** dan **samasta.bak-simtaman** sekaligus → error duplicate.

```bash
sudo rm -f /etc/nginx/sites-enabled/*.bak-simtaman
sudo nginx -t && sudo systemctl start nginx
```

## Perbaikan cepat (di VPS)

```bash
# 1. Lihat baris error lengkap
sudo nginx -t

# 2. Cari semua default_server
grep -rn "default_server" /etc/nginx/sites-enabled/ /etc/nginx/sites-available/ 2>/dev/null

# 3. Hanya SATU vhost SIMTAMAN yang boleh default (atau hapus default_server dari yang lain)
#    Contoh: matikan site bawaan Ubuntu jika masih aktif
sudo rm -f /etc/nginx/sites-enabled/default

# 4. Edit config SIMTAMAN — pastikan tidak dobel file symlink
ls -la /etc/nginx/sites-enabled/

# 5. Uji & start
sudo nginx -t
sudo systemctl start nginx
sudo systemctl status nginx --no-pager

# 6. Cek lokal
curl -I http://127.0.0.1/
```

## Aturan aman

| Port | `default_server` |
|------|------------------|
| 80 | **Maks. 1** file config |
| 443 | **Maks. 1** file config |

Contoh benar (satu baris `listen` saja jadi default):

```nginx
listen 80 default_server;
listen [::]:80 default_server;
```

File lain cukup:

```nginx
listen 80;
listen [::]:80;
```

## Referensi config SIMTAMAN

`docs/deploy/nginx-sitaman.conf.example` — salin ke `/etc/nginx/sites-available/simtaman` tanpa menduplikasi symlink.

---

*Setelah Nginx active, deploy: `bash scripts/vps-deploy-update.sh`*
