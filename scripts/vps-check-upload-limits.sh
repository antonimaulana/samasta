#!/usr/bin/env bash
# Cek batas upload PHP (CLI) dan petunjuk FPM/Nginx — jalankan di VPS.
# Usage: bash scripts/vps-check-upload-limits.sh

set -euo pipefail

echo "=== SIMTAMAN — batas upload ==="
echo ""
echo "[PHP CLI — php -i]"
php -r 'echo "upload_max_filesize = ".ini_get("upload_max_filesize").PHP_EOL; echo "post_max_size      = ".ini_get("post_max_size").PHP_EOL;'
echo ""
echo "Catatan: Website memakai PHP-FPM. Nilai CLI bisa berbeda dari FPM."
echo "Edit: /etc/php/8.x/fpm/php.ini → upload_max_filesize & post_max_size min. 20M"
echo "Lalu: sudo systemctl reload php8.x-fpm"
echo ""

if command -v nginx >/dev/null 2>&1; then
    echo "[Nginx client_max_body_size]"
    grep -R "client_max_body_size" /etc/nginx/ 2>/dev/null | grep -v "#" | head -10 || echo "(tidak ditemukan — default 1M)"
    echo "Disarankan: client_max_body_size 20M; di server block situs SIMTAMAN"
else
    echo "[Nginx] tidak terpasang atau path berbeda"
fi

echo ""
echo "Aplikasi SIMTAMAN: max 10 MB per foto, hingga 20 foto per simpan."
echo "Panduan: docs/deploy/PHP-UPLOAD-LIMITS.md"
