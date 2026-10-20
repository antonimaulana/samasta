#!/usr/bin/env bash
# Pull latest code and run deploy steps on VPS
# Run on VPS: bash scripts/vps-deploy-update.sh

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/samasta}"
BRANCH="${BRANCH:-main}"

cd "$APP_DIR"

echo "=== SIMTAMAN deploy update — $(date) ==="
echo "Directory: $APP_DIR | Branch: $BRANCH"

# Composer menjalankan `php artisan package:discover` — butuh storage/logs & bootstrap/cache writable.
ensure_writable_dirs() {
    mkdir -p storage/logs \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/app/public \
        bootstrap/cache

    local owner="${DEPLOY_USER:-${SUDO_USER:-$USER}}"
    if [[ "$(id -u)" -eq 0 ]]; then
        chown -R "${owner}:www-data" storage bootstrap/cache
        chmod -R ug+rwx storage bootstrap/cache
    elif sudo -n true 2>/dev/null; then
        sudo chown -R "${owner}:www-data" storage bootstrap/cache
        sudo chmod -R ug+rwx storage bootstrap/cache
    else
        echo "[WARN] Tanpa sudo — pastikan $USER dan www-data bisa menulis storage/ & bootstrap/cache/"
    fi
}
ensure_writable_dirs

if [[ "${BACKUP_BEFORE_DEPLOY:-1}" == "1" ]]; then
    echo "[backup] Snapshot sebelum pull..."
    php artisan simtaman:backup --full --label=sebelum-deploy || echo "[WARN] Backup gagal — lanjut deploy (periksa manual)."
fi

git fetch origin
git pull origin "$BRANCH"
echo "[git] $(git log -1 --oneline)"

composer install --no-dev --optimize-autoloader

if command -v npm >/dev/null 2>&1; then
    npm ci
    npm run build
    rm -f public/hot
fi

php artisan migrate --force

php artisan optimize:clear

if [[ -L public/storage ]] || [[ -d public/storage ]]; then
    echo "[OK] public/storage sudah ada — lewati storage:link"
else
    php artisan storage:link
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

if command -v supervisorctl >/dev/null 2>&1; then
    if sudo -n supervisorctl restart samasta-worker:* 2>/dev/null; then
        echo "[OK] Queue worker (supervisor) di-restart"
    else
        echo "[WARN] Queue worker tidak di-restart (butuh sudo supervisorctl). Web tetap jalan; email/queue mungkin tertunda."
    fi
fi

if command -v systemctl >/dev/null 2>&1; then
    sudo systemctl reload nginx 2>/dev/null || echo "[WARN] nginx reload — jalankan manual: sudo systemctl reload nginx"
    for svc in php8.3-fpm php8.2-fpm php-fpm; do
        if systemctl is-active --quiet "$svc" 2>/dev/null; then
            sudo systemctl reload "$svc" 2>/dev/null && echo "[OK] Reload $svc" && break
        fi
    done
fi

if curl -fsS -o /dev/null -m 5 http://127.0.0.1/ 2>/dev/null; then
    echo "[OK] HTTP lokal (127.0.0.1) merespons"
else
    echo "[WARN] HTTP lokal tidak merespons — cek: sudo systemctl status nginx php*-fpm"
fi

echo "[SUCCESS] Deploy update selesai"
