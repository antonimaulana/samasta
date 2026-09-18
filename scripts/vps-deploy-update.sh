#!/usr/bin/env bash
# Pull latest code and run deploy steps on VPS
# Run on VPS: bash scripts/vps-deploy-update.sh

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/samasta}"
BRANCH="${BRANCH:-main}"

cd "$APP_DIR"

echo "=== SIMTAMAN deploy update — $(date) ==="
echo "Directory: $APP_DIR | Branch: $BRANCH"

if [[ "${BACKUP_BEFORE_DEPLOY:-1}" == "1" ]]; then
    echo "[backup] Snapshot sebelum pull..."
    php artisan simtaman:backup --full --label=sebelum-deploy || echo "[WARN] Backup gagal — lanjut deploy (periksa manual)."
fi

git fetch origin
git pull origin "$BRANCH"

composer install --no-dev --optimize-autoloader

if command -v npm >/dev/null 2>&1; then
    npm ci
    npm run build
    rm -f public/hot
fi

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

if command -v supervisorctl >/dev/null 2>&1; then
    supervisorctl restart samasta-worker:* 2>/dev/null || true
fi

echo "[SUCCESS] Deploy update selesai"
