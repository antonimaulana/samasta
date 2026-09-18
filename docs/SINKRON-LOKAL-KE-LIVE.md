# Sinkron data & tampilan lokal → SIMTAMAN live (VPS)

Production memakai **MySQL + 12 taman**; laptop development memakai **SQLite + ratusan taman** dan perubahan UI. **Git saja tidak menyalin database dan foto.**

---

## Ringkas (3 langkah)

| # | Di laptop (Windows) | Di VPS |
|---|---------------------|--------|
| 1 | Push kode + `npm run build` + `simtaman:export-production-bundle` | `git pull` + `vps-deploy-update.sh` |
| 2 | Upload file ZIP bundle (SCP/WinSCP) | Backup dulu (`simtaman:backup --full`) |
| 3 | — | `simtaman:import-production-bundle --force` |

---

## A. Laptop — kode & bundle

```powershell
cd C:\Users\USER\sitaman-batam

git status
git add -A
git commit -m "Sync untuk live pemutakhiran"
git push origin main

npm ci
npm run build

php artisan simtaman:export-production-bundle
```

File keluaran (contoh):

`storage\app\publish\simtaman-publish-2026-09-19_HHMMSS.zip`

---

## B. Upload ZIP ke VPS

**PowerShell** (sesuaikan nama file ZIP):

```powershell
scp -i "$env:USERPROFILE\.ssh\GardenBatam2026.pem" `
  "C:\Users\USER\sitaman-batam\storage\app\publish\simtaman-publish-2026-09-19_HHMMSS.zip" `
  GardenBatam@103.150.92.153:/var/www/samasta/storage/app/publish/
```

Atau **WinSCP**: host `103.150.92.153`, user `GardenBatam`, key `.pem` → folder `/var/www/samasta/storage/app/publish/`.

---

## C. VPS — deploy kode + import data

```bash
cd /var/www/samasta
mkdir -p storage/app/publish

php artisan simtaman:backup --full --label=sebelum-import-lokal

git pull origin main
bash scripts/vps-deploy-update.sh

php artisan simtaman:import-production-bundle \
  storage/app/publish/simtaman-publish-2026-09-19_HHMMSS.zip \
  --force

php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan simtaman:preflight-pemutakhiran
```

Ganti nama file ZIP sesuai yang di-upload.

---

## D. Verifikasi

- http://103.150.92.153/ — beranda & jumlah taman seperti lokal  
- http://103.150.92.153/admin/tamans — jumlah baris ≈ lokal  
- Buka satu taman: foto, GPS, fasilitas sama  

---

## Catatan penting

1. **`--force` mengganti seluruh data** di tabel aplikasi (kecuali `migrations`, cache, session). Backup VPS wajib sebelum import.  
2. **Akun login** ikut dari lokal (password hash di tabel `users`).  
3. **`APP_URL` di VPS** tetap `http://103.150.92.153` (bukan ngrok lokal).  
4. Perintah bundle ada setelah **`git pull`** yang memuat commit terbaru.

---

## Jika perintah bundle belum ada di VPS

Pastikan `git pull` sudah dapat commit yang memuat `simtaman:export-production-bundle`. Sementara: backup manual + hubungi tim dev untuk deploy commit terbaru.
