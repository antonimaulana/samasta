# VPS Production — BatamGarden (Biznet Gio Cloud)

Referensi cepat server production SIMTAMAN. **Jangan commit file ini jika berisi password.**

| Item | Nilai |
|------|--------|
| **Nama layanan** | BatamGarden |
| **Provider** | Biznet Gio Cloud (NEO Lite / MS 4.2) |
| **Region** | West Java |
| **OS** | Ubuntu 24.04 LTS |
| **Spesifikasi** | 2 vCPU · 4 GB RAM · 60 GB SSD |
| **Status** | Running / Active |
| **Public IP** | `103.150.92.153` |
| **Reverse DNS** | `ip-153-92-150-103.wjv-1.biznetg.io` |
| **SSH Username** | `GardenBatam` |
| **SSH Key Pair** | `GardenBatam2026` (file private: `GardenBatam2026.pem`) |
| **Path aplikasi** | `/var/www/samasta` |
| **Repo GitHub** | `git@github.com:antonimaulana/samasta.git` |
| **Console Access** | Dashboard Biznet → Open Console (username + password Console Access) |

---

## SSH dari Windows (PowerShell)

```powershell
ssh -i "C:\Users\USER\Downloads\GardenBatam2026.pem" GardenBatam@103.150.92.153
```

Alternatif hostname Biznet:

```powershell
ssh -i "C:\Users\USER\Downloads\GardenBatam2026.pem" GardenBatam@ip-153-92-150-103.wjv-1.biznetg.io
```

Jika error permission key:

```powershell
icacls "C:\Users\USER\Downloads\GardenBatam2026.pem" /inheritance:r
icacls "C:\Users\USER\Downloads\GardenBatam2026.pem" /grant:r "$($env:USERNAME):(R)"
```

---

## Kesalahan SSH yang sering terjadi

| Salah | Benar |
|-------|--------|
| `ssh ubuntu@GardenBatam` | Username bukan `ubuntu`, host bukan nama layanan |
| `ssh ubuntu@103.150.92.153` | Username harus `GardenBatam` + wajib `-i key.pem` |
| `ssh GardenBatam@BatamGarden` | `BatamGarden` bukan hostname DNS — pakai **IP** atau reverse DNS Biznet |
| SSH tanpa `-i` key file | Biznet memakai key pair, bukan password SSH |

---

## Clone dari GitHub (deploy key)

**Panduan lengkap:** `docs/deploy/GITHUB-SETUP.md`

```bash
# 1. Setup deploy key (sekali)
bash scripts/vps-setup-deploy-key.sh
# Copy public key → GitHub → Settings → Deploy keys

# 2. Clone (folder kosong)
cd /var/www/samasta
git clone git@github.com:antonimaulana/samasta.git .

# 3. Update ke depan
bash scripts/vps-deploy-update.sh
```

**Jangan pakai** `https://github.com/ORG/samasta.git` — itu placeholder dokumentasi lama.

---

## Cloudflare DNS (setelah domain siap)

| Type | Name | Content | Proxy |
|------|------|---------|-------|
| A | `samasta` (atau subdomain) | `103.150.92.153` | Proxied |

Set `APP_URL=https://domain-anda` di `.env` production.

---

## Checklist deploy di server ini

```bash
sudo apt update && sudo apt upgrade -y
sudo timedatectl set-timezone Asia/Jakarta
# ... install LEMP (lihat PANDUAN-DEPLOY-BIZNET-CLOUDFLARE.pdf)

cd /var/www/samasta
composer install --no-dev --optimize-autoloader
npm ci && npm run build
rm -f public/hot
php artisan migrate --force
php artisan db:seed --class=WilayahBatamSeeder --force
php artisan db:seed --class=TimPelaksanaSeeder --force
php artisan db:seed --class=DpaDocumentTemplateSeeder --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Dokumen terkait

| Dokumen | Path |
|---------|------|
| Panduan deploy lengkap (PDF) | `docs/PANDUAN-DEPLOY-BIZNET-CLOUDFLARE.pdf` |
| Dokumentasi arsitektur (PDF) | `docs/DOKUMENTASI-ARSITEKTUR-SIMTAMAN.pdf` |
| Contoh Nginx | `docs/deploy/nginx-sitaman.conf.example` |
| Contoh Supervisor | `docs/deploy/supervisor-sitaman.conf.example` |
| Setup GitHub | `docs/deploy/GITHUB-SETUP.md` |
| Script push Windows | `scripts/setup-github.ps1` |
| Script update VPS | `scripts/vps-deploy-update.sh` |

---

*Diperbarui: Agustus 2026 — sesuai VPS BatamGarden aktif di Biznet Gio.*
