# Setup GitHub — SIMTAMAN

Panduan push kode dari laptop Windows ke GitHub, lalu clone/update di VPS BatamGarden.

**Repo production:** [github.com/antonimaulana/samasta](https://github.com/antonimaulana/samasta) (public)  
**URL clone SSH:** `git@github.com:antonimaulana/samasta.git`  
**URL clone HTTPS:** `https://github.com/antonimaulana/samasta.git`

---

## Prasyarat

| Di laptop Windows | Di VPS BatamGarden |
|-------------------|-------------------|
| Git for Windows | `git` (sudah di install stack LEMP) |
| GitHub CLI (`gh`) — opsional tapi disarankan | SSH key deploy (bagian 3) |
| Akun GitHub | Akses SSH ke server |

Download:
- Git: https://git-scm.com/download/win
- GitHub CLI: https://cli.github.com/

Setelah install, **restart PowerShell** agar `git` dan `gh` dikenali.

---

## Bagian 1 — Push proyek ke GitHub (laptop Windows)

### Opsi A: Script otomatis

```powershell
cd C:\Users\USER\sitaman-batam
.\scripts\setup-github.ps1 -GitHubUsername "antonimaulana" -Public
```

Script akan:
1. `git init` (jika belum ada)
2. Commit awal (`.env` dan folder besar di-skip via `.gitignore`)
3. Buat repo private di GitHub via `gh` (jika sudah login)
4. Push ke `main`

### Opsi B: Manual step-by-step

```powershell
cd C:\Users\USER\sitaman-batam

git init -b main
git add -A
git status
# Pastikan .env TIDAK muncul di daftar staged

git commit -m "Initial commit: Portal SIMTAMAN Disperakimtan Batam"

gh auth login
gh repo create samasta --public --source=. --remote=origin --push
```

Jika `gh` tidak tersedia, buat repo manual di https://github.com/new (private), lalu:

```powershell
git remote add origin https://github.com/antonimaulana/samasta.git
git push -u origin main
```

Push HTTPS membutuhkan **Personal Access Token (PAT)**, bukan password GitHub:
1. GitHub → Settings → Developer settings → Personal access tokens → Fine-grained
2. Buat token dengan akses **Contents: Read and write** pada repo `samasta`
3. Saat diminta password, paste token

---

## Bagian 2 — Deploy key di VPS (disarankan)

Deploy key = SSH key khusus VPS yang hanya bisa **read** repo (lebih aman dari PAT di server).

### 2.1 Generate key di VPS

SSH ke server, jalankan:

```bash
bash /var/www/samasta/scripts/vps-setup-deploy-key.sh
```

Atau manual:

```bash
ssh-keygen -t ed25519 -C "batam-garden-vps-deploy" -f ~/.ssh/samasta_deploy -N ""
cat ~/.ssh/samasta_deploy.pub
```

Copy seluruh baris output `ssh-ed25519 AAAA...`.

### 2.2 Tambah ke GitHub

1. Buka repo → **Settings** → **Deploy keys** → **Add deploy key**
2. Title: `BatamGarden VPS`
3. Key: paste public key
4. **Allow write access:** OFF (cukup read untuk deploy)
5. Save

### 2.3 Konfigurasi SSH di VPS

```bash
cat >> ~/.ssh/config << 'EOF'
Host github.com
  HostName github.com
  User git
  IdentityFile ~/.ssh/samasta_deploy
  IdentitiesOnly yes
EOF

chmod 600 ~/.ssh/config
ssh -T git@github.com
```

Output yang diharapkan: `Hi antonimaulana/samasta! You've successfully authenticated...`

---

## Bagian 3 — Clone pertama kali di VPS

```bash
sudo mkdir -p /var/www/samasta
sudo chown $USER:www-data /var/www/samasta
cd /var/www/samasta

# Jika folder kosong:
git clone git@github.com:antonimaulana/samasta.git .

# Lanjut deploy (lihat VPS-BATAMGARDEN.md)
cp .env.example .env
nano .env
composer install --no-dev --optimize-autoloader
npm ci && npm run build
rm -f public/hot
php artisan key:generate
php artisan migrate --force
# ... seed, storage:link, config:cache
```

**Jangan** clone `https://github.com/ORG/samasta.git` — itu placeholder dokumentasi lama.

---

## Bagian 4 — Update ke depan (git pull)

Setelah perubahan di laptop di-push ke GitHub:

**Di VPS:**

```bash
cd /var/www/samasta
bash scripts/vps-deploy-update.sh
```

Atau manual:

```bash
cd /var/www/samasta
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
rm -f public/hot
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart samasta-worker:* 2>/dev/null || true
```

---

## Workflow harian

```
[Laptop] edit kode → git add → git commit → git push
                                              ↓
[VPS]    ssh ke server → bash scripts/vps-deploy-update.sh
```

---

## Troubleshooting

| Error | Penyebab | Solusi |
|-------|----------|--------|
| `Authentication failed` (HTTPS) | PAT salah / repo private | Pakai PAT atau switch ke SSH deploy key |
| `ORG/samasta` not found | Placeholder docs lama | Pakai git@github.com:antonimaulana/samasta.git |
| `Permission denied (publickey)` git@github | Deploy key belum ditambah | Ulangi bagian 2 |
| `git: command not found` (Windows) | Git belum install | Install Git, restart terminal |
| `.env` ikut ter-commit | .gitignore diabaikan | `git rm --cached .env` lalu commit |

---

## File terkait

| File | Fungsi |
|------|--------|
| `scripts/setup-github.ps1` | Init + push dari Windows |
| `scripts/vps-setup-deploy-key.sh` | Generate deploy key di VPS |
| `scripts/vps-deploy-update.sh` | Pull + deploy di VPS |
| `docs/deploy/VPS-BATAMGARDEN.md` | Referensi server BatamGarden |

---

*Diperbarui: Agustus 2026*
