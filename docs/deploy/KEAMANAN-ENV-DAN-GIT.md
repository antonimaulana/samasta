# Keamanan `.env` dan Git — SIMTAMAN

File `.env` berisi **rahasia operasional** (APP_KEY, kredensial database, PIN lapangan, password admin awal, SMTP). File ini **tidak boleh** pernah masuk repositori Git atau GitHub.

## Status yang diharapkan

| Item | Harus |
|------|--------|
| `.env` (lokal / VPS) | Ada di disk server, **tidak** di Git |
| `.env.example` | Di Git, **tanpa** nilai rahasia |
| `docs/deploy/.env.production.example` | Di Git, placeholder kosong |

Verifikasi cepat:

```bash
git ls-files .env
# (kosong = baik)

php scripts/check-secrets-not-in-git.php
php scripts/check-secrets-not-in-git.php --history
# atau: composer check-secrets && composer check-secrets-history
```

Windows (hapus dari indeks jika masih ter-track):

```powershell
.\scripts\remove-env-from-git-track.ps1
```

## Jika `.env` pernah ter-push

Meskipun file sudah dihapus dari commit terbaru, **riwayat Git** bisa masih menyimpan salinan. Asumsikan rahasia **bocor** dan lakukan rotasi.

### 1. Hapus dari tracking (commit ke depan)

```bash
git rm --cached .env
git commit -m "Hapus .env dari repositori; gunakan .env.example"
git push
```

Pastikan `.gitignore` memuat `.env` dan pola `.env.*` (kecuali `*.example`).

### 2. Bersihkan riwayat (jika pernah benar-benar ter-commit)

Gunakan **git filter-repo** (disarankan) atau BFG Repo-Cleaner, lalu **force push** hanya setelah koordinasi tim:

```bash
# Contoh dengan git-filter-repo (install terpisah)
git filter-repo --path .env --invert-paths
git push --force-with-lease origin main
```

Setelah force push, semua clone lama harus `git fetch` + reset atau clone ulang.

### 3. Rotasi rahasia di server produksi

| Variabel | Tindakan |
|----------|----------|
| `APP_KEY` | `php artisan key:generate --force` — **invalidasi session/data terenkripsi lama**; jadwalkan maintenance singkat |
| `ADMIN_PASSWORD` / user admin | Ubah via `php artisan tinker` atau panel; password kuat (min. 12 karakter) |
| `DB_PASSWORD` | Ubah di MySQL + update `.env` di VPS |
| `SIMTAMAN_LAPANGAN_PIN` | Ganti PIN; beri tahu tim lapangan |
| `MAIL_PASSWORD` | Rotasi di server SMTP |
| GitHub PAT / deploy key | Regenerasi jika pernah tercantum di `.env` atau log |

Di VPS (contoh):

```bash
cd /var/www/samasta
nano .env          # sesuaikan nilai baru
php artisan config:clear
php artisan cache:clear
sudo systemctl reload php8.3-fpm   # sesuaikan versi PHP
```

### 4. Cegah terulang

```bash
php scripts/install-git-hooks.php
```

Hook **pre-commit** menjalankan `scripts/check-secrets-not-in-git.php`.

Sebelum push besar:

```bash
composer check-secrets
```

Jangan pernah `git add -f .env`.

## Template environment

| Lingkungan | Salin dari |
|------------|------------|
| Development | `.env.example` → `.env` |
| Production VPS | `docs/deploy/.env.production.example` → `.env` |

Production wajib: `APP_DEBUG=false`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true` (jika HTTPS), password kuat, PIN lapangan tidak default.

## Audit berkala

- `git ls-files | findstr /i env` (Windows) atau `git ls-files | grep env`
- Tinjau GitHub: Settings → Secret scanning (jika repo organisasi)
- Simpan salinan `.env` produksi di **password manager / vault**, bukan di chat atau dokumen Word

---

*Disperakimtan Kota Batam — SIMTAMAN*
